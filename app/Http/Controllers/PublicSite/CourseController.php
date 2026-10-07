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
    public function index(Request $request, CourseCatalogService $service): View
    {
        $gradeId = $request->integer('grade');

        return view('pages.courses.index', [
            'courses' => $service->paginate(12, $gradeId > 0 ? $gradeId : null),
            'selectedGradeId' => $gradeId > 0 ? $gradeId : null,
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

        if ($canAccessContent) {
            $course->load([
                'sections.lessons.media' => fn ($query) => $query
                    ->where('status', 'active')
                    ->where('visibility', 'private')
                    ->orderByPivot('sort_order'),
            ]);
        }

        return view('pages.courses.show', [
            'course' => $course,
            'canAccessContent' => $canAccessContent,
            'requiresPayment' => $course->requiresPayment(),
        ]);
    }
}
