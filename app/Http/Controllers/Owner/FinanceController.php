<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerFinanceService;
use Illuminate\View\View;

final class FinanceController extends Controller
{
    public function index(OwnerFinanceService $finance): View
    {
        $owner = request()->user();
        $data = $finance->index($owner);
        $data['academy'] = $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->first();

        return view('owner.finance.index', $data);
    }
}
