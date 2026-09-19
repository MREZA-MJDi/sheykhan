<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Lesson\StoreLessonRequest;
use App\Http\Requests\Teacher\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Course $course): View
    {
        $course->load([
            'sections.lessons.media',
        ]);

        abort_unless(
            $course->teachers()->whereKey(request()->user()->id)->exists(),
            403
        );

        return view('teacher.courses.content', compact('course'));
    }

    public function store(StoreLessonRequest $request): RedirectResponse
    {
        $section = request()->user()
            ->taughtCourses()
            ->whereHas('sections', fn ($query) => $query->whereKey($request->validated('course_section_id')))
            ->firstOrFail()
            ->sections()
            ->whereKey($request->validated('course_section_id'))
            ->firstOrFail();

        $data = $request->validated();
        $data['published_at'] = ($data['status'] ?? 'draft') === 'published'
            ? ($data['published_at'] ?? now())
            : null;

        $section->lessons()->create($data);

        return back()->with('success', 'درس با موفقیت اضافه شد.');
    }

    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson
    ): RedirectResponse {
        $course = $lesson->section->course;

        abort_unless(
            $course->teachers()->whereKey($request->user()->id)->exists(),
            403
        );

        $data = $request->validated();
        $data['published_at'] = ($data['status'] ?? $lesson->status) === 'published'
            ? ($data['published_at'] ?? $lesson->published_at ?? now())
            : null;

        $lesson->update($data);

        return back()->with('success', 'درس به‌روزرسانی شد.');
    }
}
