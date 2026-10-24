<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\AcademyMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AcademyMessageController extends Controller
{
    public function index(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy);
        $targets = [['value' => 'all_parents', 'label' => 'اطلاعیه عمومی برای همه خانواده‌های آموزشگاه']];

        $parentRows = DB::table('parent_student as links')
            ->join('users as students', 'students.id', '=', 'links.student_id')
            ->join('academy_user as sm', function ($join): void {
                $join->on('sm.user_id', '=', 'students.id')->where('sm.role', 'student')->where('sm.status', 'active');
            })
            ->join('users as parents', 'parents.id', '=', 'links.parent_id')
            ->join('academy_user as pm', function ($join): void {
                $join->on('pm.user_id', '=', 'parents.id')->on('pm.academy_id', '=', 'sm.academy_id')
                    ->where('pm.role', 'parent')->where('pm.status', 'active');
            })
            ->where('sm.academy_id', $academy->id)
            ->where('students.status', 'active')->whereNull('students.deleted_at')
            ->where('parents.status', 'active')->whereNull('parents.deleted_at')
            ->orderBy('parents.name')->orderBy('students.name')->distinct()
            ->get(['students.id as student_id', 'students.name as student_name', 'parents.id as parent_id', 'parents.name as parent_name']);

        foreach ($parentRows as $row) {
            $targets[] = [
                'value' => 'parent:' . $row->student_id . ':' . $row->parent_id,
                'label' => 'پیام خصوصی به ولی ' . $row->parent_name . ' · فرزند: ' . $row->student_name,
            ];
        }

        $studentRows = DB::table('academy_user as membership')
            ->join('users', 'users.id', '=', 'membership.user_id')
            ->where('membership.academy_id', $academy->id)
            ->where('membership.role', 'student')->where('membership.status', 'active')
            ->where('users.status', 'active')->whereNull('users.deleted_at')
            ->orderBy('users.name')->get(['users.id', 'users.name']);

        foreach ($studentRows as $row) {
            $targets[] = ['value' => 'student:' . $row->id, 'label' => 'پیام مستقیم به دانش‌آموز ' . $row->name];
        }

        $messages = AcademyMessage::query()->where('academy_id', $academy->id)
            ->with(['sender:id,name', 'recipient:id,name', 'student:id,name', 'classroom:id,title'])
            ->latest()->paginate(12)->withQueryString();

        return view('owner.communications.index', [
            'academy' => $academy,
            'pageTitle' => 'اطلاعیه و پیام به خانواده‌ها',
            'pageDescription' => 'برای همه خانواده‌های فعال اطلاعیه بفرست یا فقط به ولی یا دانش‌آموز مرتبط پیام بده.',
            'targetOptions' => $targets,
            'storeRoute' => route('owner.communications.store', $academy),
            'backRoute' => route('owner.dashboard'),
            'canManage' => $request->user()->hasPermission('parents.manage'),
            'messages' => $messages,
        ]);
    }

    public function store(Academy $academy, Request $request): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy);
        abort_unless($request->user()->hasPermission('parents.manage'), 403);

        $data = $request->validate([
            'target' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'in:announcement,academic,schedule,important,other'],
            'subject' => ['required', 'string', 'min:3', 'max:180'],
            'body' => ['required', 'string', 'min:5', 'max:10000'],
        ], [
            'target.required' => 'مخاطب پیام را انتخاب کنید.',
            'subject.required' => 'عنوان پیام را وارد کنید.',
            'body.required' => 'متن پیام را وارد کنید.',
        ]);

        $audience = null;
        $recipient = null;
        $student = null;

        if ($data['target'] === 'all_parents') {
            $audience = 'all_parents';
        } elseif (preg_match('/^student:(\d+)$/', $data['target'], $parts) === 1) {
            $student = $this->activeStudent($academy, (int) $parts[1]);
            $recipient = $student;
            $audience = 'student';
        } elseif (preg_match('/^parent:(\d+):(\d+)$/', $data['target'], $parts) === 1) {
            $student = $this->activeStudent($academy, (int) $parts[1]);
            $recipient = $this->activeParentForStudent($academy, $student, (int) $parts[2]);
            $audience = 'parent';
        } else {
            abort(404);
        }

        AcademyMessage::query()->create([
            'academy_id' => $academy->id,
            'sender_id' => $request->user()->id,
            'sender_role' => 'academy-owner',
            'audience' => $audience,
            'recipient_id' => $recipient?->id,
            'student_id' => $student?->id,
            'category' => $data['category'],
            'subject' => trim($data['subject']),
            'body' => trim($data['body']),
            'published_at' => now(),
        ]);

        return redirect()->route('owner.communications.index', $academy)
            ->with('success', 'پیام برای مخاطب‌های مجاز منتشر شد.');
    }

    private function activeStudent(Academy $academy, int $studentId): User
    {
        $student = User::query()->whereKey($studentId)->where('status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->firstOrFail();

        $hasActiveMembership = DB::table('academy_user as membership')
            ->join('academies', 'academies.id', '=', 'membership.academy_id')
            ->where('membership.user_id', $student->id)
            ->where('membership.academy_id', $academy->id)
            ->where('membership.role', 'student')
            ->where('membership.status', 'active')
            ->where('academies.status', 'active')
            ->exists();

        abort_unless($hasActiveMembership, 404);

        return $student;
    }

    private function activeParentForStudent(Academy $academy, User $student, int $parentId): User
    {
        $parent = $student->parents()->whereKey($parentId)->where('users.status', 'active')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'parent'))
            ->firstOrFail();

        $hasActiveMembership = DB::table('academy_user as membership')
            ->join('academies', 'academies.id', '=', 'membership.academy_id')
            ->where('membership.user_id', $parent->id)
            ->where('membership.academy_id', $academy->id)
            ->where('membership.role', 'parent')
            ->where('membership.status', 'active')
            ->where('academies.status', 'active')
            ->exists();

        abort_unless($hasActiveMembership, 404);

        return $parent;
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
