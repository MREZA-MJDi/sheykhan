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
            'stats' => [
                'income' => $income,
                'refunds' => $refunds,
                'net' => $income - $refunds,
                'completed' => (clone $completed)->count(),
            ],
        ];
    }
}
