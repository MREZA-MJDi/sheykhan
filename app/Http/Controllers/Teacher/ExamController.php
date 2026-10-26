<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Exam\StoreExamRequest;
use App\Models\Exam;
use App\Services\ExamQuestionWriter;
use App\Services\MediaService;
use App\Services\TeacherAccessService;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.exams.index', [
            'exams' => $workspace->exams(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.exams.form', [
            'courses' => $workspace->courses(request()->user()),
            'classrooms' => $workspace->classrooms(request()->user()),
            'exam' => new Exam(['status' => 'draft', 'duration_minutes' => 60, 'attempts_allowed' => 1]),
            'initialQuestions' => [],
        ]);
    }

    public function store(
        StoreExamRequest $request,
        TeacherWorkspaceService $workspace,
        ExamQuestionWriter $questionWriter,
        MediaService $media
    ): RedirectResponse {
        $courseId = (int) $request->validated('course_id');
        $course = $workspace->courseOwnedBy($request->user(), $courseId);

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedByCourse($request->user(), (int) $classroomId, $courseId);
        }

        $payload = $request->safe()->except(['course_id', 'questions']);
        $payload['teacher_id'] = $request->user()->id;

        $exam = DB::transaction(function () use ($course, $payload, $request, $questionWriter, $media): Exam {
            $exam = $course->exams()->create($payload);
            $questionWriter->storeQuestions($exam, $request->validated('questions', []), $media);
            return $exam;
        });

        return redirect()->route('teacher.exams.index')
            ->with('success', 'آزمون با موفقیت ساخته شد.');
    }

    public function edit(
        Exam $exam,
        TeacherWorkspaceService $workspace,
        TeacherAccessService $access
    ): View|RedirectResponse {
        abort_unless($access->canManageExam(request()->user(), $exam), 403);

        if ($exam->attempts()->exists()) {
            return redirect()->route('teacher.exams.index')
                ->with('warning', 'این آزمون قبلاً شروع شده است؛ برای حفظ صحت پاسخ‌ها و نمره‌ها، متن و کلید آن قفل شده است.');
        }

        $exam->load(['questions.media']);
        $initialQuestions = $exam->questions
            ->sortBy('sort_order')
            ->map(function ($question): array {
                $correctAnswer = $question->correct_answer;
                if (in_array($question->type, ['multiple', 'checkbox'], true) && is_string($correctAnswer)) {
                    $decoded = json_decode($correctAnswer, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $correctAnswer = $decoded;
                    }
                }

                $promptAttachment = $question->media->firstWhere('pivot.collection', 'exam-question-prompt');

                return [
                    'id' => $question->id,
                    'attachment_name' => $promptAttachment?->original_name,
                    'attachment_url' => $promptAttachment ? route('media.view', $promptAttachment) : null,
                    // The legacy database default represents a single-answer
                    // choice question; map it into the current four-choice editor.
                    'type' => $question->type === 'multiple_choice' ? 'single' : $question->type,
                    'question' => $question->question,
                    'options' => $question->options ?? [],
                    'correct_answer' => $correctAnswer,
                    'expected_answer' => $question->expected_answer,
                    'score' => $question->score,
                ];
            })
            ->values()
            ->all();

        return view('teacher.exams.form', [
            'courses' => $workspace->courses(request()->user()),
            'classrooms' => $workspace->classrooms(request()->user()),
            'exam' => $exam,
            'initialQuestions' => $initialQuestions,
        ]);
    }

    public function update(
        StoreExamRequest $request,
        Exam $exam,
        TeacherWorkspaceService $workspace,
        TeacherAccessService $access,
        ExamQuestionWriter $questionWriter,
        MediaService $media
    ): RedirectResponse {
        abort_unless($access->canManageExam($request->user(), $exam), 403);

        $courseId = (int) $request->validated('course_id');
        $course = $workspace->courseOwnedBy($request->user(), $courseId);

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedByCourse($request->user(), (int) $classroomId, $courseId);
        }

        $payload = $request->safe()->except(['course_id', 'questions']);
        $payload['course_id'] = $course->id;
        $payload['teacher_id'] = $request->user()->id;

        DB::transaction(function () use ($exam, $payload, $request, $questionWriter, $access, $media): void {
            $lockedExam = Exam::query()
                ->whereKey($exam->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($access->canManageExam($request->user(), $lockedExam), 403);

            if ($lockedExam->attempts()->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'یک دانش‌آموز این آزمون را شروع کرده است؛ برای حفظ پاسخ‌ها و نمره‌ها، دیگر نمی‌توان برگه را تغییر داد.',
                ]);
            }

            $lockedExam->update($payload);
            $questionWriter->storeQuestions($lockedExam->refresh(), $request->validated('questions', []), $media);
        });

        return redirect()->route('teacher.exams.index')
            ->with('success', 'ویرایش برگهٔ آزمون با موفقیت ذخیره شد.');
    }

    public function attempts(Exam $exam, TeacherAccessService $access): View
    {
        abort_unless($access->canManageExam(request()->user(), $exam), 403);

        $exam->load('questions');

        $attempts = $exam->attempts()
            ->with(['student:id,name', 'answers.question', 'answers.media', 'exam.questions.media'])
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.exams.attempts', compact('exam', 'attempts'));
    }
}
