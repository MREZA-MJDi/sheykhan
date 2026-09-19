<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\CourseSection\StoreCourseSectionRequest;
use App\Http\Requests\Teacher\CourseSection\UpdateCourseSectionRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Services\TeacherCourseContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CourseSectionController extends Controller
{
    public function store(
        StoreCourseSectionRequest $request,
        Course $course,
        TeacherCourseContentService $content
    ): RedirectResponse {
        $content->createSection(
            $request->user(),
            $course,
            $request->validated()
        );

        return back()->with('success', 'سرفصل با موفقیت ساخته شد.');
    }

    public function update(
        UpdateCourseSectionRequest $request,
        CourseSection $section,
        TeacherCourseContentService $content
    ): RedirectResponse {
        $content->updateSection(
            $request->user(),
            $section,
            $request->validated()
        );

        return back()->with('success', 'سرفصل به‌روزرسانی شد.');
    }

    public function destroy(
        CourseSection $section,
        TeacherCourseContentService $content
    ): RedirectResponse {
        try {
            $content->deleteSection(request()->user(), $section);
        } catch (\LogicException $exception) {
            return back()->withErrors(['section' => $exception->getMessage()]);
        }

        return back()->with('success', 'سرفصل حذف شد.');
    }

    public function reorder(
        Course $course,
        TeacherCourseContentService $content
    ): JsonResponse {
        $data = request()->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        try {
            $content->reorderSections(request()->user(), $course, $data['ids']);
        } catch (\InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'ترتیب ذخیره شد.']);
    }
}
