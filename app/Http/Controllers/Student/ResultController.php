<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentDashboardService;
use App\Services\StudentResultService;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function growth(StudentDashboardService $dashboard): View
    {
        return view('student.growth', $dashboard->growth(request()->user()));
    }

    public function index(StudentResultService $results): View
    {
        return view('student.results.index', [
            'results' => $results->paginate(request()->user()),
        ]);
    }
}
