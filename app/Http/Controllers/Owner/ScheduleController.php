<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerScheduleRequest;
use App\Models\Academy;
use App\Models\ClassSchedule;
use App\Models\Classroom;
use App\Services\MeetingLinkService;
use App\Services\OwnerScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ScheduleController extends Controller
{
    public function index(Academy $academy, OwnerScheduleService $schedules): View
    {
        return view('owner.schedules.index', $schedules->index(request()->user(), $academy));
    }

    public function store(
        StoreOwnerScheduleRequest $request,
        Academy $academy,
        Classroom $classroom,
        OwnerScheduleService $schedules,
        MeetingLinkService $links
    ): RedirectResponse {
        $schedules->create(
            $request->user(),
            $academy,
            $classroom,
            $request->validated(),
            $links
        );

        return redirect()->route('owner.schedules.index', $academy)
            ->with('success', 'زمان هفتگی و لینک ورود کلاس ذخیره شد.');
    }

    public function destroy(
        Request $request,
        Academy $academy,
        Classroom $classroom,
        ClassSchedule $schedule,
        OwnerScheduleService $schedules
    ): RedirectResponse {
        $schedules->delete($request->user(), $academy, $classroom, $schedule);

        return redirect()->route('owner.schedules.index', $academy)
            ->with('success', 'زمان هفتگی حذف شد.');
    }
}
