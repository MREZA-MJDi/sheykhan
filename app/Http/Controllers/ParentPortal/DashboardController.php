<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Services\ParentDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(ParentDashboardService $dashboard): View
    {
        return view('parent.dashboard', $dashboard->build(request()->user()));
    }
}
