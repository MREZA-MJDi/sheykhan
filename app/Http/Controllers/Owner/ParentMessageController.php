<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\ParentMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ParentMessageController extends Controller
{
    public function index(Academy $academy, Request $request): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy);

        return redirect()->route('owner.communication-workspace', [$academy, 'tab' => 'messages']);
    }

    public function reply(Request $request, Academy $academy, int $message): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, manage: true);

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

    private function authorizeAcademy(Request $request, Academy $academy, bool $manage = false): void
    {
        $user = $request->user();

        abort_unless(
            $user
                && $user->hasRole('academy-owner')
                && $user->hasPermission($manage ? 'parents.manage' : 'parents.view')
                && (int) $academy->owner_id === (int) $user->id,
            404
        );
    }
}
