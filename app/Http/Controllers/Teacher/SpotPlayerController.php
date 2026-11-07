<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\SpotPlayerDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class SpotPlayerController extends Controller
{
    public function index(Request $request, SpotPlayerDashboardService $dashboard): View
    {
        abort_unless($request->user()->hasRole('teacher'), 403);

        return view('spotplayer.index', [
            ...$dashboard->forUser($request->user(), false),
            'isOwner' => false,
            'isConfigured' => app(\App\Services\SpotPlayerService::class)->isConfigured(),
            'mode' => app(\App\Services\SpotPlayerService::class)->mode(),
        ]);
    }
}
