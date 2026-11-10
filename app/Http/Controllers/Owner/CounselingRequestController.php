<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\CounselingRequest as ParentCounselingRequest;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

final class CounselingRequestController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): RedirectResponse
    {
        $workspace->authorizeAcademy($request->user(), $academy, 'parents.view');

        return redirect()->route('owner.communication-workspace', [$academy, 'tab' => 'counseling']);
    }

    public function update(Request $request, Academy $academy, int $counselingRequest, OwnerWorkspaceService $workspace): RedirectResponse
    {
        $workspace->authorizeAcademy($request->user(), $academy, 'parents.manage');

        $data = $request->validate([
            'status' => ['required', 'string', 'in:scheduled,completed,declined,cancelled'],
            'scheduled_at' => ['nullable', 'date', Rule::requiredIf(fn () => $request->input('status') === 'scheduled'), Rule::when($request->input('status') === 'scheduled', ['after:now'])],
            'response_notes' => ['nullable', 'string', 'max:3000'],
        ], [
            'scheduled_at.required_if' => 'برای تأیید جلسه، زمان پیشنهادی را مشخص کنید.',
        ]);

        $record = ParentCounselingRequest::query()
            ->where('academy_id', $academy->id)
            ->findOrFail($counselingRequest);

        $record->forceFill([
            'status' => $data['status'],
            'scheduled_at' => $data['status'] === 'scheduled' ? $data['scheduled_at'] : $record->scheduled_at,
            'response_notes' => $data['response_notes'] ?? null,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ])->save();

        return redirect()->route('owner.communication-workspace', [$academy, 'tab' => 'counseling'])
            ->with('success', 'وضعیت درخواست مشاوره ذخیره شد.');
    }

}
