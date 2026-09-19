<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Exam\StoreExamRequest;
use App\Models\Exam;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
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
            $workspace->classroomOwnedBy($request->user(), (int) $classroomId);
        }

        $payload = $request->safe()->except(['course_id', 'questions']);
        $payload['teacher_id'] = $request->user()->id;

        $exam = $course->exams()->create($payload);

        foreach ($request->validated('questions', []) as $index => $question) {
            $exam->questions()->create([
                'type' => $question['type'] ?? 'text',
                'question' => $question['question'],
                'options' => $question['options'] ?? null,
                'correct_answer' => $question['correct_answer'] ?? null,
                'score' => $question['score'] ?? 1,
                'sort_order' => $index,
            ]);
        }

        return redirect()->route('teacher.exams.index')
            ->with('success', 'آزمون با موفقیت ساخته شد.');
    }

    public function attempts(Exam $exam): View
    {
        abort_unless($exam->teacher_id === request()->user()->id, 403);

        return view('teacher.exams.attempts', [
            'exam' => $exam->load([
                'questions',
                'attempts' => fn ($query) => $query
                    ->with(['student:id,name', 'answers.question'])
                    ->latest('submitted_at'),
            ]),
        ]);
    }
}
