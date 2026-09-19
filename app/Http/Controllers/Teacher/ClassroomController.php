<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassroomRequest;
use App\Models\Classroom;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.classrooms.index', [
            'classrooms' => $workspace->classrooms(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.classrooms.form', [
            'courses' => $workspace->courses(request()->user()),
            'classroom' => new Classroom(),
        ]);
    }

    public function store(
        StoreClassroomRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        $classroom = $course->classrooms()->create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'title' => $request->validated('title'),
            'code' => $request->validated('code'),
            'description' => $request->validated('description'),
            'capacity' => $request->validated('capacity'),
            'status' => 'active',
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
        ]);

        $classroom->teachers()->syncWithoutDetaching([$request->user()->id]);

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'کلاس با موفقیت ساخته شد.');
    }
}
