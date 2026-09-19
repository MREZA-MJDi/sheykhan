<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassroomRequest;
use App\Http\Requests\Teacher\UpdateClassroomRequest;
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
            'classroom' => new Classroom(['status' => 'active']),
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

        return redirect()->route('teacher.classrooms.show', $classroom)
            ->with('success', 'کلاس با موفقیت ساخته شد.');
    }

    public function show(
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): View {
        return view('teacher.classrooms.show', [
            'classroom' => $workspace->classroomDetails(request()->user(), $classroom),
        ]);
    }

    public function edit(
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): View {
        $classroom = $workspace->classroomOwnedBy(request()->user(), $classroom->id);

        return view('teacher.classrooms.form', [
            'courses' => $workspace->courses(request()->user()),
            'classroom' => $classroom,
        ]);
    }

    public function update(
        UpdateClassroomRequest $request,
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        try {
            $workspace->updateClassroom(
                $request->user(),
                $classroom,
                $request->validated()
            );
        } catch (\LogicException $exception) {
            return back()->withInput()->withErrors(['classroom' => $exception->getMessage()]);
        }

        return redirect()->route('teacher.classrooms.show', $classroom)
            ->with('success', 'تنظیمات کلاس به‌روزرسانی شد.');
    }

    public function archive(
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        try {
            $workspace->updateClassroom(request()->user(), $classroom, [
                'course_id' => $classroom->course_id,
                'title' => $classroom->title,
                'code' => $classroom->code,
                'description' => $classroom->description,
                'capacity' => $classroom->capacity,
                'starts_at' => $classroom->starts_at,
                'ends_at' => $classroom->ends_at,
                'status' => 'archived',
            ]);
        } catch (\LogicException $exception) {
            return back()->withErrors(['classroom' => $exception->getMessage()]);
        }

        return back()->with('success', 'کلاس آرشیو شد.');
    }
}
