<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Services\ParentDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, ParentDashboardService $dashboard): View
    {
        $selectedChildId = $request->integer('child') ?: null;

        return view(
            'parent.dashboard',
            $dashboard->build($request->user(), $selectedChildId),
        );
    }
}
