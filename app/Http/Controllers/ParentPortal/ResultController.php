<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PanelExportService;
use Illuminate\View\View;

final class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $results = $this->buildResultsQuery($request)->paginate(20)->withQueryString();

        return view('parent.results.index', compact('results'));
    }

    public function export(Request $request, PanelExportService $exports)
    {
        $filters = $request->validate(['format' => ['nullable', 'in:csv,word']]);
        $rows = $this->buildResultsQuery($request)->get()->map(fn ($row): array => [
            $row->student_name,
            $row->title,
            $row->result_type === 'exam' ? 'آزمون' : 'تکلیف',
            $row->occurred_at,
            $row->status_label,
            $row->score,
            $row->feedback,
            $row->due_at,
            $row->submitted_at,
        ]);

        return $exports->download(
            $filters['format'] ?? 'csv',
            'parent-learning-results',
            'خلاصه نتایج آموزشی فرزندان',
            ['دانش‌آموز', 'عنوان', 'نوع', 'تاریخ ثبت', 'وضعیت', 'نمره', 'بازخورد مدرس', 'مهلت تکلیف', 'زمان ارسال'],
            $rows,
            ['والد' => $request->user()->name, 'تعداد نتایج قابل مشاهده' => $rows->count()],
        );
    }

    private function buildResultsQuery(Request $request)
    {
        $parentId = (int) $request->user()->id;

        // Build rows from active course enrollments, then LEFT JOIN a submission.
        // This intentionally keeps assignments visible before the student submits.
        $assignmentResults = DB::table('assignments')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->join('academies', 'academies.id', '=', 'courses.academy_id')
            ->join('course_enrollments as enrollments', 'enrollments.course_id', '=', 'courses.id')
            ->join('users as students', 'students.id', '=', 'enrollments.student_id')
            ->join('parent_student as parent_links', function ($join) use ($parentId): void {
                $join->on('parent_links.student_id', '=', 'students.id')
                    ->where('parent_links.parent_id', '=', $parentId);
            })
            ->join('academy_user as student_membership', function ($join): void {
                $join->on('student_membership.user_id', '=', 'students.id')
                    ->on('student_membership.academy_id', '=', 'courses.academy_id')
                    ->where('student_membership.role', '=', 'student')
                    ->where('student_membership.status', '=', 'active');
            })
            ->join('academy_user as parent_membership', function ($join) use ($parentId): void {
                $join->on('parent_membership.user_id', '=', 'parent_links.parent_id')
                    ->on('parent_membership.academy_id', '=', 'courses.academy_id')
                    ->where('parent_membership.role', '=', 'parent')
                    ->where('parent_membership.status', '=', 'active');
            })
            ->leftJoin('assignment_submissions as submissions', function ($join): void {
                $join->on('submissions.assignment_id', '=', 'assignments.id')
                    ->on('submissions.student_id', '=', 'students.id');
            })
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->where('academies.status', 'active')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->where('assignments.status', 'published')
            ->where('enrollments.status', 'active')
            ->where(function ($access): void {
                $access->where('courses.access_type', 'free')
                    ->orWhere(function ($paid): void {
                        $paid->where('enrollments.payment_status', 'paid')
                            ->where('enrollments.paid_amount', '>', 0)
                            ->where(function ($amount): void {
                                $amount->whereNull('enrollments.price_amount')
                                    ->orWhereColumn('enrollments.paid_amount', '>=', 'enrollments.price_amount');
                            })
                            ->whereExists(function ($transaction): void {
                                $transaction->selectRaw('1')
                                    ->from('financial_transactions')
                                    ->whereColumn('financial_transactions.enrollment_id', 'enrollments.id')
                                    ->where('financial_transactions.status', 'completed')
                                    ->where('financial_transactions.amount', '>', 0);
                            });
                    });
            })
            ->where(function ($classroom): void {
                $classroom->whereNull('assignments.classroom_id')
                    ->orWhereExists(function ($membership): void {
                        $membership->selectRaw('1')
                            ->from('classroom_student as membership')
                            ->whereColumn('membership.classroom_id', 'assignments.classroom_id')
                            ->whereColumn('membership.student_id', 'students.id')
                            ->where('membership.status', 'active');
                    });
            })
            ->selectRaw("'assignment' as result_type")
            ->selectRaw('assignments.id as result_id')
            ->selectRaw('students.name as student_name')
            ->selectRaw('assignments.title as title')
            ->selectRaw('submissions.score as score')
            ->selectRaw('submissions.feedback as feedback')
            ->selectRaw('assignments.due_at as due_at')
            ->selectRaw('submissions.submitted_at as submitted_at')
            ->selectRaw('COALESCE(submissions.graded_at, submissions.submitted_at, assignments.created_at) as occurred_at')
            ->selectRaw("CASE
                WHEN submissions.id IS NULL OR submissions.submitted_at IS NULL THEN 'ارسال نشده'
                WHEN submissions.graded_at IS NULL THEN 'ارسال‌شده · در انتظار تصحیح'
                ELSE 'تصحیح‌شده'
            END as status_label");

        $examResults = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('academies', 'academies.id', '=', 'courses.academy_id')
            ->join('users as students', 'students.id', '=', 'attempts.student_id')
            ->join('parent_student as parent_links', function ($join) use ($parentId): void {
                $join->on('parent_links.student_id', '=', 'students.id')
                    ->where('parent_links.parent_id', '=', $parentId);
            })
            ->join('academy_user as student_membership', function ($join): void {
                $join->on('student_membership.user_id', '=', 'students.id')
                    ->on('student_membership.academy_id', '=', 'courses.academy_id')
                    ->where('student_membership.role', '=', 'student')
                    ->where('student_membership.status', '=', 'active');
            })
            ->join('academy_user as parent_membership', function ($join) use ($parentId): void {
                $join->on('parent_membership.user_id', '=', 'parent_links.parent_id')
                    ->on('parent_membership.academy_id', '=', 'courses.academy_id')
                    ->where('parent_membership.role', '=', 'parent')
                    ->where('parent_membership.status', '=', 'active');
            })
            ->where('students.status', 'active')
            ->whereNull('students.deleted_at')
            ->where('academies.status', 'active')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->where('exams.status', 'published')
            ->whereNotNull('attempts.submitted_at')
            ->where('attempts.status', '!=', 'in_progress')
            ->whereExists(function ($enrollments): void {
                $enrollments->selectRaw('1')
                    ->from('course_enrollments as enrollments')
                    ->whereColumn('enrollments.student_id', 'attempts.student_id')
                    ->whereColumn('enrollments.course_id', 'exams.course_id')
                    ->where('enrollments.status', 'active')
                    ->where(function ($access): void {
                        $access->where('courses.access_type', 'free')
                            ->orWhere(function ($paid): void {
                                $paid->where('enrollments.payment_status', 'paid')
                                    ->where('enrollments.paid_amount', '>', 0)
                                    ->where(function ($amount): void {
                                        $amount->whereNull('enrollments.price_amount')
                                            ->orWhereColumn('enrollments.paid_amount', '>=', 'enrollments.price_amount');
                                    })
                                    ->whereExists(function ($transaction): void {
                                        $transaction->selectRaw('1')
                                            ->from('financial_transactions')
                                            ->whereColumn('financial_transactions.enrollment_id', 'enrollments.id')
                                            ->where('financial_transactions.status', 'completed')
                                            ->where('financial_transactions.amount', '>', 0);
                                    });
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
            ->selectRaw('NULL as feedback')
            ->selectRaw('NULL as due_at')
            ->selectRaw('attempts.submitted_at as submitted_at')
            ->selectRaw('attempts.submitted_at as occurred_at')
            ->selectRaw("CASE
                WHEN attempts.status = 'graded' THEN 'تصحیح‌شده'
                WHEN attempts.status IN ('needs_review', 'pending_review') THEN 'در انتظار بررسی مدرس'
                ELSE 'ارسال‌شده'
            END as status_label");

        return DB::query()
            ->fromSub($assignmentResults->unionAll($examResults), 'child_results')
            ->orderByDesc('occurred_at')
            ->orderByDesc('result_id');
    }
}
}
