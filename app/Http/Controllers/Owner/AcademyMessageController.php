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
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['announcement', 'academic', 'schedule', 'important', 'other'])],
            'audience' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['all_parents', 'classroom_parents', 'teacher_class_parents', 'parent', 'student'])],
        ]);

        $messages = AcademyMessage::query()->where('academy_id', $academy->id)
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['audience'] ?? null, fn ($query, $audience) => $query->where('audience', $audience))
            ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                $term = trim((string) $filters['q']);
                $query->where(function ($nested) use ($term): void {
                    $nested->where('subject', 'like', '%' . $term . '%')
                        ->orWhere('body', 'like', '%' . $term . '%')
                        ->orWhereHas('sender', fn ($sender) => $sender->where('name', 'like', '%' . $term . '%'))
                        ->orWhereHas('recipient', fn ($recipient) => $recipient->where('name', 'like', '%' . $term . '%'))
                        ->orWhereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%'))
                        ->orWhereHas('classroom', fn ($classroom) => $classroom->where('title', 'like', '%' . $term . '%'));
                });
            })
            ->with(['sender:id,name', 'recipient:id,name', 'student:id,name', 'classroom:id,title'])
            ->latest()->paginate(12)->withQueryString();

        return view('owner.communications.index', [
            'academy' => $academy,
            'pageTitle' => 'اطلاعیه و پیام به خانواده‌ها',
            'pageDescription' => 'برای همه خانواده‌های فعال اطلاعیه بفرست یا فقط به ولی یا دانش‌آموز مرتبط پیام بده.',
            'storeRoute' => route('owner.communications.store', $academy),
            'backRoute' => route('owner.dashboard'),
            'canManage' => $request->user()->hasPermission('parents.manage'),
            'messages' => $messages,
            'filters' => $filters,
        ]);
    }

    public function recipients(Academy $academy, Request $request)
    {
        $this->authorizeAcademy($request, $academy);
        abort_unless($request->user()->hasPermission('parents.manage'), 403);

        $filters = $request->validate([
            'type' => ['required', 'string', 'in:students,parents'],
            'q' => ['required', 'string', 'min:2', 'max:80'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ]);

        $like = trim($filters['q']) . '%';

        if ($filters['type'] === 'students') {
            $query = DB::table('academy_user as membership')
                ->join('users', 'users.id', '=', 'membership.user_id')
                ->where('membership.academy_id', $academy->id)
                ->where('membership.role', 'student')
                ->where('membership.status', 'active')
                ->where('users.status', 'active')
                ->whereNull('users.deleted_at')
                ->where('users.name', 'like', $like)
                ->orderBy('users.name')
                ->orderBy('users.id')
                ->select(['users.id', 'users.name']);
        } else {
            $query = DB::table('parent_student as links')
                ->join('users as students', 'students.id', '=', 'links.student_id')
                ->join('academy_user as sm', function ($join): void {
                    $join->on('sm.user_id', '=', 'students.id')
                        ->where('sm.role', 'student')
                        ->where('sm.status', 'active');
                })
                ->join('users as parents', 'parents.id', '=', 'links.parent_id')
                ->join('academy_user as pm', function ($join): void {
                    $join->on('pm.user_id', '=', 'parents.id')
                        ->on('pm.academy_id', '=', 'sm.academy_id')
                        ->where('pm.role', 'parent')
                        ->where('pm.status', 'active');
                })
                ->where('sm.academy_id', $academy->id)
                ->where('students.status', 'active')
                ->whereNull('students.deleted_at')
                ->where('parents.status', 'active')
                ->whereNull('parents.deleted_at')
                ->where(function ($nested) use ($like): void {
                    $nested->where('parents.name', 'like', $like)
                        ->orWhere('students.name', 'like', $like);
                })
                ->orderBy('parents.name')
                ->orderBy('students.name')
                ->select([
                    'students.id as student_id',
                    'students.name as student_name',
                    'parents.id as parent_id',
                    'parents.name as parent_name',
                ])
                ->distinct();
        }

        $paginator = $query->simplePaginate(20);
        $data = $paginator->getCollection()->map(function ($row) use ($filters): array {
            if ($filters['type'] === 'students') {
                return [
                    'value' => 'student:' . $row->id,
                    'label' => 'دانش‌آموز: ' . $row->name,
                ];
            }

            return [
                'value' => 'parent:' . $row->student_id . ':' . $row->parent_id,
                'label' => 'ولی: ' . $row->parent_name . ' · فرزند: ' . $row->student_name,
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'has_more' => $paginator->hasMorePages(),
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
