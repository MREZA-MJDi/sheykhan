<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitExamRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\StudentAccessService;
use App\Services\StudentExamService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(StudentExamService $exams): View
    {
        return view('student.exams.index', [
            'exams' => $exams->query(request()->user())->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Exam $exam, StudentExamService $exams): View
    {
        return view('student.exams.show', [
            'exam' => $exams->find(request()->user(), $exam),
        ]);
    }

    public function start(Exam $exam, StudentExamService $exams): RedirectResponse
    {
        $attempt = $exams->start(request()->user(), $exam);

        return redirect()->route('student.exams.attempt', [
            'exam' => $exam,
            'attempt' => $attempt,
        ]);
    }

    public function attempt(Exam $exam, ExamAttempt $attempt, StudentAccessService $access): View|RedirectResponse
    {
        $student = request()->user();

        abort_unless($attempt->student_id === $student->id, 404);
        abort_unless((int) $attempt->exam_id === (int) $exam->id, 404);

        if ($attempt->status !== 'in_progress') {
            abort_unless(
                in_array($attempt->status, ['graded', 'needs_review', 'pending_review', 'submitted'], true)
                    && $attempt->submitted_at !== null,
                404,
            );
            abort_unless($access->canViewExamResult($student, $exam), 404);

            return redirect()
                ->route('student.exams.result', ['exam' => $exam->id])
                ->with('status', 'این تلاش قبلاً ارسال شده است؛ نتیجهٔ آن را از این صفحه ببین.');
        }

        abort_unless($access->exam($student, $exam), 404);

        $attempt = $attempt->load([
            'exam:id,course_id,title,duration_minutes,starts_at,ends_at,status',
            'exam.questions' => fn ($query) => $query
                ->select('id','exam_id','type','question','options','score','sort_order')
                ->orderBy('sort_order'),
            'answers:id,exam_attempt_id,question_id,answer',
            'media:id,original_name,mime_type,extension,size,status',
        ]);

        return view('student.exams.attempt', [
            'attempt' => $attempt,
        ]);
    }

    public function submit(
        SubmitExamRequest $request,
        ExamAttempt $attempt,
        StudentExamService $exams,
        MediaService $media
    ): RedirectResponse {
        $exams->submit(
            $request->user(),
            $attempt,
            $request->validated('answers', []),
            $request->file('attachments', []),
            $media,
        );

        return redirect()->route('student.exams.index')
            ->with('success', 'پاسخ‌های آزمون با موفقیت ثبت شد.');
    }

    public function result(Exam $exam, StudentExamService $exams): View
    {
        $exam = $exams->findForResults(request()->user(), $exam);

        return view('student.exams.result', [
            'exam' => $exam,
            'attempts' => $exam->attempts,
        ]);
    }
}
