<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\ParentMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ParentMessageController extends Controller
{
    public function index(Academy $academy, Request $request): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy);

        return redirect()->route('owner.communication-workspace', [$academy, 'tab' => 'messages']);
    }

    public function reply(Request $request, Academy $academy, int $message): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:in_review,responded,closed'],
            'response' => ['nullable', 'string', 'max:5000', 'required_if:status,responded'],
        ], [
            'response.required_if' => 'برای ثبت پاسخ، متن پاسخ را بنویسید.',
        ]);

        $record = ParentMessage::query()
            ->where('academy_id', $academy->id)
            ->findOrFail($message);

        $record->forceFill([
            'status' => $data['status'],
            'response' => filled($data['response'] ?? null) ? trim($data['response']) : $record->response,
            'responded_by' => $data['status'] === 'responded' ? $request->user()->id : $record->responded_by,
            'responded_at' => $data['status'] === 'responded' ? now() : $record->responded_at,
        ])->save();

        return redirect()->route('owner.communication-workspace', [$academy, 'tab' => 'messages'])
            ->with('success', 'وضعیت و پاسخ پیام ذخیره شد.');
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
