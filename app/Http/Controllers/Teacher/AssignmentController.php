<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Assignment\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.assignments.index', [
            'assignments' => $workspace->assignments(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.assignments.form', [
            'courses' => $workspace->courses(request()->user()),
            'classrooms' => $workspace->classrooms(request()->user()),
            'assignment' => new Assignment(['status' => 'draft', 'max_score' => 20]),
        ]);
    }

    public function store(
        StoreAssignmentRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedByCourse(
                $request->user(),
                (int) $classroomId,
                $course->id
            );
        }

        $payload = $request->safe()->except('course_id');
        $payload['teacher_id'] = $request->user()->id;

        $course->assignments()->create($payload);

        return redirect()->route('teacher.assignments.index')
            ->with('success', 'تکلیف با موفقیت ساخته شد.');
    }

    public function submissions(
        Assignment $assignment,
        TeacherWorkspaceService $workspace
    ): View {
        abort_unless($assignment->teacher_id === request()->user()->id, 403);

        return view('teacher.assignments.submissions', [
            'assignment' => $assignment->load([
                'classroom:id,title',
                'submissions' => fn ($query) => $query
                    ->with('student:id,name')
                    ->latest('submitted_at'),
            ]),
        ]);
    }
}
