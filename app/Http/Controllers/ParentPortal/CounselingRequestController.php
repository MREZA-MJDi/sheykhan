<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\CounselingRequest;
use App\Services\ParentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CounselingRequestController extends Controller
{
    public function index(Request $request, ParentPortalService $portal): View
    {
        $requests = CounselingRequest::query()
            ->where('parent_id', $request->user()->id)
            ->with(['student:id,name', 'academy:id,name', 'handler:id,name'])
            ->orderByRaw("CASE WHEN status = 'requested' THEN 0 WHEN status = 'scheduled' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('parent.counseling.index', [
            'requests' => $requests,
            'contactableChildren' => $portal->contactableChildren($request->user()),
        ]);
    }

    public function store(Request $request, ParentPortalService $portal): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^\d+:\d+$/'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'details' => ['nullable', 'string', 'max:5000'],
            'preferred_at' => ['nullable', 'date', 'after:now'],
        ], [
            'target.required' => 'فرزند و آموزشگاه مرتبط را انتخاب کنید.',
            'subject.required' => 'موضوع درخواست را وارد کنید.',
            'preferred_at.after' => 'زمان پیشنهادی باید در آینده باشد.',
        ]);

        [$studentId, $academyId] = array_map('intval', explode(':', $data['target'], 2));
        [$student, $academy] = $portal->resolveTarget($request->user(), $studentId, $academyId);

        CounselingRequest::query()->create([
            'academy_id' => $academy->id,
            'parent_id' => $request->user()->id,
            'student_id' => $student->id,
            'subject' => trim($data['subject']),
            'details' => filled($data['details'] ?? null) ? trim($data['details']) : null,
            'preferred_at' => $data['preferred_at'] ?? null,
            'status' => 'requested',
        ]);

        return redirect()->route('parent.counseling.index')
            ->with('success', 'درخواست مشاوره ثبت شد؛ نتیجهٔ پیگیری در همین صفحه نمایش داده می‌شود.');
    }
}
