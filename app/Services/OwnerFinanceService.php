<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\User;

final class OwnerFinanceService
{
    public function index(User $owner): array
    {
        app(OwnerWorkspaceService::class)->authorizeOwnedAcademies($owner, 'finance.view');

        $academyIds = app(OwnerWorkspaceService::class)
            ->authorizeOwnedAcademies($owner, 'finance.view')
            ->pluck('id');

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

        $transactions = FinancialTransaction::query()
            ->whereIn('academy_id',$academyIds)
            ->with([
                'academy:id,name',
                'user:id,name',
                'enrollment.course:id,title',
            ])
            ->latest('occurred_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return [
            'transactions' => $transactions,
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
