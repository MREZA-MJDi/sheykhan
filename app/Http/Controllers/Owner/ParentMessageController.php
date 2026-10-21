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
    public function index(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy);

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'in:open,in_review,responded,closed'],
            'category' => ['nullable', 'string', 'in:question,suggestion,complaint,other'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $messages = ParentMessage::query()
            ->where('academy_id', $academy->id)
            ->with(['parent:id,name,mobile', 'student:id,name', 'responder:id,name'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);
                $query->where(function ($nested) use ($term): void {
                    $nested->where('subject', 'like', '%' . $term . '%')
                        ->orWhere('body', 'like', '%' . $term . '%')
                        ->orWhereHas('parent', fn ($parent) => $parent->where('name', 'like', '%' . $term . '%'))
                        ->orWhereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'open' => ParentMessage::query()->where('academy_id', $academy->id)->where('status', 'open')->count(),
            'in_review' => ParentMessage::query()->where('academy_id', $academy->id)->where('status', 'in_review')->count(),
            'responded' => ParentMessage::query()->where('academy_id', $academy->id)->where('status', 'responded')->count(),
        ];

        return view('owner.messages.index', compact('academy', 'messages', 'filters', 'stats'));
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

        return redirect()->route('owner.messages.index', $academy)
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
