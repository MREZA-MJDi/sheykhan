<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreAcademyMeetingRequest;
use App\Models\Academy;
use App\Models\AcademyMeeting;
use App\Services\OwnerMeetingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class MeetingController extends Controller
{
    public function index(Academy $academy, OwnerMeetingService $meetings): View
    {
        return view('owner.meetings.index', $meetings->index(request()->user(), $academy));
    }

    public function store(
        StoreAcademyMeetingRequest $request,
        Academy $academy,
        OwnerMeetingService $meetings
    ): RedirectResponse {
        $meetings->create($request->user(), $academy, $request->validated());

        return redirect()->route('owner.meetings.index', $academy)
            ->with('success', 'جلسه ثبت شد؛ زمان و پیام آن در کارتابل دانش‌آموز و ولی انتخاب‌شده نمایش داده می‌شود.');
    }

    public function updateStatus(
        Request $request,
        Academy $academy,
        AcademyMeeting $meeting,
        OwnerMeetingService $meetings
    ): RedirectResponse {
        $data = $request->validate([
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled', 'declined'])],
            'scheduled_at' => [Rule::requiredIf(fn () => $meeting->status === 'proposed' && $request->input('status') === 'scheduled'), 'nullable', 'date', 'after:now'],
            'scheduled_end_at' => ['nullable', 'date', 'after:scheduled_at'],
        ], [
            'scheduled_at.required' => 'برای تأیید پیشنهاد مدرس، زمان نهایی را مشخص کن.',
            'scheduled_at.after' => 'زمان جلسه باید در آینده باشد.',
            'scheduled_end_at.after' => 'زمان پایان باید بعد از زمان شروع باشد.',
        ]);

        $meetings->changeStatus($request->user(), $academy, $meeting, $data['status'], $data['scheduled_at'] ?? null, $data['scheduled_end_at'] ?? null);

        return redirect()->route('owner.meetings.index', $academy)
            ->with('success', 'وضعیت جلسه به‌روزرسانی شد.');
    }
}
