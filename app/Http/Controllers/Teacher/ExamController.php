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
use Throwable;

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

        $payload = $request->safe()->except(['course_id', 'questions', 'handout']);
        $payload['teacher_id'] = $request->user()->id;
        $uploaded = [];

        try {
            $exam = DB::transaction(function () use ($course, $payload, $request, $questionWriter, $media, &$uploaded): Exam {
                $exam = $course->exams()->create($payload);
                $questionWriter->storeQuestions($exam, $request->validated('questions', []));

                if ($request->hasFile('handout')) {
                    $uploaded[] = $media->upload($request->file('handout'), $exam, [
                        'disk' => 'local',
                        'directory' => 'exams/' . $exam->id,
                        'collection' => 'exam-handout',
                        'visibility' => 'private',
                    ]);
                }

                return $exam;
            }, 3);
        } catch (Throwable $exception) {
            foreach ($uploaded as $file) {
                $media->delete($file);
            }

            throw $exception;
        }

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

        $exam->load(['questions', 'media']);
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

                return [
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

        $payload = $request->safe()->except(['course_id', 'questions', 'handout']);
        $payload['course_id'] = $course->id;
        $payload['teacher_id'] = $request->user()->id;
        $oldHandouts = $exam->media()->wherePivot('collection', 'exam-handout')->get();
        $uploaded = [];

        try {
            DB::transaction(function () use ($exam, $payload, $request, $questionWriter, $access, $media, $oldHandouts, &$uploaded): void {
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
                $lockedExam->questions()->delete();
                $questionWriter->storeQuestions($lockedExam->refresh(), $request->validated('questions', []));

                if ($request->hasFile('handout')) {
                    $uploaded[] = $media->upload($request->file('handout'), $lockedExam, [
                        'disk' => 'local',
                        'directory' => 'exams/' . $lockedExam->id,
                        'collection' => 'exam-handout',
                        'visibility' => 'private',
                    ]);

                    foreach ($oldHandouts as $oldHandout) {
                        $media->detach($oldHandout, $lockedExam);
                    }
                }
            }, 3);
        } catch (Throwable $exception) {
            foreach ($uploaded as $file) {
                try {
                    $media->delete($file);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        if ($uploaded !== []) {
            foreach ($oldHandouts as $oldHandout) {
                if ($oldHandout->attachments()->doesntExist()) {
                    $media->delete($oldHandout);
                }
            }
        }

        return redirect()->route('teacher.exams.index')
            ->with('success', 'ویرایش برگهٔ آزمون با موفقیت ذخیره شد.');
    }

    public function attempts(Exam $exam, TeacherAccessService $access): View
    {
        abort_unless($access->canManageExam(request()->user(), $exam), 403);

        $exam->load(['questions', 'media']);

        $attempts = $exam->attempts()
            ->with(['student:id,name', 'answers.question', 'exam.questions', 'media:id,original_name,mime_type,extension,size,status'])
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.exams.attempts', compact('exam', 'attempts'));
    }
}
