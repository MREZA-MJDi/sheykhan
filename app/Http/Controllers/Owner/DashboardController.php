<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(OwnerDashboardService $dashboard): View
    {
        return view('owner.dashboard', $dashboard->build(request()->user()));
    }
}
