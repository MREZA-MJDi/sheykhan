<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerCacheService;
use App\Services\OwnerDashboardService;
use App\Services\OwnerWorkspaceService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(
        OwnerDashboardService $dashboard,
        OwnerWorkspaceService $workspace,
        OwnerCacheService $cache,
    ): View {
        $owner = request()->user();

        $data = $cache->rememberDashboard(
            $owner,
            fn () => $dashboard->build($owner)
        );

        $data['academies'] = $workspace->academies($owner);

        return view('owner.dashboard', $data);
    }
}
