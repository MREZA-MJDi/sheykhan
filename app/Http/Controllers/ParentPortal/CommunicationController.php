<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\CounselingRequest;
use App\Models\ParentMessage;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class CommunicationController extends Controller
{
    public function messages(Request $request): View
    {
        $parent = $request->user();

        return view('parent.messages.index', [
            'targets' => $this->availableTargets($parent),
            'messages' => ParentMessage::query()
                ->where('parent_id', $parent->id)
                ->with(['academy:id,name', 'student:id,name', 'responder:id,name'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function storeMessage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^\d+:\d+$/'],
            'category' => ['required', 'string', 'in:question,suggestion,complaint,other'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'target.required' => 'فرزند و آموزشگاه را انتخاب کنید.',
            'body.min' => 'پیام باید دست‌کم ۱۰ نویسه داشته باشد.',
        ]);

        [$academyId, $studentId] = $this->parseTarget($data['target']);
        $parent = $request->user();
        $this->assertParentTarget($parent, $academyId, $studentId);

        ParentMessage::query()->create([
            'academy_id' => $academyId,
            'parent_id' => $parent->id,
            'student_id' => $studentId,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'status' => 'open',
        ]);

        return redirect()->route('parent.messages.index')
            ->with('success', 'پیام شما با موفقیت ثبت شد. پاسخ آموزشگاه در همین صفحه نمایش داده می‌شود.');
    }

    public function counseling(Request $request): View
    {
        $parent = $request->user();

        return view('parent.counseling.index', [
            'targets' => $this->availableTargets($parent),
            'requests' => CounselingRequest::query()
                ->where('parent_id', $parent->id)
                ->with(['academy:id,name', 'student:id,name', 'handler:id,name'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function storeCounseling(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'string', 'regex:/^\d+:\d+$/'],
            'subject' => ['required', 'string', 'max:180'],
            'details' => ['nullable', 'string', 'max:3000'],
            'preferred_at' => ['nullable', 'date', 'after:now'],
        ]);

        [$academyId, $studentId] = $this->parseTarget($data['target']);
        $parent = $request->user();
        $this->assertParentTarget($parent, $academyId, $studentId);

        CounselingRequest::query()->create([
            'academy_id' => $academyId,
            'parent_id' => $parent->id,
            'student_id' => $studentId,
            'subject' => $data['subject'],
            'details' => $data['details'] ?? null,
            'preferred_at' => $data['preferred_at'] ?? null,
            'status' => 'requested',
        ]);

        return redirect()->route('parent.counseling.index')
            ->with('success', 'درخواست مشاوره ثبت شد. زمان قطعی پس از بررسی آموزشگاه در همین صفحه نمایش داده می‌شود.');
    }

    private function availableTargets(User $parent)
    {
        return DB::table('parent_student as links')
            ->join('users as students', 'students.id', '=', 'links.student_id')
            ->join('academy_user as memberships', 'memberships.user_id', '=', 'students.id')
            ->join('academies', 'academies.id', '=', 'memberships.academy_id')
            ->where('links.parent_id', $parent->id)
            ->where('memberships.role', 'student')
            ->where('memberships.status', 'active')
            ->where('students.status', 'active')
            ->where('academies.status', 'active')
            ->select([
                'academies.id as academy_id',
                'academies.name as academy_name',
                'students.id as student_id',
                'students.name as student_name',
            ])
            ->distinct()
            ->orderBy('academies.name')
            ->orderBy('students.name')
            ->get();
    }

    private function parseTarget(string $target): array
    {
        [$academyId, $studentId] = array_map('intval', explode(':', $target, 2));

        abort_unless($academyId > 0 && $studentId > 0, 404);

        return [$academyId, $studentId];
    }

    private function assertParentTarget(User $parent, int $academyId, int $studentId): void
    {
        $exists = DB::table('parent_student as links')
            ->join('academy_user as memberships', 'memberships.user_id', '=', 'links.student_id')
            ->join('academies', 'academies.id', '=', 'memberships.academy_id')
            ->where('links.parent_id', $parent->id)
            ->where('links.student_id', $studentId)
            ->where('memberships.academy_id', $academyId)
            ->where('memberships.role', 'student')
            ->where('memberships.status', 'active')
            ->where('academies.status', 'active')
            ->exists();

        abort_unless($exists, 404);
    }
}
