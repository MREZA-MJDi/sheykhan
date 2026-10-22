<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $childIds = $request->user()->children()
            ->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->select('users.id');

        $assignmentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('users as students', 'students.id', '=', 'submissions.student_id')
            ->whereIn('submissions.student_id', $childIds)
            ->whereNotNull('submissions.graded_at')
            ->where('assignments.status', 'published')
            ->whereExists(function ($enrollments): void {
                $enrollments->selectRaw('1')
                    ->from('course_enrollments as enrollments')
                    ->whereColumn('enrollments.student_id', 'submissions.student_id')
                    ->whereColumn('enrollments.course_id', 'assignments.course_id')
                    ->where('enrollments.status', 'active')
                    ->where(function ($access): void {
                        $access->where('enrollments.paid_amount', '>', 0)
                            ->orWhereExists(function ($free): void {
                                $free->selectRaw('1')
                                    ->from('courses as free_courses')
                                    ->whereColumn('free_courses.id', 'assignments.course_id')
                                    ->where('free_courses.access_type', 'free');
                            });
                    });
            })
            ->where(function ($classroom): void {
                $classroom->whereNull('assignments.classroom_id')
                    ->orWhereExists(function ($membership): void {
                        $membership->selectRaw('1')
                            ->from('classroom_student as membership')
                            ->whereColumn('membership.classroom_id', 'assignments.classroom_id')
                            ->whereColumn('membership.student_id', 'submissions.student_id')
                            ->where('membership.status', 'active');
                    });
            })
            ->selectRaw("'assignment' as result_type")
            ->selectRaw('submissions.id as result_id')
            ->selectRaw('students.name as student_name')
            ->selectRaw('assignments.title as title')
            ->selectRaw('submissions.score as score')
            ->selectRaw('submissions.graded_at as occurred_at')
            ->selectRaw("'تصحیح‌شده' as status_label");

        $examResults = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->join('users as students', 'students.id', '=', 'attempts.student_id')
            ->whereIn('attempts.student_id', $childIds)
            ->whereNotNull('attempts.submitted_at')
            ->where('exams.status', 'published')
            ->whereExists(function ($enrollments): void {
                $enrollments->selectRaw('1')
                    ->from('course_enrollments as enrollments')
                    ->whereColumn('enrollments.student_id', 'attempts.student_id')
                    ->whereColumn('enrollments.course_id', 'exams.course_id')
                    ->where('enrollments.status', 'active')
                    ->where(function ($access): void {
                        $access->where('enrollments.paid_amount', '>', 0)
                            ->orWhereExists(function ($free): void {
                                $free->selectRaw('1')
                                    ->from('courses as free_courses')
                                    ->whereColumn('free_courses.id', 'exams.course_id')
                                    ->where('free_courses.access_type', 'free');
                            });
                    });
            })
            ->where(function ($classroom): void {
                $classroom->whereNull('exams.classroom_id')
                    ->orWhereExists(function ($membership): void {
                        $membership->selectRaw('1')
                            ->from('classroom_student as membership')
                            ->whereColumn('membership.classroom_id', 'exams.classroom_id')
                            ->whereColumn('membership.student_id', 'attempts.student_id')
                            ->where('membership.status', 'active');
                    });
            })
            ->selectRaw("'exam' as result_type")
            ->selectRaw('attempts.id as result_id')
            ->selectRaw('students.name as student_name')
            ->selectRaw('exams.title as title')
            ->selectRaw("CASE WHEN attempts.status = 'graded' THEN attempts.score ELSE NULL END as score")
            ->selectRaw('attempts.submitted_at as occurred_at')
            ->selectRaw("CASE WHEN attempts.status = 'graded' THEN 'تصحیح‌شده' ELSE 'در انتظار بررسی' END as status_label");

        $results = DB::query()
            ->fromSub($assignmentResults->unionAll($examResults), 'child_results')
            ->orderByDesc('occurred_at')
            ->orderByDesc('result_id')
            ->paginate(20)
            ->withQueryString();

        return view('parent.results.index', compact('results'));
    }
}
