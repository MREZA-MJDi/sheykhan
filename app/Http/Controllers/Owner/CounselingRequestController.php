<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\CounselingRequest as ParentCounselingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

final class CounselingRequestController extends Controller
{
    public function index(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy);

        $requests = ParentCounselingRequest::query()
            ->where('academy_id', $academy->id)
            ->with(['parent:id,name,mobile', 'student:id,name', 'handler:id,name', 'proposer:id,name'])
            ->orderByRaw("CASE WHEN status = 'requested' THEN 0 WHEN status = 'scheduled' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'requested' => ParentCounselingRequest::query()->where('academy_id', $academy->id)->where('status', 'requested')->count(),
            'proposed' => ParentCounselingRequest::query()->where('academy_id', $academy->id)->where('status', 'proposed')->count(),
            'scheduled' => ParentCounselingRequest::query()->where('academy_id', $academy->id)->where('status', 'scheduled')->count(),
            'completed' => ParentCounselingRequest::query()->where('academy_id', $academy->id)->where('status', 'completed')->count(),
        ];

        return view('owner.counseling.index', compact('academy', 'requests', 'stats'));
    }

    public function update(Request $request, Academy $academy, int $counselingRequest): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy);

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

        return redirect()->route('owner.counseling.index', $academy)
            ->with('success', 'وضعیت درخواست مشاوره ذخیره شد.');
    }

    private function authorizeAcademy(Request $request, Academy $academy): void
    {
        abort_unless(
            $request->user()->hasRole('academy-owner')
            && $request->user()->hasPermission('parents.view')
            && (int) $academy->owner_id === (int) $request->user()->id,
            404
        );
    }
}
