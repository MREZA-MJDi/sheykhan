<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseAccessService;
use App\Services\CourseCatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(CourseCatalogService $service): View
    {
        return view('pages.courses.index', [
            'courses' => $service->paginate(),
        ]);
    }

    public function show(
        Request $request,
        Course $course,
        CourseCatalogService $service,
        CourseAccessService $access,
    ): View {
        $course = $service->findPublished($course);

        $canAccessContent = $request->user()
            ? $access->canAccess($request->user(), $course)
            : false;

        return view('pages.courses.show', [
            'course' => $course,
            'canAccessContent' => $canAccessContent,
            'requiresPayment' => $course->requiresPayment(),
        ]);
    }
}
