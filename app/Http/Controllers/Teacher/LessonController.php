<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\Lesson\StoreCourseSectionRequest;
use App\Http\Requests\Teacher\Lesson\StoreLessonRequest;
use App\Http\Requests\Teacher\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Services\LessonReviewNotificationService;
use App\Services\MediaService;
use App\Services\TeacherAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Course $course, TeacherAccessService $teachers): View
    {
        abort_unless($teachers->canTeachCourse(request()->user(), $course), 403);

        $course->load(['sections.lessons.media']);

        return view('teacher.courses.content', compact('course'));
    }

    public function storeSection(
        StoreCourseSectionRequest $request,
        Course $course,
        TeacherAccessService $teachers,
    ): RedirectResponse {
        abort_unless($teachers->canTeachCourse($request->user(), $course), 403);

        $nextOrder = ((int) $course->sections()->max('sort_order')) + 1;

        $course->sections()->create([
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'sort_order' => $nextOrder,
        ]);

        return back()->with('success', 'سرفصل با موفقیت ساخته شد.');
    }

    public function store(
        StoreLessonRequest $request,
        TeacherAccessService $teachers,
        LessonReviewNotificationService $notifications,
    ): RedirectResponse {
        $section = $request->user()
            ->taughtCourses()
            ->whereHas('sections', fn ($query) => $query->whereKey($request->validated('course_section_id')))
            ->firstOrFail()
            ->sections()
            ->whereKey($request->validated('course_section_id'))
            ->firstOrFail();

        $course = $section->course()->with('academy.owner')->firstOrFail();
        abort_unless($teachers->canTeachCourse($request->user(), $course), 403);

        $data = $request->validated();
        $data['status'] = 'draft';
        $data['published_at'] = null;

        $lesson = $section->lessons()->create($data);
        $notifications->notifyOwner($request->user(), $course, $lesson);

        return back()->with('success', 'درس ذخیره شد و برای بازبینی و تأیید مالک آموزشگاه ارسال شد.');
    }

    public function update(
        UpdateLessonRequest $request,
        Lesson $lesson,
        TeacherAccessService $teachers,
        LessonReviewNotificationService $notifications,
    ): RedirectResponse {
        $lesson->loadMissing('section.course.academy.owner');
        $course = $lesson->section?->course;

        abort_unless($course && $teachers->canTeachCourse($request->user(), $course), 403);

        $data = $request->validated();
        $requestedSectionId = (int) ($data['course_section_id'] ?? $lesson->course_section_id);
        $targetSection = $course->sections()->whereKey($requestedSectionId)->firstOrFail();

        $data['course_section_id'] = $targetSection->id;
        $data['status'] = 'draft';
        $data['published_at'] = null;

        DB::transaction(function () use ($lesson, $data): void {
            $locked = Lesson::query()->whereKey($lesson->id)->lockForUpdate()->firstOrFail();
            $locked->update($data);

            LearningResource::query()
                ->where('lesson_id', $locked->id)
                ->update([
                    'status' => 'draft',
                    'release_at' => null,
                ]);
        }, 3);

        $notifications->notifyOwner($request->user(), $course, $lesson->fresh());

        return back()->with('success', 'تغییرات درس برای بازبینی و تأیید مالک آموزشگاه ارسال شد.');
    }

    public function destroy(
        Request $request,
        Lesson $lesson,
        MediaService $media,
        TeacherAccessService $teachers,
    ): RedirectResponse {
        $course = $lesson->section()->with('course')->firstOrFail()->course;
        abort_unless($teachers->canTeachCourse($request->user(), $course), 403);

        $files = collect();

        DB::transaction(function () use ($request, $lesson, $media, $teachers, &$files): void {
            $locked = Lesson::query()->whereKey($lesson->id)->lockForUpdate()->firstOrFail();
            $course = $locked->section()->with('course')->firstOrFail()->course;

            abort_unless($teachers->canTeachCourse($request->user(), $course), 403);

            if ($locked->status !== 'draft') {
                throw ValidationException::withMessages([
                    'lesson' => 'برای حذف درس منتشرشده، ابتدا باید درخواست خارج‌کردن آن از انتشار توسط مالک تأیید شود.',
                ]);
            }

            if ($locked->progress()->exists() || LearningResource::query()->where('lesson_id', $locked->id)->exists()) {
                throw ValidationException::withMessages([
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
