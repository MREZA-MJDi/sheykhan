<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

final class StudentResultService
{
    public function paginate(User $student, int $perPage = 20): LengthAwarePaginator
    {
        $courseIds = app(StudentAccessService::class)->enrolledCourseIds($student);

        $assignmentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->whereIn('assignments.course_id', $courseIds)
            ->where('assignments.status', 'published')
            ->where('submissions.student_id', $student->id)
            ->whereNotNull('submissions.graded_at');

        $this->scopeToStudentTeacherCohort($assignmentResults, $student, 'assignments');

        $assignmentResults
            ->selectRaw("'assignment' as result_type")
            ->selectRaw('submissions.id as result_id')
            ->selectRaw('assignments.id as target_id')
            ->selectRaw('assignments.title as title')
            ->selectRaw('submissions.score as score')
            ->selectRaw('submissions.graded_at as occurred_at')
            ->selectRaw("'تصحیح‌شده' as status_label");

        $examResults = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->whereIn('exams.course_id', $courseIds)
            ->where('attempts.student_id', $student->id)
            ->whereNotNull('attempts.submitted_at');

        $this->scopeToStudentTeacherCohort($examResults, $student, 'exams');

        $examResults
            ->selectRaw("'exam' as result_type")
            ->selectRaw('attempts.id as result_id')
            ->selectRaw('exams.id as target_id')
            ->selectRaw('exams.title as title')
            ->selectRaw("CASE WHEN attempts.status IN ('graded', 'needs_review') THEN attempts.score ELSE NULL END as score")
            ->selectRaw('attempts.submitted_at as occurred_at')
            ->selectRaw("CASE WHEN attempts.status = 'graded' THEN 'تصحیح‌شده' WHEN attempts.status = 'needs_review' THEN 'نمره موقت · در انتظار تصحیح تشریحی' WHEN attempts.status = 'pending_review' THEN 'در صف تصحیح مدرس' ELSE 'ارسال‌شده' END as status_label");

        return DB::query()
            ->fromSub($assignmentResults->unionAll($examResults), 'student_results')
            ->orderByDesc('occurred_at')
            ->orderByDesc('result_id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Results must follow the same teacher/cohort boundary as assignments,
     * exams, and live classes. A course-level assessment (classroom_id = NULL)
     * is visible only when its teacher is assigned to one of the student's
     * active classrooms for that course.
     */
    private function scopeToStudentTeacherCohort(
        QueryBuilder $query,
        User $student,
        string $assessmentTable
    ): void {
        $query->whereExists(function ($group) use ($student, $assessmentTable): void {
            $group->selectRaw('1')
                ->from('classroom_student as student_membership')
                ->join('classrooms as student_classroom', 'student_classroom.id', '=', 'student_membership.classroom_id')
                ->join('classroom_teacher as teaching_assignment', 'teaching_assignment.classroom_id', '=', 'student_classroom.id')
                ->join('course_enrollments as student_enrollment', function ($join) use ($student): void {
                    $join->on('student_enrollment.classroom_id', '=', 'student_classroom.id')
                        ->on('student_enrollment.course_id', '=', 'student_classroom.course_id')
                        ->where('student_enrollment.student_id', '=', $student->id)
                        ->where('student_enrollment.status', '=', 'active');
                })
                ->whereColumn('student_classroom.course_id', $assessmentTable . '.course_id')
                ->whereColumn('teaching_assignment.teacher_id', $assessmentTable . '.teacher_id')
                ->where('student_membership.student_id', $student->id)
                ->where('student_membership.status', 'active')
                ->where('student_classroom.status', 'active')
                ->where(function ($scope) use ($assessmentTable): void {
                    $scope->whereNull($assessmentTable . '.classroom_id')
                        ->orWhereColumn($assessmentTable . '.classroom_id', 'student_classroom.id');
                });
        });
    }
}
