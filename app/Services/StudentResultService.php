<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class StudentResultService
{
    public function paginate(User $student, int $perPage = 20): LengthAwarePaginator
    {
        $access = app(StudentAccessService::class);
        $courseIds = $access->enrolledCourseIds($student);
        $classroomIds = $student->classroomsAsStudent()
            ->wherePivot('status', 'active')
            ->pluck('classrooms.id');

        $assignmentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->whereIn('assignments.course_id', $courseIds)
            ->where('assignments.status', 'published')
            ->where(function ($query) use ($classroomIds): void {
                $query->whereNull('assignments.classroom_id')
                    ->orWhereIn('assignments.classroom_id', $classroomIds);
            })
            ->where('submissions.student_id', $student->id)
            ->whereNotNull('submissions.graded_at')
            ->selectRaw("'assignment' as result_type")
            ->selectRaw('submissions.id as result_id')
            ->selectRaw('assignments.title as title')
            ->selectRaw('submissions.score as score')
            ->selectRaw('submissions.graded_at as occurred_at')
            ->selectRaw("'تصحیح‌شده' as status_label");

        $examResults = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->whereIn('exams.course_id', $courseIds)
            ->where('exams.status', 'published')
            ->where(function ($query) use ($classroomIds): void {
                $query->whereNull('exams.classroom_id')
                    ->orWhereIn('exams.classroom_id', $classroomIds);
            })
            ->where('attempts.student_id', $student->id)
            ->whereNotNull('attempts.submitted_at')
            ->selectRaw("'exam' as result_type")
            ->selectRaw('attempts.id as result_id')
            ->selectRaw('exams.title as title')
            ->selectRaw("CASE WHEN attempts.status = 'graded' THEN attempts.score ELSE NULL END as score")
            ->selectRaw('attempts.submitted_at as occurred_at')
            ->selectRaw("CASE WHEN attempts.status = 'graded' THEN 'تصحیح‌شده' ELSE 'در انتظار بررسی' END as status_label");

        return DB::query()
            ->fromSub($assignmentResults->unionAll($examResults), 'student_results')
            ->orderByDesc('occurred_at')
            ->orderByDesc('result_id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
