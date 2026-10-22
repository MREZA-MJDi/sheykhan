<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\TeacherPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class TeacherPaymentController extends Controller
{
    public function index(Academy $academy, Request $request): View
    {
        $this->authorizeAcademy($request, $academy, 'teacher_payments.view');

        $teachers = $academy->users()
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.email']);

        $payments = TeacherPayment::query()
            ->where('academy_id', $academy->id)
            ->with(['teacher:id,name,email', 'recorder:id,name'])
            ->when($request->integer('teacher_id'), fn ($query, $teacherId) => $query->where('teacher_id', $teacherId))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'paid' => TeacherPayment::query()->where('academy_id', $academy->id)->where('status', 'paid')->sum('amount'),
            'pending' => TeacherPayment::query()->where('academy_id', $academy->id)->where('status', 'pending')->sum('amount'),
        ];

        return view('owner.teacher-payments.index', compact('academy', 'teachers', 'payments', 'summary'));
    }

    public function store(Request $request, Academy $academy): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'teacher_payments.manage');

        $data = $request->validate([
            'teacher_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000000'],
            'status' => ['required', 'string', 'in:pending,paid'],
            'reference' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $isActiveTeacher = $academy->users()
            ->whereKey((int) $data['teacher_id'])
            ->wherePivot('role', 'teacher')
            ->wherePivot('status', 'active')
            ->where('users.status', 'active')
            ->exists();

        abort_unless($isActiveTeacher, 404);

        TeacherPayment::query()->create([
            'academy_id' => $academy->id,
            'teacher_id' => (int) $data['teacher_id'],
            'recorded_by' => $request->user()->id,
            'title' => trim($data['title']),
            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'amount' => (int) $data['amount'],
            'currency' => 'IRT',
            'status' => $data['status'],
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        return redirect()->route('owner.teacher-payments.index', $academy)
            ->with('success', 'سند پرداخت مدرس ثبت شد. مبلغ بر اساس ورودی ثبت شده و به‌صورت خودکار از درآمد دوره محاسبه نمی‌شود.');
    }

    public function updateStatus(Request $request, Academy $academy, int $payment): RedirectResponse
    {
        $this->authorizeAcademy($request, $academy, 'teacher_payments.manage');
        $data = $request->validate(['status' => ['required', 'string', 'in:pending,paid']]);

        $record = TeacherPayment::query()
            ->where('academy_id', $academy->id)
            ->findOrFail($payment);

        $record->forceFill([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? ($record->paid_at ?? now()) : null,
        ])->save();

        return redirect()->route('owner.teacher-payments.index', $academy)
            ->with('success', 'وضعیت سند پرداخت ذخیره شد.');
    }

    private function authorizeAcademy(Request $request, Academy $academy, string $permission): void
    {
        abort_unless(
            $request->user()->hasRole('academy-owner')
            && $request->user()->hasPermission($permission)
            && (int) $academy->owner_id === (int) $request->user()->id,
            404
        );
    }
}
