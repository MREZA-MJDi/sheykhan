<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseCatalogService;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(CourseCatalogService $service): View
    {
        return view('pages.courses.index', [
            'courses' => $service->paginate(),
        ]);
    }

    public function show(Course $course, CourseCatalogService $service): View
    {
        $course = $service->findPublished($course);

        return view('pages.courses.show', compact('course'));
    }
}
