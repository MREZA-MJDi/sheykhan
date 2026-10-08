<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerFinanceService;
use Illuminate\View\View;

final class FinanceController extends Controller
{
    public function index(OwnerFinanceService $finance): View
    {
        return view('owner.finance.index', $finance->index(request()->user()));
    }
}
