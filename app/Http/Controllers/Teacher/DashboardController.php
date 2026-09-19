<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\TeacherDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(TeacherDashboardService $dashboard): View
    {
        return view('teacher.dashboard', $dashboard->build(request()->user()));
    }
}
