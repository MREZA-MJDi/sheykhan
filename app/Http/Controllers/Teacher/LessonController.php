<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Lesson\StoreLessonRequest;
use App\Http\Requests\Teacher\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Services\TeacherCourseContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(
        Course $course,
        TeacherCourseContentService $content
    ): View {
        $sections = $content->sectionsFor(request()->user(), $course);

        return view('teacher.courses.content', compact('course', 'sections'));
    }

    public function store(
        StoreLessonRequest $request,
        TeacherCourseContentService $content
    ): RedirectResponse {
        $section = CourseSection::query()->findOrFail(
            (int) $request->validated('course_section_id')
        );

        $content->createLesson(
            $request->user(),
            $section,
            $request->validated()
        );

        return back()->with('success', 'درس با موفقیت اضافه شد.');
    }

    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson,
        TeacherCourseContentService $content
    ): RedirectResponse {
        $content->updateLesson(
            $request->user(),
            $lesson,
            $request->validated()
        );

        return back()->with('success', 'درس به‌روزرسانی شد.');
    }

    public function destroy(
        Lesson $lesson,
        TeacherCourseContentService $content
    ): RedirectResponse {
        $content->deleteLesson(request()->user(), $lesson);

        return back()->with('success', 'درس حذف شد.');
    }
}
