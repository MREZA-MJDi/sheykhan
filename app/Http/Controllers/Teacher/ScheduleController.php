<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreScheduleRequest;
use App\Services\TeacherWorkspaceService;
use Illuminate\Support\Str;
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

        $data = $request->validated();

        // A recurring schedule gets its own stable virtual room automatically.
        // We use Jitsi because it requires no external account/API integration.
        $data['meeting_url'] = 'https://meet.jit.si/Sheykhan-'
            . Str::slug((string) ($classroom->code ?: $classroom->id))
            . '-'
            . Str::lower(Str::random(12));
        $data['provider'] = 'Jitsi';

        $workspace->storeSchedule(
            $request->user(),
            $classroom,
            $data,
        );

        return back()->with('success', 'جلسه هفتگی ثبت شد.');
    }

    public function destroy(\App\Models\ClassSchedule $schedule, TeacherWorkspaceService $workspace): RedirectResponse
    {
        $workspace->deleteSchedule(request()->user(), $schedule->id);

        return back()->with('success', 'زمان هفتگی حذف شد.');
    }
}
