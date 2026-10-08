<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Lesson\StoreLessonRequest;
use App\Http\Requests\Teacher\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\LearningResource;
use Illuminate\Support\Facades\DB;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Course $course): View
    {
        abort_unless(
            $course->teachers()->whereKey(request()->user()->id)->exists()
                && $course->academy?->status === 'active',
            403
        );

        $course->load([
            'sections.lessons.media',
        ]);

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

        DB::transaction(function () use ($lesson, $data): void {
            $lesson->update($data);

            if ($lesson->status === 'published') {
                LearningResource::query()
                    ->where('lesson_id', $lesson->id)
                    ->whereNull('release_at')
                    ->update(['release_at' => now()]);

                LearningResource::query()
                    ->where('lesson_id', $lesson->id)
                    ->update(['status' => 'active']);
            } else {
                LearningResource::query()
                    ->where('lesson_id', $lesson->id)
                    ->update(['status' => 'draft']);
            }
        });

        return back()->with('success', 'درس به‌روزرسانی شد.');
    }
}
