<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\AcademyMeeting;
use App\Models\AcademyMessage;
use App\Models\CounselingRequest;
use App\Models\ParentMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CommunicationWorkspaceController extends Controller
{
    public function __invoke(Academy $academy, Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasRole('academy-owner') && (int) $academy->owner_id === (int) $user->id,
            404
        );

        $canViewFamilies = $user->hasPermission('parents.view');
        $canManageFamilies = $user->hasPermission('parents.manage');
        $canViewMeetings = $user->hasPermission('live_classes.view');
        $canManageMeetings = $user->hasPermission('live_classes.manage');

        abort_unless($canViewFamilies || $canViewMeetings, 403);

        $tabs = [
            'overview' => ['label' => 'نمای کلی', 'allowed' => true],
            'messages' => ['label' => 'پیام خانواده‌ها', 'allowed' => $canViewFamilies],
            'announcements' => ['label' => 'اطلاعیه‌ها', 'allowed' => $canViewFamilies],
            'counseling' => ['label' => 'مشاوره‌ها', 'allowed' => $canViewFamilies],
            'meetings' => ['label' => 'جلسات', 'allowed' => $canViewMeetings],
        ];

        $tab = (string) $request->query('tab', 'overview');
        abort_unless(isset($tabs[$tab]) && $tabs[$tab]['allowed'], 404);

        $data = compact('academy', 'tabs', 'tab', 'canViewFamilies', 'canManageFamilies', 'canViewMeetings', 'canManageMeetings');

        if ($canViewFamilies) {
            $data['stats'] = [
                'unanswered' => ParentMessage::query()->where('academy_id', $academy->id)->whereIn('status', ['open', 'in_review'])->count(),
                'counseling' => CounselingRequest::query()->where('academy_id', $academy->id)->whereIn('status', ['requested', 'proposed'])->count(),
                'announcements' => AcademyMessage::query()->where('academy_id', $academy->id)->count(),
            ];
        }

        if ($canViewMeetings) {
            $data['stats']['upcoming'] = AcademyMeeting::query()
                ->where('academy_id', $academy->id)
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->count();
        }

        if ($tab === 'messages') {
            $filters = $request->validate([
                'status' => ['nullable', 'in:open,in_review,responded,closed'],
                'category' => ['nullable', 'in:question,suggestion,complaint,other'],
                'q' => ['nullable', 'string', 'max:120'],
            ]);
            $data['filters'] = $filters;
            $data['items'] = ParentMessage::query()
                ->where('academy_id', $academy->id)
                ->with(['parent:id,name,mobile,email', 'student:id,name', 'responder:id,name'])
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
                ->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))
                ->when(filled($filters['q'] ?? null), function ($q) use ($filters): void {
                    $term = trim($filters['q']);
                    $q->where(function ($nested) use ($term): void {
                        $nested->where('subject', 'like', '%' . $term . '%')
                            ->orWhere('body', 'like', '%' . $term . '%')
                            ->orWhereHas('parent', fn ($parent) => $parent->where('name', 'like', '%' . $term . '%'))
                            ->orWhereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%'));
                    });
                })
                ->latest()
                ->paginate(15, ['*'], 'messages_page')
                ->withQueryString();
        } elseif ($tab === 'announcements') {
            $data['items'] = AcademyMessage::query()
                ->where('academy_id', $academy->id)
                ->with(['sender:id,name', 'recipient:id,name', 'student:id,name', 'classroom:id,title'])
                ->latest()
                ->paginate(12, ['*'], 'announcements_page')
                ->withQueryString();
        } elseif ($tab === 'counseling') {
            $filters = $request->validate([
                'status' => ['nullable', 'in:requested,proposed,scheduled,completed,declined,cancelled'],
                'q' => ['nullable', 'string', 'max:120'],
            ]);
            $data['filters'] = $filters;
            $data['items'] = CounselingRequest::query()
                ->where('academy_id', $academy->id)
                ->with(['parent:id,name,mobile', 'student:id,name', 'handler:id,name', 'proposer:id,name'])
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
                ->when(filled($filters['q'] ?? null), function ($q) use ($filters): void {
                    $term = trim($filters['q']);
                    $q->where(function ($nested) use ($term): void {
                        $nested->where('subject', 'like', '%' . $term . '%')
                            ->orWhere('details', 'like', '%' . $term . '%')
                            ->orWhereHas('parent', fn ($parent) => $parent->where('name', 'like', '%' . $term . '%'))
                            ->orWhereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%'));
                    });
                })
                ->orderByRaw("CASE WHEN status = 'requested' THEN 0 WHEN status = 'proposed' THEN 1 WHEN status = 'scheduled' THEN 2 ELSE 3 END")
                ->latest()
                ->paginate(15, ['*'], 'counseling_page')
                ->withQueryString();
        } elseif ($tab === 'meetings') {
            $filters = $request->validate([
                'status' => ['nullable', 'in:proposed,scheduled,completed,cancelled,declined'],
                'q' => ['nullable', 'string', 'max:120'],
            ]);
            $data['filters'] = $filters;
            $data['items'] = AcademyMeeting::query()
                ->where('academy_id', $academy->id)
                ->with(['classroom:id,title', 'course:id,title', 'student:id,name', 'guardian:id,name'])
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
                ->when(filled($filters['q'] ?? null), function ($q) use ($filters): void {
                    $term = trim($filters['q']);
                    $q->where(function ($nested) use ($term): void {
                        $nested->where('title', 'like', '%' . $term . '%')
                            ->orWhereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%'))
                            ->orWhereHas('guardian', fn ($parent) => $parent->where('name', 'like', '%' . $term . '%'));
                    });
                })
                ->orderByRaw("CASE WHEN status = 'proposed' THEN 0 WHEN status = 'scheduled' THEN 1 ELSE 2 END")
                ->orderBy('scheduled_at')
                ->paginate(15, ['*'], 'meetings_page')
                ->withQueryString();
        }

        return view('owner.communication-workspace.index', $data);
    }
}
