<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\LiveClass;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class OwnerDashboardService
{
    public function __construct(private readonly OwnerLearningAnalyticsService $analytics)
    {
    }

    public function build(User $owner, string $period = 'month'): array
    {
        abort_unless($owner->hasRole('academy-owner') && $owner->hasPermission('dashboard.view'), 403);

        $period = in_array($period, ['today', 'week', 'month'], true) ? $period : 'month';
        $now = now();
        $periodStart = match ($period) {
            'today' => $now->copy()->startOfDay(),
            'week' => $now->copy()->startOfWeek(Carbon::SATURDAY),
            default => $now->copy()->startOfMonth(),
        };

        $academies = $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $academyIds = $academies->pluck('id');

        if ($academyIds->isEmpty()) {
            return $this->empty($owner);
        }

        $courseCount = Course::query()
            ->whereIn('academy_id', $academyIds)
            ->count();

        $publishedCourseCount = Course::query()
            ->whereIn('academy_id', $academyIds)
            ->published()
            ->count();

        $teacherCount = DB::table('academy_user as membership')
            ->join('users as accounts', 'accounts.id', '=', 'membership.user_id')
            ->whereIn('membership.academy_id', $academyIds)
            ->where('membership.role', 'teacher')
            ->where('membership.status', 'active')
            ->where('accounts.status', 'active')
            ->whereNull('accounts.deleted_at')
            ->distinct()
            ->count('membership.user_id');

        $studentCount = DB::table('academy_user as membership')
            ->join('users as accounts', 'accounts.id', '=', 'membership.user_id')
            ->whereIn('membership.academy_id', $academyIds)
            ->where('membership.role', 'student')
            ->where('membership.status', 'active')
            ->where('accounts.status', 'active')
            ->whereNull('accounts.deleted_at')
            ->distinct()
            ->count('membership.user_id');

        $classroomBase = Classroom::query()
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'active');

        $classroomCount = (clone $classroomBase)->count();

        $revenue = (float) DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->whereIn('currency', ['IRT', 'TOMAN', 'TOMANS'])
            ->where('type', 'enrollment_payment')
            ->whereRaw('COALESCE(occurred_at, created_at) BETWEEN ? AND ?', [
                $periodStart->toDateTimeString(),
                $now->toDateTimeString(),
            ])
            ->sum('amount');

        $refunds = (float) DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->whereIn('currency', ['IRT', 'TOMAN', 'TOMANS'])
            ->where('type', 'refund')
            ->whereRaw('COALESCE(occurred_at, created_at) BETWEEN ? AND ?', [
                $periodStart->toDateTimeString(),
                $now->toDateTimeString(),
            ])
            ->sum('amount');

        $pendingReviews = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->whereIn('courses.academy_id', $academyIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->count();

        $attendanceBase = DB::table('attendances')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active');

        $attendanceTotals = "SUM(CASE WHEN attendances.status IN ('present', 'late', 'absent') THEN 1 ELSE 0 END) AS total,
             SUM(CASE WHEN attendances.status IN ('present', 'late') THEN 1 ELSE 0 END) AS attended";

        // The KPI labelled "today" must only use today's records; keep a separate
        // range aggregate for the period selector. whereDate also works if a driver
        // stores attendance_date as a datetime rather than a pure DATE column.
        $attendanceToday = (clone $attendanceBase)
            ->whereDate('attendances.attendance_date', '=', $now->toDateString())
            ->selectRaw($attendanceTotals)
            ->first();

        $attendanceForPeriod = (clone $attendanceBase)
            ->whereDate('attendances.attendance_date', '>=', $periodStart->toDateString())
            ->whereDate('attendances.attendance_date', '<=', $now->toDateString())
            ->selectRaw($attendanceTotals)
            ->first();

        // Keep the dashboard analytical: fetch whole-academy aggregates instead
        // of hydrating classroom cards that belong on the dedicated roster page.
        $activeStudentCounts = DB::table('classroom_student as memberships')
            ->join('users as roster_users', 'roster_users.id', '=', 'memberships.student_id')
            ->where('memberships.status', 'active')
            ->where('roster_users.status', 'active')
            ->whereNull('roster_users.deleted_at')
            ->groupBy('memberships.classroom_id')
            ->select('memberships.classroom_id')
            ->selectRaw('COUNT(DISTINCT memberships.student_id) AS active_students_count');

        $capacitySummary = DB::table('classrooms as classrooms')
            ->leftJoinSub($activeStudentCounts, 'student_counts', 'student_counts.classroom_id', '=', 'classrooms.id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->selectRaw('COALESCE(SUM(CASE WHEN classrooms.capacity IS NOT NULL THEN classrooms.capacity ELSE 0 END), 0) AS capacity_total')
            ->selectRaw('COALESCE(SUM(CASE WHEN classrooms.capacity IS NOT NULL THEN COALESCE(student_counts.active_students_count, 0) ELSE 0 END), 0) AS capacity_used')
            ->selectRaw('COALESCE(SUM(CASE WHEN classrooms.capacity IS NOT NULL THEN 1 ELSE 0 END), 0) AS configured_capacity_classrooms')
            ->first();

        $capacityTotal = (int) ($capacitySummary->capacity_total ?? 0);
        $capacityUsed = (int) ($capacitySummary->capacity_used ?? 0);
        $capacityRate = $capacityTotal > 0
            ? min(100, (int) round(($capacityUsed / $capacityTotal) * 100))
            : null;

        $liveNowClasses = LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('scheduled_at', '<=', $now)
            ->whereHas('classroom', fn ($query) => $query
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active'))
            ->orderByDesc('scheduled_at')
            ->limit(32)
            ->get(['id', 'scheduled_at', 'scheduled_end_at', 'duration_minutes']);

        $liveNowCount = $liveNowClasses
            ->filter(function (LiveClass $liveClass) use ($now): bool {
                $start = $liveClass->scheduled_at;
                $end = $liveClass->scheduled_end_at
                    ? Carbon::parse($liveClass->scheduled_end_at)
                    : $start->copy()->addMinutes(max(1, (int) ($liveClass->duration_minutes ?? 60)));

                return $start->lte($now) && $end->gte($now);
            })
            ->count();

        $upcomingLiveClasses = LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
            ->whereHas('classroom', fn ($query) => $query
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active'))
            ->with([
                'course:id,title',
                'teacher:id,name',
                'classroom:id,title,academy_id',
            ])
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        $capacityAlerts = DB::query()
            ->from('classrooms as classrooms')
            ->leftJoinSub($activeStudentCounts, 'student_counts', 'student_counts.classroom_id', '=', 'classrooms.id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->whereNotNull('classrooms.capacity')
            ->where('classrooms.capacity', '>', 0)
            ->whereRaw('COALESCE(student_counts.active_students_count, 0) >= (classrooms.capacity * 0.9)')
            ->count();

        $classroomsWithoutTeacher = DB::table('classrooms as classrooms')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('classroom_teacher as assignment')
                    ->join('users as teacher_users', 'teacher_users.id', '=', 'assignment.teacher_id')
                    ->join('academy_user as teacher_membership', function ($join): void {
                        $join->on('teacher_membership.user_id', '=', 'assignment.teacher_id')
                            ->on('teacher_membership.academy_id', '=', 'classrooms.academy_id')
                            ->where('teacher_membership.role', '=', 'teacher')
                            ->where('teacher_membership.status', '=', 'active');
                    })
                    ->whereColumn('assignment.classroom_id', 'classrooms.id')
                    ->where('teacher_users.status', '=', 'active')
                    ->whereNull('teacher_users.deleted_at');
            })
            ->count();

        $teacherReports = DB::table('course_teacher as ct')
            ->join('users', 'users.id', '=', 'ct.teacher_id')
            ->join('courses', 'courses.id', '=', 'ct.course_id')
            ->leftJoinSub(
                $this->analytics->teacherRosterCountsQuery($academyIds),
                'cohort_counts',
                'cohort_counts.teacher_id',
                '=',
                'ct.teacher_id'
            )
            ->leftJoin('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->leftJoin('financial_transactions as transactions', function ($join) use ($periodStart, $now): void {
                $join->on('transactions.enrollment_id', '=', 'enrollments.id')
                    ->where('transactions.status', '=', 'completed')
                    ->whereIn('transactions.currency', ['IRT', 'TOMAN', 'TOMANS'])
                    ->where(function ($periodQuery) use ($periodStart, $now): void {
                        $periodQuery->whereBetween('transactions.occurred_at', [
                            $periodStart->toDateTimeString(),
                            $now->toDateTimeString(),
                        ])->orWhere(function ($fallbackQuery) use ($periodStart, $now): void {
                            $fallbackQuery->whereNull('transactions.occurred_at')
                                ->whereBetween('transactions.created_at', [
                                    $periodStart->toDateTimeString(),
                                    $now->toDateTimeString(),
                                ]);
                        });
                    });
            })
            ->whereIn('courses.academy_id', $academyIds)
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereExists(function ($membership): void {
                $membership->selectRaw('1')
                    ->from('academy_user as active_teacher_membership')
                    ->whereColumn('active_teacher_membership.user_id', 'ct.teacher_id')
                    ->whereColumn('active_teacher_membership.academy_id', 'courses.academy_id')
                    ->where('active_teacher_membership.role', 'teacher')
                    ->where('active_teacher_membership.status', 'active');
            })
            ->groupBy('ct.teacher_id', 'users.name', 'cohort_counts.student_count')
            ->select([
                'ct.teacher_id',
                'users.name',
                DB::raw('COUNT(DISTINCT courses.id) AS course_count'),
                DB::raw('COALESCE(cohort_counts.student_count, 0) AS student_count'),
                DB::raw("COALESCE(SUM(CASE WHEN transactions.type = 'enrollment_payment' THEN transactions.amount WHEN transactions.type = 'refund' THEN -transactions.amount ELSE 0 END),0) AS sales"),
            ])
            ->orderByDesc('student_count')
            ->orderBy('users.name')
            ->limit(8)
            ->get();

        $dashboardTeacherIds = $teacherReports->pluck('teacher_id')->map(fn ($id) => (int) $id)->all();

        $teacherProgress = $this->analytics->teacherProgressForAcademies($academyIds, $dashboardTeacherIds);

        $teacherPending = empty($dashboardTeacherIds)
            ? collect()
            : DB::table('assignment_submissions as submissions')
                ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                ->join('courses', 'courses.id', '=', 'assignments.course_id')
                ->whereIn('courses.academy_id', $academyIds)
                ->whereIn('assignments.teacher_id', $dashboardTeacherIds)
                ->whereNotNull('submissions.submitted_at')
                ->whereNull('submissions.graded_at')
                ->groupBy('assignments.teacher_id')
                ->select(
                    'assignments.teacher_id',
                    DB::raw('COUNT(*) AS pending_reviews')
                )
                ->pluck('pending_reviews', 'assignments.teacher_id');

        $teacherReports = $teacherReports->map(function ($report) use ($teacherProgress, $teacherPending) {
            $report->progress_average = (float)($teacherProgress[$report->teacher_id] ?? 0);
            $report->pending_reviews = (int)($teacherPending[$report->teacher_id] ?? 0);

            return $report;
        });

        // Actual seven-day sparkline data for the dashboard prototype's KPI cards.
        // All series are bounded to the selected academies and current moment.
        $trends = $this->dashboardTrends($academyIds, $now);

        return [
            'owner' => $owner,
            'academies' => $academies,
            'metrics' => [
                'courses' => $courseCount,
                'teachers' => $teacherCount,
                'students' => $studentCount,
                'classrooms' => $classroomCount,
                'sales' => $revenue - $refunds,
                'published' => $publishedCourseCount,
                'pendingReviews' => (int) $pendingReviews,
                'todayAttendanceRate' => $attendanceToday?->total
                    ? round(((int) $attendanceToday->attended / (int) $attendanceToday->total) * 100, 1)
                    : null,
                'attendanceRate' => $attendanceForPeriod?->total
                    ? round(((int) $attendanceForPeriod->attended / (int) $attendanceForPeriod->total) * 100, 1)
                    : null,
                'liveNow' => $liveNowCount,
                'capacityAlerts' => $capacityAlerts,
                'classroomsWithoutTeacher' => $classroomsWithoutTeacher,
            ],
            'teacherReports' => $teacherReports,
            'trends' => $trends,
            'capacity' => [
                'total' => $capacityTotal,
                'used' => $capacityUsed,
                'remaining' => max(0, $capacityTotal - $capacityUsed),
                'rate' => $capacityRate,
                'configured_classrooms' => (int) ($capacitySummary->configured_capacity_classrooms ?? 0),
            ],
            'upcomingLiveClasses' => $upcomingLiveClasses,
            'recentCourses' => Course::query()
                ->whereIn('academy_id', $academyIds)
                ->with('academy:id,name')
                ->withCount([
                    'enrollments as active_students_count' => fn ($query) => $query
                        ->where('status', 'active')
                        ->whereHas('student', fn ($student) => $student->where('users.status', 'active')),
                ])
                ->latest()
                ->limit(6)
                ->get(),
        ];
    }

    /**
     * Build real, compact seven-day series for the KPI sparklines.
     *
     * A missing attendance day remains null instead of pretending that its
     * attendance rate was zero. Event-count and revenue series use zero for
     * days with no matching events.
     */
    private function dashboardTrends($academyIds, Carbon $now): array
    {
        $start = $now->copy()->subDays(6)->startOfDay();
        $from = $start->toDateTimeString();
        $to = $now->toDateTimeString();
        $dates = collect(range(0, 6))
            ->map(fn (int $offset) => $start->copy()->addDays($offset)->toDateString());

        $studentsByDay = DB::table('academy_user as membership')
            ->join('users as accounts', 'accounts.id', '=', 'membership.user_id')
            ->whereIn('membership.academy_id', $academyIds)
            ->where('membership.role', 'student')
            ->where('membership.status', 'active')
            ->where('accounts.status', 'active')
            ->whereNull('accounts.deleted_at')
            ->whereRaw('COALESCE(membership.joined_at, membership.created_at) BETWEEN ? AND ?', [$from, $to])
            ->groupByRaw('DATE(COALESCE(membership.joined_at, membership.created_at))')
            ->selectRaw('DATE(COALESCE(membership.joined_at, membership.created_at)) as day, COUNT(DISTINCT membership.user_id) as value')
            ->pluck('value', 'day');

        $attendanceByDay = DB::table('attendances')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->whereDate('attendances.attendance_date', '>=', $start->toDateString())
            ->whereDate('attendances.attendance_date', '<=', $now->toDateString())
            ->groupByRaw('DATE(attendances.attendance_date)')
            ->selectRaw(
                "DATE(attendances.attendance_date) as day,
                SUM(CASE WHEN attendances.status IN ('present', 'late', 'absent') THEN 1 ELSE 0 END) as total,
                SUM(CASE WHEN attendances.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended"
            )
            ->get()
            ->keyBy('day');

        $salesByDay = DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->whereIn('currency', ['IRT', 'TOMAN', 'TOMANS'])
            ->whereIn('type', ['enrollment_payment', 'refund'])
            ->whereRaw('COALESCE(occurred_at, created_at) BETWEEN ? AND ?', [$from, $to])
            ->groupByRaw('DATE(COALESCE(occurred_at, created_at))')
            ->selectRaw(
                "DATE(COALESCE(occurred_at, created_at)) as day,
                SUM(CASE WHEN type = 'enrollment_payment' THEN amount WHEN type = 'refund' THEN -amount ELSE 0 END) as value"
            )
            ->pluck('value', 'day');

        $pendingByDay = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->whereIn('courses.academy_id', $academyIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->whereBetween('submissions.submitted_at', [$from, $to])
            ->groupByRaw('DATE(submissions.submitted_at)')
            ->selectRaw('DATE(submissions.submitted_at) as day, COUNT(*) as value')
            ->pluck('value', 'day');

        return [
            'students' => $dates->map(fn (string $date) => max(0, (int) ($studentsByDay[$date] ?? 0)))->all(),
            'attendance' => $dates->map(function (string $date) use ($attendanceByDay): ?float {
                $row = $attendanceByDay->get($date);
                if (! $row || (int) $row->total < 1) {
                    return null;
                }

                return round(((int) $row->attended / (int) $row->total) * 100, 1);
            })->all(),
            'sales' => $dates->map(fn (string $date) => (float) ($salesByDay[$date] ?? 0))->all(),
            'pendingReviews' => $dates->map(fn (string $date) => max(0, (int) ($pendingByDay[$date] ?? 0)))->all(),
        ];
    }

    private function classroomStatus(Classroom $classroom): string
    {
        if ($classroom->status === 'archived') {
            return 'archived';
        }

        if ($classroom->live_now ?? false) {
            return 'running';
        }

        $now = now();

        if ($classroom->starts_at && $classroom->ends_at) {
            if ($classroom->starts_at->lte($now) && $classroom->ends_at->gte($now)) {
                return 'running';
            }

            if ($classroom->starts_at->gt($now)) {
                return 'scheduled';
            }

            if ($classroom->ends_at->lt($now)) {
                return 'finished';
            }
        }

        return 'active';
    }

    private function empty(User $owner): array
    {
        return [
            'owner' => $owner,
            'academies' => collect(),
            'metrics' => [
                'courses' => 0,
                'teachers' => 0,
                'students' => 0,
                'classrooms' => 0,
                'sales' => 0,
                'published' => 0,
                'pendingReviews' => 0,
                'todayAttendanceRate' => null,
                'attendanceRate' => null,
                'liveNow' => 0,
                'capacityAlerts' => 0,
                'classroomsWithoutTeacher' => 0,
            ],
            'teacherReports' => collect(),
            'trends' => [
                'students' => array_fill(0, 7, 0),
                'attendance' => array_fill(0, 7, null),
                'sales' => array_fill(0, 7, 0),
                'pendingReviews' => array_fill(0, 7, 0),
            ],
            'capacity' => ['total' => 0, 'used' => 0, 'remaining' => 0, 'rate' => null, 'configured_classrooms' => 0],
            'upcomingLiveClasses' => collect(),
            'recentCourses' => collect(),
        ];
    }
}
