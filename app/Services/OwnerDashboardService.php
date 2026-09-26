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

        $courseIds = Course::query()
            ->whereIn('academy_id', $academyIds)
            ->pluck('id');

        $teacherIds = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id');

        $studentIds = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'student')
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id');

        $classroomIds = Classroom::query()
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'active')
            ->pluck('id');

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
            ->whereIn('assignments.course_id', $courseIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->count();

        $attendanceToday = DB::table('attendances')
            ->whereIn('classroom_id', $classroomIds)
            ->whereDate('attendance_date', today())
            ->selectRaw(
                'COUNT(*) AS total,
                 SUM(CASE WHEN status IN (\'present\', \'late\') THEN 1 ELSE 0 END) AS attended'
            )
            ->first();

        $classrooms = Classroom::query()
            ->whereIn('id', $classroomIds)
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

        $liveWindow = LiveClass::query()
            ->whereIn('classroom_id', $classroomIds)
            ->whereIn('status', ['scheduled', 'live'])
            ->whereBetween('scheduled_at', [now()->subDay(), now()->addDays(7)])
            ->with([
                'course:id,title',
                'teacher:id,name',
                'classroom:id,title',
            ])
            ->orderBy('scheduled_at')
            ->get();

        $liveNowByClassroom = $liveWindow
            ->filter(function (LiveClass $liveClass): bool {
                $start = Carbon::parse($liveClass->scheduled_at);
                $end = $start->copy()->addMinutes((int) ($liveClass->duration_minutes ?? 60));

                return $start->lte(now()) && $end->gte(now());
            })
            ->keyBy('classroom_id');

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

        $teacherReports = DB::table('course_teacher as ct')
            ->join('users', 'users.id', '=', 'ct.teacher_id')
            ->join('courses', 'courses.id', '=', 'ct.course_id')
            ->leftJoin('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'courses.id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->whereIn('courses.academy_id', $academyIds)
            ->whereIn('ct.teacher_id', $teacherIds)
            ->groupBy('ct.teacher_id', 'users.name')
            ->select([
                'ct.teacher_id',
                'users.name',
                DB::raw('COUNT(DISTINCT courses.id) AS course_count'),
                DB::raw('COUNT(DISTINCT enrollments.student_id) AS student_count'),
            ])
            ->orderByDesc('student_count')
            ->get();

        $teacherProgress = $this->analytics->teacherProgress($courseIds, $teacherIds);

        $teacherPending = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->whereIn('assignments.course_id', $courseIds)
            ->whereIn('assignments.teacher_id', $teacherIds)
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
                'courses' => $courseIds->count(),
                'teachers' => $teacherIds->count(),
                'students' => $studentIds->count(),
                'classrooms' => $classroomIds->count(),
                'sales' => $revenue - $refunds,
                'published' => Course::query()
                    ->whereIn('id', $courseIds)
                    ->published()
                    ->count(),
                'pendingReviews' => (int) $pendingReviews,
                'todayAttendanceRate' => $attendanceToday?->total
                    ? round(((int) $attendanceToday->attended / (int) $attendanceToday->total) * 100, 1)
                    : null,
                'liveNow' => $liveNowByClassroom->count(),
                'capacityAlerts' => $classrooms
                    ->filter(fn (Classroom $classroom) => $classroom->occupancy_percent !== null && $classroom->occupancy_percent >= 90)
                    ->count(),
                'classroomsWithoutTeacher' => $classrooms
                    ->filter(fn (Classroom $classroom) => $classroom->teachers->isEmpty())
                    ->count(),
            ],
            'teacherReports' => $teacherReports,
            'classrooms' => $classrooms,
            'upcomingLiveClasses' => $liveWindow
                ->filter(fn (LiveClass $liveClass) => Carbon::parse($liveClass->scheduled_at)->gte(now()))
                ->take(8)
                ->values(),
            'recentCourses' => Course::query()
                ->whereIn('id', $courseIds)
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
