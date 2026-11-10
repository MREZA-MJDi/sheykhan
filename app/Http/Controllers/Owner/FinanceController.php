<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerFinanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class FinanceController extends Controller
{
    public function index(Request $request, OwnerFinanceService $finance): View
    {
        $owner = $request->user();
        $filters = $request->validate([
            'sheet' => ['nullable', 'string', 'in:transactions,debts'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $data = $finance->index($owner, $filters);
        $data['academy'] = $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->first();

        return view('owner.finance.index', $data);
    }
}
