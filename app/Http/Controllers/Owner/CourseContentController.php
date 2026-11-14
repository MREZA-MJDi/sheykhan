<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Services\CourseManagementService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class CourseContentController extends Controller
{
    public function storeSection(Request $request, Course $course, CourseManagementService $courses): RedirectResponse
    {
        abort_unless(
            $request->user()->hasPermission('lessons.manage') && $courses->canManage($request->user(), $course),
            403
        );

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'title.required' => 'عنوان فصل الزامی است.',
            'title.max' => 'عنوان فصل نباید بیشتر از ۲۵۵ نویسه باشد.',
        ]);

        $course->sections()->create([
            ...$data,
            'sort_order' => ((int) $course->sections()->max('sort_order')) + 1,
        ]);

        return back()->with('success', 'فصل جدید به دوره اضافه شد.');
    }

    public function updateSection(Request $request, Course $course, int $section, CourseManagementService $courses): RedirectResponse
    {
        abort_unless(
            $request->user()->hasPermission('lessons.manage') && $courses->canManage($request->user(), $course),
            403
        );

        $item = CourseSection::query()
            ->where('course_id', $course->id)
            ->whereKey($section)
            ->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:9999'],
        ]);

        $item->update($data);

        return back()->with('success', 'اطلاعات فصل ذخیره شد.');
    }

    public function destroySection(Request $request, Course $course, int $section, CourseManagementService $courses): RedirectResponse
    {
        abort_unless(
            $request->user()->hasPermission('lessons.manage') && $courses->canManage($request->user(), $course),
            403
        );

        $item = CourseSection::query()
            ->where('course_id', $course->id)
            ->whereKey($section)
            ->firstOrFail();

        if ($item->lessons()->exists()) {
            throw ValidationException::withMessages([
                'section' => 'این فصل هنوز درس دارد. برای حفظ سابقهٔ دانش‌آموزان، ابتدا درس‌های قابل‌حذف را مدیریت کن.',
            ]);
        }

        $item->delete();

        return back()->with('success', 'فصل خالی حذف شد.');
    }

    public function storeLesson(Request $request, Course $course, int $section, CourseManagementService $courses): RedirectResponse
    {
        abort_unless(
            $request->user()->hasPermission('lessons.manage') && $courses->canManage($request->user(), $course),
            403
        );

        $sectionItem = CourseSection::query()
            ->where('course_id', $course->id)
            ->whereKey($section)
            ->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('lessons', 'slug')],
            'type' => ['required', Rule::in(['video', 'text', 'quiz', 'live', 'file'])],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:30000'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'is_free' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort_order' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ], [
            'title.required' => 'عنوان درس الزامی است.',
            'slug.required' => 'شناسهٔ درس الزامی است.',
            'slug.alpha_dash' => 'شناسهٔ درس فقط می‌تواند شامل حروف لاتین، عدد، خط تیره و زیرخط باشد.',
            'slug.unique' => 'این شناسهٔ درس قبلاً استفاده شده است.',
        ]);

        $data['duration_seconds'] = (int) ($data['duration_seconds'] ?? 0);
        $data['is_free'] = (bool) ($data['is_free'] ?? false);
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        $data['sort_order'] = (int) ($data['sort_order'] ?? (((int) $sectionItem->lessons()->max('sort_order')) + 1));

        $sectionItem->lessons()->create($data);

        return back()->with('success', 'درس به فصل اضافه شد.');
    }

    public function updateLesson(Request $request, Course $course, int $section, int $lesson, CourseManagementService $courses): RedirectResponse
    {
        abort_unless(
            $request->user()->hasPermission('lessons.manage') && $courses->canManage($request->user(), $course),
            403
        );

        $sectionItem = CourseSection::query()
            ->where('course_id', $course->id)
            ->whereKey($section)
            ->firstOrFail();

        $item = Lesson::query()
            ->where('course_section_id', $sectionItem->id)
            ->whereKey($lesson)
            ->firstOrFail();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('lessons', 'slug')->ignore($item->id)],
            'type' => ['required', Rule::in(['video', 'text', 'quiz', 'live', 'file'])],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:30000'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'is_free' => ['sometimes', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort_order' => ['required', 'integer', 'min:1', 'max:9999'],
        ], [
            'title.required' => 'عنوان درس الزامی است.',
            'slug.required' => 'شناسهٔ درس الزامی است.',
            'slug.unique' => 'این شناسهٔ درس قبلاً استفاده شده است.',
        ]);

        $data['duration_seconds'] = (int) ($data['duration_seconds'] ?? 0);
        $data['is_free'] = (bool) ($data['is_free'] ?? false);
        $data['published_at'] = $data['status'] === 'published'
            ? ($item->published_at ?: now())
            : null;

        DB::transaction(function () use ($item, $data): void {
            $item->update($data);

            if ($item->status === 'published') {
                LearningResource::query()
                    ->where('lesson_id', $item->id)
                    ->whereNull('release_at')
                    ->update(['release_at' => now()]);

                LearningResource::query()
                    ->where('lesson_id', $item->id)
                    ->update(['status' => 'active']);
            } else {
                LearningResource::query()
                    ->where('lesson_id', $item->id)
                    ->update(['status' => 'draft']);
            }
        });

        return back()->with('success', 'تغییرات درس ذخیره شد.');
    }

    public function destroyLesson(
        Request $request,
        Course $course,
        int $section,
        int $lesson,
        CourseManagementService $courses,
        MediaService $media,
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasPermission('lessons.manage') && $courses->canManage($request->user(), $course),
            403
        );

        $sectionItem = CourseSection::query()
            ->where('course_id', $course->id)
            ->whereKey($section)
            ->firstOrFail();

        $files = collect();

        DB::transaction(function () use ($request, $sectionItem, $lesson, $media, &$files): void {
            $item = Lesson::query()
                ->where('course_section_id', $sectionItem->id)
                ->whereKey($lesson)
                ->lockForUpdate()
                ->firstOrFail();

            if ($item->status !== 'draft') {
                throw ValidationException::withMessages([
                    'lesson' => 'برای حذف درس منتشرشده یا آرشیوشده، ابتدا آن را به پیش‌نویس برگردان.',
                ]);
            }

            if ($item->progress()->exists() || LearningResource::query()->where('lesson_id', $item->id)->exists()) {
                throw ValidationException::withMessages([
                    'lesson' => 'برای این درس سابقهٔ پیشرفت یا منبع آموزشی وجود دارد؛ حذف مجاز نیست تا اطلاعات دانش‌آموزان حفظ شود.',
                ]);
            }

            $files = $item->media()->get();
            foreach ($files as $file) {
                $media->detach($file, $item);
            }

            $item->delete();
        }, 3);

        foreach ($files->unique('id') as $file) {
            if ($file->attachments()->doesntExist()) {
                $media->delete($file);
            }
        }

        return back()->with('success', 'درس پیش‌نویس و فایل‌های بدون استفادهٔ آن حذف شدند.');
    }
}
