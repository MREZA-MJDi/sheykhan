<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademyMeeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class MeetingController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        $meetings = AcademyMeeting::query()
            ->where(function ($query) use ($teacher): void {
                $query->whereHas('classroom.teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
                    ->orWhereHas('course.teachers', fn ($teachers) => $teachers->whereKey($teacher->id));
            })
            ->with(['academy:id,name', 'classroom:id,title', 'course:id,title', 'student:id,name', 'guardian:id,name'])
            ->orderByRaw("CASE WHEN status = 'scheduled' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.meetings.index', compact('meetings'));
    }

    public function updateStatus(Request $request, int $meeting): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:completed,cancelled'],
        ]);

        $teacher = $request->user();
        $record = AcademyMeeting::query()
            ->whereKey($meeting)
            ->where(function ($query) use ($teacher): void {
                $query->whereHas('classroom.teachers', fn ($teachers) => $teachers->whereKey($teacher->id))
                    ->orWhereHas('course.teachers', fn ($teachers) => $teachers->whereKey($teacher->id));
            })
            ->firstOrFail();

        abort_unless($record->status === 'scheduled', 422, 'فقط جلسه‌های برنامه‌ریزی‌شده قابل تغییر هستند.');
        $record->forceFill(['status' => $data['status']])->save();

        return redirect()->route('teacher.meetings.index')
            ->with('success', $data['status'] === 'completed' ? 'جلسه به‌عنوان برگزارشده ثبت شد.' : 'جلسه لغو شد.');
    }
}
