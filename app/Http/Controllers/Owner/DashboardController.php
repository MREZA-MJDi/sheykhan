<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\CourseManagementService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(CourseManagementService $courses): View
    {
        $items = $courses->coursesFor(request()->user());

        return view('owner.dashboard', [
            'courses' => $items,
            'courseCount' => $items->count(),
            'publishedCount' => $items->where('status', 'published')->count(),
            'freeCount' => $items->where('access_type', 'free')->count(),
            'paidCount' => $items->where('access_type', 'paid')->count(),
        ]);
    }
}
