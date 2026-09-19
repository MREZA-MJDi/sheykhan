<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerDashboardService;
use App\Services\OwnerWorkspaceService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        OwnerDashboardService $dashboard,
        OwnerWorkspaceService $workspace
    ): View {
        $data = $dashboard->build(request()->user());
        $data['academies'] = $workspace->academies(request()->user());

        return view('owner.dashboard', $data);
    }
}
