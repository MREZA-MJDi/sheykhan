<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentDashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(StudentDashboardService $dashboard): View
    {
        return view('student.dashboard', $dashboard->build(request()->user()));
    }
}
