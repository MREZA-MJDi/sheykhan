<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Exam\StoreExamRequest;
use App\Models\Exam;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
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
        ]);
    }

    public function store(
        StoreExamRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedByCourse($request->user(), (int) $classroomId, (int) $request->validated('course_id'));
        }

        $payload = $request->safe()->except(['course_id', 'questions']);
        $payload['teacher_id'] = $request->user()->id;

        DB::transaction(function () use ($course, $payload, $request): void {
            $exam = $course->exams()->create($payload);

            foreach ($request->validated('questions', []) as $index => $question) {
                $type = $question['type'] ?? 'text';
                $options = $type === 'text'
                    ? null
                    : array_values(array_filter(
                        (array) ($question['options'] ?? []),
                        static fn ($option) => is_string($option) && trim($option) !== ''
                    ));

                $correctAnswer = $type === 'text'
                    ? null
                    : ($question['correct_answer'] ?? null);

                // The database stores the expected answer in a text column.
                // Multi-answer questions are stored as JSON so the grading
                // service can compare a normalized list of selected options.
                if (is_array($correctAnswer)) {
                    $correctAnswer = json_encode(array_values($correctAnswer), JSON_UNESCAPED_UNICODE);
                }

                $exam->questions()->create([
                    'type' => $type,
                    'question' => $question['question'],
                    'options' => $options,
                    'correct_answer' => $correctAnswer,
                    'score' => $question['score'] ?? 1,
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()->route('teacher.exams.index')
            ->with('success', 'آزمون با موفقیت ساخته شد.');
    }

    public function attempts(Exam $exam, \App\Services\TeacherAccessService $access): View
    {
        abort_unless($access->canManageExam(request()->user(), $exam), 403);

        $exam->load('questions');

        $attempts = $exam->attempts()
            ->with(['student:id,name', 'answers.question', 'exam.questions'])
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.exams.attempts', compact('exam', 'attempts'));
    }
}
