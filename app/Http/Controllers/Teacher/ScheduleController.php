<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreScheduleRequest;
use App\Http\Requests\Teacher\UpdateScheduleRequest;
use App\Models\ClassSchedule;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.schedule.index', [
            'classrooms' => $workspace->classrooms(request()->user()),
        ]);
    }

    public function store(
        StoreScheduleRequest $request,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $classroom = $workspace->classroomOwnedBy(
            $request->user(),
            (int) $request->validated('classroom_id')
        );

        $classroom->schedules()->create($request->validated());

        return back()->with('success', 'جلسه هفتگی ثبت شد.');
    }

    public function update(
        UpdateScheduleRequest $request,
        ClassSchedule $schedule,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->updateSchedule(
            $request->user(),
            $schedule,
            $request->validated()
        );

        return back()->with('success', 'برنامه جلسه به‌روزرسانی شد.');
    }

    public function destroy(
        ClassSchedule $schedule,
        TeacherWorkspaceService $workspace
    ): RedirectResponse {
        $workspace->deleteSchedule(request()->user(), $schedule);

        return back()->with('success', 'جلسه از برنامه حذف شد.');
    }
}
