<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
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

        $hasCourseEntitlement = $request->user()
            ? $access->canAccess($request->user(), $course)
            : false;
        $parentCourseEntitlement = (bool) ($request->user()?->hasRole('parent') && $hasCourseEntitlement);
        // A parent can monitor the child's paid enrollment, but private lesson
        // media should only be hydrated for the learner's own account.
        $canAccessContent = $hasCourseEntitlement && ! $parentCourseEntitlement;

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
            'parentCourseEntitlement' => $parentCourseEntitlement,
            'requiresPayment' => $course->requiresPayment(),
            'seoMeta' => $course->seoMeta,
        ]);
    }

    public function preview(
        Course $course,
        Lesson $lesson,
        CourseCatalogService $service,
    ): View {
        $course = $service->findPublished($course);

        $lesson->loadMissing('section.course');

        abort_unless(
            $lesson->section?->course
            && (int) $lesson->section->course->id === (int) $course->id
            && $lesson->status === 'published'
            && (!$lesson->published_at || $lesson->published_at->isPast())
            && $lesson->is_free,
            404
        );

        // Public preview media must be explicitly public. Protected lesson
        // files are never hydrated into an unauthenticated response.
        $lesson->load([
            'media' => fn ($query) => $query
                ->where('status', 'active')
                ->where('visibility', 'public')
                ->orderByPivot('sort_order'),
        ]);

        return view('pages.courses.preview', [
            'course' => $course,
            'lesson' => $lesson,
        ]);
    }
}
