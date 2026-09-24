<?php

namespace App\Services;

use App\Models\Academy;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class OwnerReportService
{
    public function build(User $owner): array
    {
        abort_unless($owner->hasRole('academy-owner'), 403);

        $academies = $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($academies->isEmpty()) {
            return $this->empty($owner);
        }

        $academyIds = $academies->pluck('id');
        $academy = $academies->count() === 1 ? $academies->first() : null;

        $courseIds = DB::table('courses')->whereIn('academy_id', $academyIds)->pluck('id')->all();
        $classroomIds = DB::table('classrooms')->whereIn('academy_id', $academyIds)->pluck('id')->all();

        $teacherIds = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'teacher')
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id')
            ->all();

        $studentIds = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'student')
            ->where('status', 'active')
            ->distinct()
            ->pluck('user_id')
            ->all();

        $parentsCount = DB::table('academy_user')
            ->whereIn('academy_id', $academyIds)
            ->where('role', 'parent')
            ->where('status', 'active')
            ->distinct()
            ->count('user_id');

        $revenue = DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->where('type', 'enrollment_payment')
            ->sum('amount');

        $refunds = DB::table('financial_transactions')
            ->whereIn('academy_id', $academyIds)
            ->where('status', 'completed')
            ->where('type', 'refund')
            ->sum('amount');

        $attendance = DB::table('attendances')
            ->whereIn('classroom_id', $classroomIds)
            ->selectRaw("COUNT(*) AS total, SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) AS attended")
            ->first();

        $examAverage = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->whereIn('exams.course_id', $courseIds)
            ->whereNotNull('attempts.submitted_at')
            ->whereNotNull('attempts.score')
            ->avg('attempts.score');

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
                DB::raw('COALESCE(SUM(enrollments.paid_amount), 0) AS enrollment_sales'),
            ])
            ->orderByDesc('student_count')
            ->get();

        $teacherProgress = DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->join('course_teacher as ct', 'ct.course_id', '=', 'course_sections.course_id')
            ->whereIn('course_sections.course_id', $courseIds)
            ->whereIn('ct.teacher_id', $teacherIds)
            ->groupBy('ct.teacher_id')
            ->select('ct.teacher_id', DB::raw('ROUND(AVG(progress.progress_percent), 1) AS progress_average'))
            ->pluck('progress_average', 'ct.teacher_id');

        $teacherPending = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->whereIn('assignments.course_id', $courseIds)
            ->whereIn('assignments.teacher_id', $teacherIds)
            ->whereNotNull('submissions.submitted_at')
            ->whereNull('submissions.graded_at')
            ->groupBy('assignments.teacher_id')
            ->select('assignments.teacher_id', DB::raw('COUNT(*) AS pending_reviews'))
            ->pluck('pending_reviews', 'assignments.teacher_id');

        $teacherReports = $teacherReports->map(function ($report) use ($teacherProgress, $teacherPending) {
            $report->progress_average = (float) ($teacherProgress[$report->teacher_id] ?? 0);
            $report->pending_reviews = (int) ($teacherPending[$report->teacher_id] ?? 0);

            return $report;
        });

        $courseReports = Course::query()
            ->whereIn('id', $courseIds)
            ->withCount([
                'enrollments as active_student_count' => fn ($query) => $query->where('status', 'active'),
                'sections',
                'assignments',
                'exams',
                'liveClasses',
            ])
            ->orderByDesc('updated_at')
            ->get();

        $courseProgress = DB::table('lesson_progress as progress')
            ->join('lessons', 'lessons.id', '=', 'progress.lesson_id')
            ->join('course_sections', 'course_sections.id', '=', 'lessons.course_section_id')
            ->whereIn('course_sections.course_id', $courseIds)
            ->groupBy('course_sections.course_id')
            ->select('course_sections.course_id', DB::raw('ROUND(AVG(progress.progress_percent), 1) AS progress_average'))
            ->pluck('progress_average', 'course_id');

        $courseExamAverages = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->whereIn('exams.course_id', $courseIds)
            ->whereNotNull('attempts.submitted_at')
            ->whereNotNull('attempts.score')
            ->groupBy('exams.course_id')
            ->select('exams.course_id', DB::raw('ROUND(AVG(attempts.score), 1) AS exam_average'))
            ->pluck('exam_average', 'course_id');

        $courseReports->each(function (Course $course) use ($courseProgress, $courseExamAverages): void {
            $course->progress_average = (float) ($courseProgress[$course->id] ?? 0);
            $course->exam_average = isset($courseExamAverages[$course->id])
                ? (float) $courseExamAverages[$course->id]
                : null;
        });

        $classroomReports = \App\Models\Classroom::query()
            ->whereIn('id', $classroomIds)
            ->with('course:id,title')
            ->withCount([
                'students as active_students_count' => fn ($query) => $query->where('classroom_student.status', 'active'),
                'teachers as teacher_count',
            ])
            ->latest()
            ->get();

        $attendanceByClassroom = DB::table('attendances')
            ->whereIn('classroom_id', $classroomIds)
            ->groupBy('classroom_id')
            ->select(
                'classroom_id',
                DB::raw('COUNT(*) AS attendance_total'),
                DB::raw("SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) AS attendance_attended")
            )
            ->get()
            ->keyBy('classroom_id');

        $classroomReports->each(function ($classroom) use ($attendanceByClassroom): void {
            $item = $attendanceByClassroom->get($classroom->id);
            $classroom->attendance_rate = $item && (int) $item->attendance_total > 0
                ? round(((int) $item->attendance_attended / (int) $item->attendance_total) * 100, 1)
                : null;
        });

        return [
            'owner' => $owner,
            'academy' => $academy,
            'academies' => $academies,
            'metrics' => [
                'courses' => count($courseIds),
                'published' => Course::query()->whereIn('id', $courseIds)->published()->count(),
                'teachers' => count($teacherIds),
                'students' => count($studentIds),
                'parents' => $parentsCount,
                'classrooms' => count($classroomIds),
                'enrollments' => CourseEnrollment::query()->whereIn('course_id', $courseIds)->where('status', 'active')->count(),
                'attendance_rate' => $attendance?->total ? round(((int) $attendance->attended / (int) $attendance->total) * 100, 1) : null,
                'exam_average' => $examAverage !== null ? round((float) $examAverage, 1) : null,
                'revenue' => (float) $revenue - (float) $refunds,
                'pending_reviews' => (int) DB::table('assignment_submissions as submissions')
                    ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
                    ->whereIn('assignments.course_id', $courseIds)
                    ->whereNotNull('submissions.submitted_at')
                    ->whereNull('submissions.graded_at')
                    ->count(),
            ],
            'teacherReports' => $teacherReports,
            'courseReports' => $courseReports,
            'classroomReports' => $classroomReports,
            'recentEnrollments' => CourseEnrollment::query()
                ->whereIn('course_id', $courseIds)
                ->with(['student:id,name,email', 'course:id,title'])
                ->latest()
                ->limit(8)
                ->get(),
        ];
    }

    private function empty(User $owner): array
    {
        return [
            'owner' => $owner,
            'academy' => null,
            'academies' => collect(),
            'metrics' => [
                'courses' => 0,
                'published' => 0,
                'teachers' => 0,
                'students' => 0,
                'parents' => 0,
                'classrooms' => 0,
                'enrollments' => 0,
                'attendance_rate' => null,
                'exam_average' => null,
                'revenue' => 0,
                'pending_reviews' => 0,
            ],
            'teacherReports' => collect(),
            'courseReports' => collect(),
            'classroomReports' => collect(),
            'recentEnrollments' => collect(),
        ];
    }
}
