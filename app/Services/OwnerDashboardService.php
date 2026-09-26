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
    public function __construct(private readonly OwnerLearningAnalyticsService $analytics) {}

    public function build(User $owner): array
    {
        abort_unless($owner->hasRole('academy-owner'), 403);

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

        $teacherCount = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->distinct()
            ->count('user_id');

        $studentCount = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'student')
            ->where('status', 'active')
            ->distinct()
            ->count('user_id');

        $classroomBase = Classroom::query()
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'active');

        $classroomCount = (clone $classroomBase)->count();

        $revenue = (float) DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->where('type', 'enrollment_payment')
            ->sum('amount');

        $refunds = (float) DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->where('type', 'refund')
            ->sum('amount');

        $pendingReviews = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->whereIn('courses.academy_id', $academyIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->count();

        $attendanceToday = DB::table('attendances')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->whereDate('attendances.attendance_date', today())
            ->selectRaw(
                'COUNT(*) AS total,
                 SUM(CASE WHEN attendances.status IN (\'present\', \'late\') THEN 1 ELSE 0 END) AS attended'
            )
            ->first();

        $classrooms = (clone $classroomBase)
            ->with([
                'academy:id,name',
                'course:id,title',
                'teachers:id,name',
            ])
            ->withCount([
                'students as active_students_count' => fn ($query) => $query
                    ->where('classroom_student.status', 'active'),
            ])
            ->orderByRaw('CASE WHEN starts_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $classroomIdsForView = $classrooms->pluck('id');

        $attendanceByClassroom = DB::table('attendances')
            ->whereIn('classroom_id', $classroomIdsForView)
            ->whereDate('attendance_date', today())
            ->groupBy('classroom_id')
            ->select(
                'classroom_id',
                DB::raw('COUNT(*) AS total'),
                DB::raw("SUM(CASE WHEN status IN ('present','late') THEN 1 ELSE 0 END) AS attended")
            )
            ->get()
            ->keyBy('classroom_id');

        $now = now();

        $liveNowClasses = LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('scheduled_at', '<=', $now)
            ->whereHas('classroom', fn ($query) => $query
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active'))
            ->with([
                'course:id,title',
                'teacher:id,name',
                'classroom:id,title',
            ])
            ->orderByDesc('scheduled_at')
            ->limit(32)
            ->get();

        $liveNowByClassroom = $liveNowClasses
            ->filter(function (LiveClass $liveClass) use ($now): bool {
                $start = Carbon::parse($liveClass->scheduled_at);
                $end = $start->copy()->addMinutes((int) ($liveClass->duration_minutes ?? 60));

                return $start->lte($now) && $end->gte($now);
            })
            ->keyBy('classroom_id');

        $upcomingLiveClasses = LiveClass::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->whereBetween('scheduled_at', [$now, $now->copy()->addDays(7)])
            ->whereHas('classroom', fn ($query) => $query
                ->whereIn('academy_id', $academyIds)
                ->where('status', 'active'))
            ->with([
                'course:id,title',
                'teacher:id,name',
                'classroom:id,title',
            ])
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get();

        $classrooms->each(function (Classroom $classroom) use ($attendanceByClassroom, $liveNowByClassroom): void {
            $attendance = $attendanceByClassroom->get($classroom->id);
            $classroom->today_attendance_rate = $attendance && (int) $attendance->total > 0
                ? round(((int) $attendance->attended / (int) $attendance->total) * 100, 1)
                : null;

            $classroom->live_now = $liveNowByClassroom->get($classroom->id);
            $classroom->execution_status = $this->classroomStatus($classroom);
            $classroom->capacity_remaining = $classroom->capacity === null
                ? null
                : max(0, (int) $classroom->capacity - (int) $classroom->active_students_count);
            $classroom->occupancy_percent = $classroom->capacity && $classroom->capacity > 0
                ? min(100, round(($classroom->active_students_count / $classroom->capacity) * 100, 1))
                : null;
        });

        $activeStudentCounts = DB::table('classroom_student')
            ->where('status', 'active')
            ->groupBy('classroom_id')
            ->select('classroom_id', DB::raw('COUNT(*) AS active_students_count'));

        $capacityAlerts = DB::query()
            ->from('classrooms as classrooms')
            ->leftJoinSub($activeStudentCounts, 'student_counts', 'student_counts.classroom_id', '=', 'classrooms.id')
            ->whereIn('classrooms.academy_id', $academyIds)
            ->where('classrooms.status', 'active')
            ->whereNotNull('classrooms.capacity')
            ->whereRaw('COALESCE(student_counts.active_students_count, 0) >= (classrooms.capacity * 0.9)')
            ->count();

        $classroomsWithoutTeacher = (clone $classroomBase)
            ->doesntHave('teachers')
            ->count();

        $teacherReports = DB::table('course_teacher as ct')
            ->join('users', 'users.id', '=', 'ct.teacher_id')
            ->join('courses', 'courses.id', '=', 'ct.course_id')
            ->leftJoin('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->whereIn('courses.academy_id', $academyIds)
            ->groupBy('ct.teacher_id', 'users.name')
            ->select([
                'ct.teacher_id',
                'users.name',
                DB::raw('COUNT(DISTINCT courses.id) AS course_count'),
                DB::raw('COUNT(DISTINCT enrollments.student_id) AS student_count'),
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
            $report->progress_average = (float) ($teacherProgress[$report->teacher_id] ?? 0);
            $report->pending_reviews = (int) ($teacherPending[$report->teacher_id] ?? 0);

            return $report;
        });

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
                'liveNow' => $liveNowByClassroom->count(),
                'capacityAlerts' => $capacityAlerts,
                'classroomsWithoutTeacher' => $classroomsWithoutTeacher,
            ],
            'teacherReports' => $teacherReports,
            'classrooms' => $classrooms,
            'upcomingLiveClasses' => $upcomingLiveClasses,
            'recentCourses' => Course::query()
                ->whereIn('academy_id', $academyIds)
                ->with('academy:id,name')
                ->withCount([
                    'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
                ])
                ->latest()
                ->limit(6)
                ->get(),
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
                'liveNow' => 0,
                'capacityAlerts' => 0,
                'classroomsWithoutTeacher' => 0,
            ],
            'teacherReports' => collect(),
            'classrooms' => collect(),
            'upcomingLiveClasses' => collect(),
            'recentCourses' => collect(),
        ];
    }
}
