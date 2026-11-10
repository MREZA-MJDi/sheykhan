<?php

namespace App\Services;

use App\Models\CourseEnrollment;
use App\Models\FinancialTransaction;
use App\Models\User;

final class OwnerFinanceService
{
    public function index(User $owner, array $filters = []): array
    {
        $academies = app(OwnerWorkspaceService::class)->authorizeOwnedAcademies($owner, 'finance.view');
        $academyIds = $academies->pluck('id');
        $sheet = in_array($filters['sheet'] ?? 'transactions', ['transactions', 'debts'], true)
            ? ($filters['sheet'] ?? 'transactions')
            : 'transactions';

        $completed = FinancialTransaction::query()
            ->whereIn('academy_id',$academyIds)
            ->where('status','completed');

        $income = (float) (clone $completed)->where('type','enrollment_payment')->sum('amount');
        $refunds = (float) (clone $completed)->where('type','refund')->sum('amount');

        $debtSummary = CourseEnrollment::query()
            ->whereIn('course_id', function ($courses) use ($academyIds): void {
                $courses->select('id')->from('courses')->whereIn('academy_id', $academyIds);
            })
            ->where('status', 'active')
            ->whereNotNull('price_amount')
            ->whereColumn('price_amount', '>', 'paid_amount')
            ->selectRaw('COUNT(*) AS count, COALESCE(SUM(CASE WHEN price_amount > paid_amount THEN price_amount - paid_amount ELSE 0 END), 0) AS amount')
            ->first();

        $transactions = $sheet === 'transactions'
            ? FinancialTransaction::query()
                ->whereIn('academy_id', $academyIds)
                ->with(['academy:id,name', 'user:id,name', 'enrollment.course:id,title'])
                ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
                ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('occurred_at', '>=', $date))
                ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('occurred_at', '<=', $date))
                ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                    $term = trim((string) $filters['q']);
                    $query->where(function ($nested) use ($term): void {
                        $nested->where('reference', 'like', '%' . $term . '%')
                            ->orWhere('description', 'like', '%' . $term . '%')
                            ->orWhereHas('user', fn ($user) => $user->where('name', 'like', '%' . $term . '%'))
                            ->orWhereHas('enrollment.course', fn ($course) => $course->where('title', 'like', '%' . $term . '%'));
                    });
                })
                ->when(($filters['sort'] ?? 'recent') === 'oldest', fn ($query) => $query->orderBy('occurred_at')->orderBy('id'))
                ->when(($filters['sort'] ?? 'recent') === 'amount_desc', fn ($query) => $query->orderByDesc('amount')->orderByDesc('id'))
                ->when(($filters['sort'] ?? 'recent') === 'amount_asc', fn ($query) => $query->orderBy('amount')->orderBy('id'))
                ->when(! in_array(($filters['sort'] ?? 'recent'), ['oldest', 'amount_desc', 'amount_asc'], true), fn ($query) => $query->latest('occurred_at')->latest('id'))
                ->paginate(25, ['*'], 'transactions_page')
                ->withQueryString()
            : null;

        $debts = $sheet === 'debts'
            ? CourseEnrollment::query()
                ->whereIn('course_id', function ($courses) use ($academyIds): void {
                    $courses->select('id')->from('courses')->whereIn('academy_id', $academyIds);
                })
                ->where('status', 'active')
                ->whereNotNull('price_amount')
                ->whereColumn('price_amount', '>', 'paid_amount')
                ->with(['course:id,title', 'student:id,name,mobile', 'classroom:id,title'])
                ->when(filled($filters['q'] ?? null), function ($query) use ($filters): void {
                    $term = trim((string) $filters['q']);
                    $query->where(fn ($nested) => $nested
                        ->whereHas('student', fn ($student) => $student->where('name', 'like', '%' . $term . '%')->orWhere('mobile', 'like', '%' . $term . '%'))
                        ->orWhereHas('course', fn ($course) => $course->where('title', 'like', '%' . $term . '%')));
                })
                ->when(($filters['sort'] ?? 'recent') === 'oldest', fn ($query) => $query->orderBy('created_at')->orderBy('id'))
                ->when(($filters['sort'] ?? 'recent') === 'balance_desc', fn ($query) => $query->orderByRaw('(price_amount - paid_amount) DESC')->orderByDesc('id'))
                ->when(($filters['sort'] ?? 'recent') === 'balance_asc', fn ($query) => $query->orderByRaw('(price_amount - paid_amount) ASC')->orderBy('id'))
                ->when(($filters['sort'] ?? 'recent') === 'name', fn ($query) => $query->orderBy(User::query()->select('name')->whereColumn('users.id', 'course_enrollments.student_id'))->orderByDesc('id'))
                ->when(! in_array(($filters['sort'] ?? 'recent'), ['oldest', 'balance_desc', 'balance_asc', 'name'], true), fn ($query) => $query->orderByDesc('id'))
                ->paginate(25, ['*'], 'debts_page')
                ->withQueryString()
            : null;

        return [
            'academy' => $academies->first(),
            'sheet' => $sheet,
            'filters' => $filters,
            'transactions' => $transactions,
            'debts' => $debts,
            'debtSummary' => [
                'count' => (int) ($debtSummary->count ?? 0),
                'amount' => (float) ($debtSummary->amount ?? 0),
            ],
            'stats' => [
                'income' => $income,
                'refunds' => $refunds,
                'net' => $income - $refunds,
                'completed' => (clone $completed)->count(),
            ],
        ];
    }
}
