<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
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

        if ($academyIds !== [] && $owner->hasPermission('resources.view') && Schema::hasTable('protected_learning_media_views')) {
            $activeStudentIds = DB::table('academy_user')
                ->whereIn('academy_id', $academyIds)
                ->where('role', 'student')
                ->where('status', 'active')
                ->select('user_id')
                ->distinct();

            // Only the initial open event is shown; page-by-page viewer events would
            // overwhelm a manager's feed and do not represent separate study actions.
            $data['recentLearningViews'] = ProtectedLearningMediaView::query()
                ->whereIn('academy_id', $academyIds)
                ->whereIn('user_id', $activeStudentIds)
                ->where('event_type', 'open')
                ->with(['user:id,name', 'media:id,original_name', 'resource:id,title'])
                ->orderByDesc('viewed_at')
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

        return view('owner.dashboard', $data);
    }
}
