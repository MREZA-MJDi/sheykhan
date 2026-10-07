<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitExamRequest;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\StudentAccessService;
use App\Services\StudentExamService;
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

    public function attempt(Exam $exam, ExamAttempt $attempt, StudentExamService $exams, StudentAccessService $access): View
    {
        abort_unless($attempt->student_id === request()->user()->id, 404);
        abort_unless((int) $attempt->exam_id === (int) $exam->id, 404);
        abort_unless($access->exam(request()->user(), $exam), 404);

        $attempt = $attempt->load([
            'exam:id,course_id,title,duration_minutes,starts_at,ends_at,status',
            'exam.questions' => fn ($query) => $query
                ->select('id','exam_id','type','question','options','score','sort_order')
                ->orderBy('sort_order'),
            'answers:id,exam_attempt_id,question_id,answer',
        ]);

        abort_unless($attempt->status === 'in_progress', 404);

        return view('student.exams.attempt', [
            'attempt' => $attempt,
        ]);
    }

    public function submit(
        SubmitExamRequest $request,
        ExamAttempt $attempt,
        StudentExamService $exams
    ): RedirectResponse {
        $exams->submit(
            $request->user(),
            $attempt,
            $request->validated('answers', []),
        );

        return redirect()->route('student.exams.index')
            ->with('success', 'پاسخ‌های آزمون با موفقیت ثبت شد.');
    }

    public function result(Exam $exam, StudentExamService $exams): View
    {
        $exam = $exams->find(request()->user(), $exam);

        return view('student.exams.result', [
            'exam' => $exam,
            'attempts' => $exam->attempts,
        ]);
    }
}
