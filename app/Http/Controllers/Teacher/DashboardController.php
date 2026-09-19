<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\CourseManagementService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(CourseManagementService $courses): View
    {
        $items = $courses->coursesFor(request()->user());

        return view('teacher.dashboard', [
            'courses' => $items,
            'courseCount' => $items->count(),
            'publishedCount' => $items->where('status', 'published')->count(),
            'draftCount' => $items->where('status', 'draft')->count(),
            'paidCount' => $items->where('access_type', 'paid')->count(),
        ]);
    }
}
