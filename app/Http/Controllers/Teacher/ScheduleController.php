<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreScheduleRequest;
use App\Services\TeacherWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(TeacherWorkspaceService $workspace): View
    {
        return view('teacher.schedule.index', [
            'classrooms' => $workspace->classroomsForSchedule(request()->user()),
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

        $workspace->storeSchedule(
            $request->user(),
            $classroom,
            $request->validated(),
        );

        return back()->with('success', 'جلسه هفتگی ثبت شد.');
    }

    public function destroy(\App\Models\ClassSchedule $schedule, TeacherWorkspaceService $workspace): RedirectResponse
    {
        $workspace->deleteSchedule(request()->user(), $schedule->id);

        return back()->with('success', 'زمان هفتگی حذف شد.');
    }
}
