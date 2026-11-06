<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreClassroomRequest;
use App\Http\Requests\Teacher\UpdateClassroomRequest;
use App\Models\Classroom;
use App\Services\TeacherWorkspaceService;
use App\Services\ClassroomCodeGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.classrooms.index', [
            'classrooms' => $workspace->classroomsPaginated(request()->user()),
        ]);
    }

    public function create(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.classrooms.form', [
            'courses' => $workspace->courses(request()->user()),
            'classroom' => new Classroom(['code' => app(ClassroomCodeGenerator::class)->generate()]),
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
            'code' => app(ClassroomCodeGenerator::class)->resolve($request->validated('code') ?: null),
            'description' => $request->validated('description'),
            'capacity' => $request->validated('capacity'),
            'status' => 'active',
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
        ]);

        $classroom->teachers()->syncWithoutDetaching([$request->user()->id]);

        if ($classroom->starts_at) {
            $starts = $classroom->starts_at;
            $ends = $classroom->ends_at;
            $startTime = $starts->format('H:i');
            $endTime = ($ends && $ends->format('H:i') > $startTime)
                ? $ends->format('H:i')
                : $starts->copy()->addMinutes(90)->format('H:i');

            $classroom->schedules()->create([
                'weekday' => $starts->dayOfWeek,
                'start_time' => $startTime . ':00',
                'end_time' => $endTime . ':00',
                'provider' => \App\Services\MeetingLinkService::JITSI,
                'meeting_url' => app(\App\Services\MeetingLinkService::class)->generateJitsiUrl(),
            ]);
        }

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'کلاس با موفقیت ساخته شد.');
    }

    public function edit(Classroom $classroom, TeacherWorkspaceService $workspace): View
    {
        $classroomModel = $workspace->classroomOwnedBy(request()->user(), $classroom->id);

        return view('teacher.classrooms.form', [
            'courses' => $workspace->courses(request()->user()),
            'classroom' => $classroomModel,
        ]);
    }

    public function update(
        UpdateClassroomRequest $request,
        Classroom $classroom,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $classroomModel = $workspace->classroomOwnedBy($request->user(), $classroom->id);
        $course = $workspace->courseOwnedBy($request->user(), (int) $request->validated('course_id'));

        $classroomModel->update([
            'course_id' => $course->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'capacity' => $request->validated('capacity'),
            'status' => $request->validated('status'),
            'starts_at' => $request->validated('starts_at'),
            'ends_at' => $request->validated('ends_at'),
        ]);

        if ($classroomModel->starts_at && $classroomModel->schedules()->count() === 0) {
            $starts = $classroomModel->starts_at;
            $ends = $classroomModel->ends_at;
            $startTime = $starts->format('H:i');
            $endTime = ($ends && $ends->format('H:i') > $startTime)
                ? $ends->format('H:i')
                : $starts->copy()->addMinutes(90)->format('H:i');

            $classroomModel->schedules()->create([
                'weekday' => $starts->dayOfWeek,
                'start_time' => $startTime . ':00',
                'end_time' => $endTime . ':00',
                'provider' => \App\Services\MeetingLinkService::JITSI,
                'meeting_url' => app(\App\Services\MeetingLinkService::class)->generateJitsiUrl(),
            ]);
        }

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'اطلاعات کلاس با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Classroom $classroom, TeacherWorkspaceService $workspace): RedirectResponse
    {
        $classroomModel = $workspace->classroomOwnedBy(request()->user(), $classroom->id);

        DB::transaction(function () use ($classroomModel): void {
            $classroomModel->delete();
        });

        return redirect()->route('teacher.classrooms.index')
            ->with('success', 'کلاس با موفقیت حذف شد.');
    }
}
