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

        // Do not compute dashboard modules that the current admin cannot open.
        $canViewPeople = $owner->hasPermission('academy.view');
        $canViewCourses = $owner->hasPermission('courses.view');
        $canViewClassrooms = $owner->hasPermission('classrooms.view');
        $canViewAttendance = $owner->hasPermission('attendance.view');
        $canViewFinance = $owner->hasPermission('finance.view');
        $canViewAssignments = $owner->hasPermission('assignments.view');
        $canViewMeetings = $owner->hasPermission('live_classes.view');
        $canViewExams = $owner->hasPermission('exams.view');
        $canViewReports = $owner->hasPermission('reports.view');

        $academies = $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'owner_id']);

        $academyIds = $academies->pluck('id');

        if ($academyIds->isEmpty()) {
            return $this->empty($owner);
        }

        // One conditional aggregate replaces separate total/published course queries.
        $courseStats = $canViewCourses
            ? DB::table('courses')
                ->whereIn('academy_id', $academyIds)
                ->selectRaw('COUNT(*) AS total_courses')
                ->selectRaw(
                    "SUM(CASE WHEN status = 'published' AND published_at IS NOT NULL AND published_at <= ? THEN 1 ELSE 0 END) AS published_courses",
                    [$now->toDateTimeString()]
                )
                ->first()
            : (object) ['total_courses' => 0, 'published_courses' => 0];

        $courseCount = (int) ($courseStats->total_courses ?? 0);
        $publishedCourseCount = (int) ($courseStats->published_courses ?? 0);

        // Real scored courses only; the cylinder chart must not invent subject grades.
        $scoreCourseCandidates = $canViewExams
            ? Course::query()
                ->whereIn('academy_id', $academyIds)
                ->whereExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('exams as scored_exams')
                        ->join('exam_attempts as scored_attempts', 'scored_attempts.exam_id', '=', 'scored_exams.id')
                        ->whereColumn('scored_exams.course_id', 'courses.id')
                        ->whereNotNull('scored_attempts.submitted_at')
                        ->whereNotNull('scored_attempts.score');
                })
                ->orderByDesc('updated_at')
                ->limit(24)
                ->get(['id', 'title'])
            : collect();

        $courseExamAverages = $scoreCourseCandidates->isEmpty()
            ? collect()
            : $this->analytics->courseExamAverages($scoreCourseCandidates->pluck('id')->all());

        $subjectAverages = $scoreCourseCandidates
            ->map(function (Course $course) use ($courseExamAverages): ?array {
                if (! $courseExamAverages->has($course->id)) {
                    return null;
                }

                return [
                    'id' => (int) $course->id,
                    'title' => (string) $course->title,
                    'average' => (float) $courseExamAverages[$course->id],
                ];
            })
            ->filter()
            ->sortByDesc('average')
            ->take(4)
            ->values();

        // Teacher and student counts share the same membership scan.
        $memberCounts = $canViewPeople
            ? DB::table('academy_user as membership')
                ->join('users as accounts', 'accounts.id', '=', 'membership.user_id')
                ->whereIn('membership.academy_id', $academyIds)
                ->whereIn('membership.role', ['teacher', 'student'])
                ->where('membership.status', 'active')
                ->where('accounts.status', 'active')
                ->whereNull('accounts.deleted_at')
                ->groupBy('membership.role')
                ->select('membership.role')
                ->selectRaw('COUNT(DISTINCT membership.user_id) AS total')
                ->pluck('total', 'membership.role')
            : collect();

        $teacherCount = (int) ($memberCounts['teacher'] ?? 0);
        $studentCount = (int) ($memberCounts['student'] ?? 0);

        // Payments and refunds are grouped in one scan of the transactions table.
        $financeSummary = $canViewFinance
            ? DB::table('financial_transactions')
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'completed')
                ->whereIn('currency', ['IRT', 'TOMAN', 'TOMANS'])
                ->whereIn('type', ['enrollment_payment', 'refund'])
                ->whereRaw('COALESCE(occurred_at, created_at) BETWEEN ? AND ?', [
                    $periodStart->toDateTimeString(),
                    $now->toDateTimeString(),
                ])
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'enrollment_payment' THEN amount ELSE 0 END), 0) AS revenue")
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END), 0) AS refunds")
                ->first()
            : (object) ['revenue' => 0, 'refunds' => 0];

        $revenue = (float) ($financeSummary->revenue ?? 0);
        $refunds = (float) ($financeSummary->refunds ?? 0);

        $pendingReviewsQuery = $canViewAssignments
            ? DB::table('assignment_submissions as submissions')
                ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                ->join('courses', 'courses.id', '=', 'assignments.course_id')
                ->whereIn('courses.academy_id', $academyIds)
                ->whereNotNull('submissions.submitted_at')
                ->whereNull('submissions.graded_at')
            : null;

        $pendingReviews = $pendingReviewsQuery
            ? (clone $pendingReviewsQuery)->count()
            : 0;

        $pendingApprovalItems = $pendingReviewsQuery
            ? (clone $pendingReviewsQuery)
                ->join('users as students', 'students.id', '=', 'submissions.student_id')
                ->where('students.status', 'active')
                ->whereNull('students.deleted_at')
                ->select([
                    'submissions.id',
                    'submissions.submitted_at',
                    'assignments.id as assignment_id',
                    'courses.academy_id',
                    'assignments.title as assignment_title',
                    'courses.title as course_title',
                    'students.name as student_name',
                ])
                ->orderBy('submissions.submitted_at')
                ->limit(4)
                ->get()
            : collect();

        // One conditional attendance aggregate provides both period and today's KPIs.
        $attendanceSummary = $canViewAttendance
            ? DB::table('attendances')
                ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
                ->whereIn('classrooms.academy_id', $academyIds)
                ->where('classrooms.status', 'active')
                ->whereDate('attendances.attendance_date', '>=', $periodStart->toDateString())
                ->whereDate('attendances.attendance_date', '<=', $now->toDateString())
                ->selectRaw(
                    "SUM(CASE WHEN DATE(attendances.attendance_date) = ? AND attendances.status IN ('present', 'late', 'absent') THEN 1 ELSE 0 END) AS today_total",
                    [$now->toDateString()]
                )
                ->selectRaw(
                    "SUM(CASE WHEN DATE(attendances.attendance_date) = ? AND attendances.status IN ('present', 'late') THEN 1 ELSE 0 END) AS today_attended",
                    [$now->toDateString()]
                )
                ->selectRaw("SUM(CASE WHEN attendances.status IN ('present', 'late', 'absent') THEN 1 ELSE 0 END) AS period_total")
                ->selectRaw("SUM(CASE WHEN attendances.status IN ('present', 'late') THEN 1 ELSE 0 END) AS period_attended")
                ->first()
            : null;

        $attendanceTodayTotal = (int) ($attendanceSummary->today_total ?? 0);
        $attendanceTodayAttended = (int) ($attendanceSummary->today_attended ?? 0);
        $attendancePeriodTotal = (int) ($attendanceSummary->period_total ?? 0);
        $attendancePeriodAttended = (int) ($attendanceSummary->period_attended ?? 0);

        // Scope the roster subquery to this owner's active classrooms and derive
        // classroom count, capacity totals and capacity alerts in one aggregate.
        $capacitySummary = null;
        if ($canViewClassrooms) {
            $activeStudentCounts = DB::table('classroom_student as memberships')
                ->join('users as roster_users', 'roster_users.id', '=', 'memberships.student_id')
                ->where('memberships.status', 'active')
                ->where('roster_users.status', 'active')
                ->whereNull('roster_users.deleted_at')
                ->whereIn('memberships.classroom_id', function ($query) use ($academyIds): void {
                    $query->select('id')
                        ->from('classrooms')
                        ->whereIn('academy_id', $academyIds)
                        ->where('status', 'active');
                })
                ->groupBy('memberships.classroom_id')
                ->select('memberships.classroom_id')
                ->selectRaw('COUNT(DISTINCT memberships.student_id) AS active_students_count');

            $capacitySummary = DB::table('classrooms as classrooms')
                ->leftJoinSub($activeStudentCounts, 'student_counts', 'student_counts.classroom_id', '=', 'classrooms.id')
                ->whereIn('classrooms.academy_id', $academyIds)
                ->where('classrooms.status', 'active')
                ->selectRaw('COUNT(*) AS classroom_count')
                ->selectRaw('COALESCE(SUM(CASE WHEN classrooms.capacity IS NOT NULL THEN classrooms.capacity ELSE 0 END), 0) AS capacity_total')
                ->selectRaw('COALESCE(SUM(CASE WHEN classrooms.capacity IS NOT NULL THEN COALESCE(student_counts.active_students_count, 0) ELSE 0 END), 0) AS capacity_used')
                ->selectRaw('COALESCE(SUM(CASE WHEN classrooms.capacity IS NOT NULL THEN 1 ELSE 0 END), 0) AS configured_capacity_classrooms')
                ->selectRaw('SUM(CASE WHEN classrooms.capacity IS NOT NULL AND classrooms.capacity > 0 AND COALESCE(student_counts.active_students_count, 0) >= (classrooms.capacity * 0.9) THEN 1 ELSE 0 END) AS capacity_alerts')
                ->first();
        }

        $classroomCount = (int) ($capacitySummary->classroom_count ?? 0);
        $capacityAlerts = (int) ($capacitySummary->capacity_alerts ?? 0);
        $capacityTotal = (int) ($capacitySummary->capacity_total ?? 0);
        $capacityUsed = (int) ($capacitySummary->capacity_used ?? 0);
        $capacityRate = $capacityTotal > 0
            ? min(100, (int) round(($capacityUsed / $capacityTotal) * 100))
            : null;

        $liveNowClasses = $canViewMeetings ? LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('scheduled_at', '<=', $now)
            ->whereHas('classroom', fn ($query) => $query
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active'))
            ->orderByDesc('scheduled_at')
            ->limit(32)
            ->get(['id', 'scheduled_at', 'scheduled_end_at', 'duration_minutes'])
            : collect();

        $liveNowCount = $liveNowClasses
            ->filter(function (LiveClass $liveClass) use ($now): bool {
                $start = $liveClass->scheduled_at;
                $end = $liveClass->scheduled_end_at
                    ? Carbon::parse($liveClass->scheduled_end_at)
                    : $start->copy()->addMinutes(max(1, (int) ($liveClass->duration_minutes ?? 60)));

                return $start->lte($now) && $end->gte($now);
            })
            ->count();

        $todayLiveClasses = $canViewMeetings ? LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->whereDate('scheduled_at', $now->toDateString())
            ->whereHas('classroom', fn ($query) => $query
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active'))
            ->with([
                'course:id,title',
                'teacher:id,name',
                'classroom:id,title,academy_id',
            ])
            ->orderBy('scheduled_at')
            ->limit(12)
            ->get()
            : collect();

        $upcomingLiveClasses = $canViewMeetings ? LiveClass::query()
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
            ->get()
            : collect();

        $classroomsWithoutTeacher = $canViewClassrooms
            ? DB::table('classrooms as classrooms')
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
                ->count()
            : 0;

        $teacherReports = $canViewReports ? DB::table('course_teacher as ct')
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
            ->get()
            : collect();

        $dashboardTeacherIds = $teacherReports->pluck('teacher_id')->map(fn ($id) => (int) $id)->all();

        $teacherProgress = $dashboardTeacherIds === []
            ? collect()
            : $this->analytics->teacherProgressForAcademies($academyIds, $dashboardTeacherIds);

        $teacherPending = empty($dashboardTeacherIds) || !$canViewAssignments
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
        $trends = $this->dashboardTrends($academyIds, $now, [
            'students' => $canViewPeople,
            'attendance' => $canViewAttendance,
            'sales' => $canViewFinance,
            'pendingReviews' => $canViewAssignments,
        ]);

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
                'todayAttendanceRate' => $attendanceTodayTotal > 0
                    ? round(($attendanceTodayAttended / $attendanceTodayTotal) * 100, 1)
                    : null,
                'attendanceRate' => $attendancePeriodTotal > 0
                    ? round(($attendancePeriodAttended / $attendancePeriodTotal) * 100, 1)
                    : null,
                'liveNow' => $liveNowCount,
                'capacityAlerts' => $capacityAlerts,
                'classroomsWithoutTeacher' => $classroomsWithoutTeacher,
            ],
            'teacherReports' => $teacherReports,
            'subjectAverages' => $subjectAverages,
            'pendingApprovalItems' => $pendingApprovalItems,
            'trends' => $trends,
            'capacity' => [
                'total' => $capacityTotal,
                'used' => $capacityUsed,
                'remaining' => max(0, $capacityTotal - $capacityUsed),
                'rate' => $capacityRate,
                'configured_classrooms' => (int) ($capacitySummary->configured_capacity_classrooms ?? 0),
            ],
            'upcomingLiveClasses' => $upcomingLiveClasses,
            'todayLiveClasses' => $todayLiveClasses,
            'recentCourses' => $canViewCourses
                ? Course::query()
                    ->whereIn('academy_id', $academyIds)
                    ->with('academy:id,name')
                    ->withCount([
                        'enrollments as active_students_count' => fn ($query) => $query
                            ->where('status', 'active')
                            ->whereHas('student', fn ($student) => $student->where('users.status', 'active')),
                    ])
                    ->latest()
                    ->limit(6)
                    ->get()
                : collect(),
        ];
    }

    /**
     * Build database-backed trend series for compact KPI sparklines and dashboard charts.
     *
     * Event counts use zero for days with no matching events. Attendance keeps
     * nulls where there are no valid records so a chart never invents a 0% rate.
     */
    private function dashboardTrends($academyIds, Carbon $now, array $permissions = []): array
    {
        $today = $now->copy()->startOfDay();
        $chartStart = $today->copy()->startOfWeek(Carbon::SATURDAY)->subWeeks(12);
        $from = $chartStart->toDateTimeString();
        $to = $now->toDateTimeString();



        $dailyDates = collect();
        for ($day = $chartStart->copy(); $day->lte($today); $day->addDay()) {
            $dailyDates->push($day->toDateString());
        }

        $sparkDates = collect(range(0, 6))
            ->map(fn (int $offset) => $today->copy()->subDays(6 - $offset)->toDateString());

$studentsByDay = !empty($permissions['students'])
            ? DB::table('academy_user as membership')
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
            : collect()

$attendanceByDay = !empty($permissions['attendance'])
            ? DB::table('attendances')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->whereDate('attendances.attendance_date', '>=', $chartStart->toDateString())
            ->whereDate('attendances.attendance_date', '<=', $now->toDateString())
            ->groupByRaw('DATE(attendances.attendance_date)')
            ->selectRaw(
                "DATE(attendances.attendance_date) as day,
                SUM(CASE WHEN attendances.status IN ('present', 'late', 'absent') THEN 1 ELSE 0 END) as total,
                SUM(CASE WHEN attendances.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended"
            )
            ->get()
            ->keyBy('day');
            : collect()

        // Extend revenue to four Jalali/Gregorian calendar months for the cylinder chart.
        // A single grouped-by-day query is reused to derive both weekly and monthly totals.
$salesByDay = !empty($permissions['sales'])
            ? DB::table('financial_transactions')
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
            : collect()

$pendingByDay = !empty($permissions['pendingReviews'])
            ? DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->whereIn('courses.academy_id', $academyIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->whereBetween('submissions.submitted_at', [$from, $to])
            ->groupByRaw('DATE(submissions.submitted_at)')
            ->selectRaw('DATE(submissions.submitted_at) as day, COUNT(*) as value')
            ->pluck('value', 'day');
            : collect()

        $weekly = collect(range(0, 12))->map(function (int $offset) use (
            $chartStart,
            $today,
            $dailyDates,
            $studentsByDay,
            $attendanceByDay,
            $salesByDay,
            $pendingByDay
        ): array {
            $weekStart = $chartStart->copy()->addWeeks($offset)->startOfDay();
            $weekEnd = $weekStart->copy()->addDays(6);
            if ($weekEnd->gt($today)) {
                $weekEnd = $today->copy();
            }

            $days = $dailyDates->filter(
                fn (string $date): bool => $date >= $weekStart->toDateString() && $date <= $weekEnd->toDateString()
            );

            $newStudents = 0;
            $sales = 0.0;
            $pending = 0;
            $attendanceTotal = 0;
            $attendanceCounted = 0;

            foreach ($days as $date) {
                $newStudents += max(0, (int) ($studentsByDay[$date] ?? 0));
                $sales += (float) ($salesByDay[$date] ?? 0);
                $pending += max(0, (int) ($pendingByDay[$date] ?? 0));

                $attendance = $attendanceByDay->get($date);
                if ($attendance && (int) $attendance->total > 0) {
                    $attendanceTotal += (int) $attendance->total;
                    $attendanceCounted += (int) $attendance->attended;
                }
            }

            return [
                'label' => \App\Support\PersianUi::date($weekStart),
                'students' => $newStudents,
                'attendance' => $attendanceTotal > 0
                    ? round(($attendanceCounted / $attendanceTotal) * 100, 1)
                    : null,
                'sales' => $sales,
                'pendingReviews' => $pending,
            ];
        })->values();

        return [
            'students' => $sparkDates->map(fn (string $date) => max(0, (int) ($studentsByDay[$date] ?? 0)))->all(),
            'attendance' => $sparkDates->map(function (string $date) use ($attendanceByDay): ?float {
                $row = $attendanceByDay->get($date);
                if (! $row || (int) $row->total < 1) {
                    return null;
                }

                return round(((int) $row->attended / (int) $row->total) * 100, 1);
            })->all(),
            'sales' => $sparkDates->map(fn (string $date) => (float) ($salesByDay[$date] ?? 0))->all(),
            'pendingReviews' => $sparkDates->map(fn (string $date) => max(0, (int) ($pendingByDay[$date] ?? 0)))->all(),
            'weekly' => [
                'labels' => $weekly->pluck('label')->all(),
                'students' => $weekly->pluck('students')->all(),
                'attendance' => $weekly->pluck('attendance')->all(),
                'sales' => $weekly->pluck('sales')->all(),
                'pendingReviews' => $weekly->pluck('pendingReviews')->all(),
            ],
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
            'subjectAverages' => collect(),
            'pendingApprovalItems' => collect(),
            'trends' => [
                'students' => array_fill(0, 7, 0),
                'attendance' => array_fill(0, 7, null),
                'sales' => array_fill(0, 7, 0),
                'pendingReviews' => array_fill(0, 7, 0),
                'weekly' => [
                    'labels' => array_fill(0, 13, ''),
                    'students' => array_fill(0, 13, 0),
                    'attendance' => array_fill(0, 13, null),
                    'sales' => array_fill(0, 13, 0),
                    'pendingReviews' => array_fill(0, 13, 0),
                ],
            ],
            'capacity' => ['total' => 0, 'used' => 0, 'remaining' => 0, 'rate' => null, 'configured_classrooms' => 0],
            'upcomingLiveClasses' => collect(),
            'todayLiveClasses' => collect(),
            'recentCourses' => collect(),
        ];
    }
}
