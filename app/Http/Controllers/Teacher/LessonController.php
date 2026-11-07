<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Lesson\StoreCourseSectionRequest;
use App\Http\Requests\Teacher\Lesson\StoreLessonRequest;
use App\Http\Requests\Teacher\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\LearningResource;
use App\Services\MediaService;
use Illuminate\Support\Facades\DB;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function storeSection(
        StoreCourseSectionRequest $request,
        Course $course
    ): RedirectResponse {
        abort_unless(
            $course->teachers()->whereKey($request->user()->id)->exists()
                && $course->academy?->status === 'active',
            403
        );

        $nextOrder = ((int) $course->sections()->max('sort_order')) + 1;

        $course->sections()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'sort_order' => $nextOrder,
        ]);

        return back()->with('success', 'سرفصل با موفقیت ساخته شد.');
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

    public function destroy(Request $request, Lesson $lesson, MediaService $media): RedirectResponse
    {
        $course = $lesson->section()->with('course')->firstOrFail()->course;

        abort_unless(
            $course->teachers()->whereKey($request->user()->id)->exists()
                && $course->academy?->status === 'active',
            403
        );

        $files = collect();

        DB::transaction(function () use ($request, $lesson, $media, &$files): void {
            $locked = Lesson::query()->whereKey($lesson->id)->lockForUpdate()->firstOrFail();
            $course = $locked->section()->with('course')->firstOrFail()->course;

            abort_unless(
                $course->teachers()->whereKey($request->user()->id)->exists()
                    && $course->academy?->status === 'active',
                403
            );

            if ($locked->status !== 'draft') {
                throw \\Illuminate\\Validation\\ValidationException::withMessages([
                    'lesson' => 'برای حذف درس منتشرشده، ابتدا آن را از انتشار خارج کن.',
                ]);
            }

            if ($locked->progress()->exists() || LearningResource::query()->where('lesson_id', $locked->id)->exists()) {
                throw \\Illuminate\\Validation\\ValidationException::withMessages([
                    'lesson' => 'برای این درس سابقهٔ پیشرفت یا منبع آموزشی ثبت شده است؛ حذف مجاز نیست تا سوابق حفظ شوند.',
                ]);
            }

            $files = $locked->media()->get();
            foreach ($files as $file) {
                $media->detach($file, $locked);
            }

            $locked->delete();
        }, 3);

        foreach ($files->unique('id') as $file) {
            if ($file->attachments()->doesntExist()) {
                $media->delete($file);
            }
        }

        return back()->with('success', 'درس پیش‌نویس و فایل‌های بدون استفادهٔ آن حذف شدند.');
    }
}
