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
            'status' => ['nullable', 'string', 'in:completed,pending,failed,refunded'],
            'type' => ['nullable', 'string', 'in:enrollment_payment,refund'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'sort' => ['nullable', 'string', 'in:recent,oldest,amount_desc,amount_asc,balance_desc,balance_asc,name'],
        ]);
        $data = $finance->index($owner, $filters);
        return view('owner.finance.index', $data);
    }
}
