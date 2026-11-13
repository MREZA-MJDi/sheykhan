<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ClassSchedule;
use App\Models\LiveClass;
use App\Models\ProtectedLearningMediaView;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\OwnerCacheService;
use App\Services\OwnerDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        OwnerDashboardService $dashboard,
        OwnerCacheService $cache,
    ): View {
        $owner = request()->user();
        $period = (string) request()->query('period', 'month');
        $period = in_array($period, ['today', 'week', 'month'], true) ? $period : 'month';

        $data = $cache->rememberDashboard(
            $owner,
            fn () => $dashboard->build($owner, $period),
            $period,
        );
        $data['period'] = $period;
        $data['periodCaption'] = match ($period) {
            'today' => 'امروز',
            'week' => 'این هفته',
            default => 'این ماه',
        };

        // The dashboard service already returns the owner's active academies.
        // Reuse the cached collection instead of issuing the same query twice.
        $data['academies'] = collect($data['academies'] ?? []);

        $firstAcademy = $data['academies']->first();
        $metrics = $data['metrics'] ?? [];
        $data['notifications'] = [];
        $pendingReviews = $owner->hasPermission('assignments.view') ? (int) data_get($metrics, 'pendingReviews', 0) : 0;
        $classroomsWithoutTeacher = $owner->hasPermission('classrooms.view') ? (int) data_get($metrics, 'classroomsWithoutTeacher', 0) : 0;
        $capacityAlerts = $owner->hasPermission('classrooms.view') ? (int) data_get($metrics, 'capacityAlerts', 0) : 0;

        if ($pendingReviews > 0 && $firstAcademy) {
            $data['notifications'][] = [
                'title' => 'تکالیف بررسی‌نشده',
                'detail' => \App\Support\PersianUi::digits($pendingReviews) . ' ارسال منتظر بررسی است.',
                'url' => route('owner.assignments.index', $firstAcademy),
                'color' => '#ff1f6b',
            ];
        }
        if ($classroomsWithoutTeacher > 0 && $firstAcademy) {
            $data['notifications'][] = [
                'title' => 'کلاس بدون مدرس',
                'detail' => \App\Support\PersianUi::digits($classroomsWithoutTeacher) . ' کلاس مدرس فعال ندارد.',
                'url' => route('owner.classrooms.index', $firstAcademy),
                'color' => '#ff9a1f',
            ];
        }
        if ($capacityAlerts > 0 && $firstAcademy) {
            $data['notifications'][] = [
                'title' => 'کلاس نزدیک ظرفیت',
                'detail' => \App\Support\PersianUi::digits($capacityAlerts) . ' کلاس به ۹۰٪ ظرفیت رسیده است.',
                'url' => route('owner.classrooms.index', $firstAcademy),
                'color' => '#22d3ee',
            ];
        }

        // Do not cache recent login events with the KPI aggregate; owners need this to refresh promptly.
        $academyIds = $data['academies']->pluck('id')->map(fn ($id) => (int) $id)->all();
        $data['recentStudentLogins'] = collect();
        $data['recentLearningViews'] = collect();
        $data['recentClassJoinRequests'] = collect();

        if ($academyIds !== [] && $owner->hasPermission('resources.view') && Schema::hasTable('protected_learning_media_views')) {
            // Correlate the viewer with an active student membership in the same
            // academy as the resource event; a membership in another academy is not enough.
            $data['recentLearningViews'] = ProtectedLearningMediaView::query()
                ->whereIn('academy_id', $academyIds)
                ->whereExists(function ($membership): void {
                    $membership->selectRaw('1')
                        ->from('academy_user as student_membership')
                        ->whereColumn('student_membership.user_id', 'protected_learning_media_views.user_id')
                        ->whereColumn('student_membership.academy_id', 'protected_learning_media_views.academy_id')
                        ->where('student_membership.role', 'student')
                        ->where('student_membership.status', 'active');
                })
                ->where('event_type', 'open')
                ->with(['user:id,name', 'media:id,original_name', 'resource:id,title'])
                ->orderByDesc('viewed_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if (
            $academyIds !== []
            && $owner->hasPermission('academy.view')
            && $owner->hasPermission('live_classes.view')
            && Schema::hasTable('audit_logs')
        ) {
            $activeStudentIds = DB::table('academy_user')
                ->whereIn('academy_id', $academyIds)
                ->where('role', 'student')
                ->where('status', 'active')
                ->select('user_id')
                ->distinct();

            $academyLiveClassIds = DB::table('live_classes')
                ->join('courses', 'courses.id', '=', 'live_classes.course_id')
                ->whereIn('courses.academy_id', $academyIds)
                ->select('live_classes.id');

            $academyScheduleIds = DB::table('class_schedules')
                ->join('classrooms', 'classrooms.id', '=', 'class_schedules.classroom_id')
                ->whereIn('classrooms.academy_id', $academyIds)
                ->select('class_schedules.id');

            $data['recentClassJoinRequests'] = AuditLog::query()
                ->whereIn('actor_id', $activeStudentIds)
                ->whereIn('action', ['live_class_join_requested', 'weekly_schedule_join_requested'])
                ->where(function ($subjects) use ($academyLiveClassIds, $academyScheduleIds): void {
                    $subjects
                        ->where(function ($liveClasses) use ($academyLiveClassIds): void {
                            $liveClasses
                                ->where('subject_type', LiveClass::class)
                                ->whereIn('subject_id', $academyLiveClassIds);
                        })
                        ->orWhere(function ($schedules) use ($academyScheduleIds): void {
                            $schedules
                                ->where('subject_type', ClassSchedule::class)
                                ->whereIn('subject_id', $academyScheduleIds);
                        });
                })
                ->with(['actor:id,name', 'subject'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        if ($academyIds !== [] && $owner->hasPermission('academy.view') && Schema::hasTable('audit_logs')) {
            $activeStudentIds = DB::table('academy_user')
                ->whereIn('academy_id', $academyIds)
                ->where('role', 'student')
                ->where('status', 'active')
                ->select('user_id')
                ->distinct();

            $data['recentStudentLogins'] = AuditLog::query()
                ->where('action', 'user_login')
                ->where('subject_type', User::class)
                ->whereIn('subject_id', $activeStudentIds)
                ->with('subject:id,name,email')
                ->orderByDesc('created_at')
                ->limit(3)
                ->get();
        }

        $joinActivities = $data['recentClassJoinRequests']->map(static function (AuditLog $event): object {
            return (object) [
                'student_name' => $event->actor?->name ?? 'دانش‌آموز',
                'detail' => $event->subject?->title ?? $event->subject?->classroom?->title ?? 'کلاس آنلاین',
                'action_label' => 'برای ورود به اتاق اقدام کرد · حضور قطعی محسوب نمی‌شود',
                'occurred_at' => $event->created_at,
                'icon' => 'video',
                'accent' => '#5265bd',
            ];
        });

        $resourceActivities = $data['recentLearningViews']->map(static function (ProtectedLearningMediaView $view): object {
            return (object) [
                'student_name' => $view->user?->name ?? 'دانش‌آموز',
                'detail' => $view->resource?->title ?? $view->media?->original_name ?? 'منبع آموزشی',
                'action_label' => 'برای مشاهده جزوه اقدام کرد',
                'occurred_at' => $view->viewed_at,
                'icon' => 'file',
                'accent' => '#16836b',
            ];
        });

        $data['recentLearningActivities'] = $joinActivities
            ->concat($resourceActivities)
            ->sortByDesc(static fn (object $activity): int => $activity->occurred_at?->timestamp ?? 0)
            ->take(8)
            ->values();

        return view('owner.dashboard', $data);
    }
}
