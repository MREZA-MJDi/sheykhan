<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLiveClassRequest;
use App\Models\LiveClass;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiveClassController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.live-classes.index', [
            'liveClasses' => $workspace->liveClasses(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.live-classes.form', [
            'courses' => $workspace->courses(request()->user()),
            'classrooms' => $workspace->classrooms(request()->user()),
            'liveClass' => new LiveClass(['duration_minutes' => 60, 'status' => 'scheduled']),
        ]);
    }

    public function store(
        StoreLiveClassRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        $classroomId = $request->validated('classroom_id');
        if ($classroomId) {
            $workspace->classroomOwnedBy($request->user(), (int) $classroomId);
        }

        $payload = $request->safe()->except('course_id');
        $payload['teacher_id'] = $request->user()->id;

        $course->liveClasses()->create($payload);

        return redirect()->route('teacher.live-classes.index')
            ->with('success', 'جلسه آنلاین ثبت شد.');
    }
}
