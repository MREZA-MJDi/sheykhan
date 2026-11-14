<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Course\StoreCourseRequest;
use App\Http\Requests\Owner\Course\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseManagementService;
use App\Services\OwnerLearningAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(CourseManagementService $service): View
    {
        $owner = request()->user();
        $data = $service->ownerIndexData($owner);
        $workspaceAcademy = $service->accessibleAcademies($owner)
            ->first(fn ($academy) => (int) $academy->owner_id === (int) $owner->id);

        return view('owner.courses.index', [...$data, 'workspaceAcademy' => $workspaceAcademy]);
    }

    public function create(CourseManagementService $service): View
    {
        return view('owner.courses.form', [
            'course' => new Course(),
            'academies' => $service->accessibleAcademies(request()->user()),
        ]);
    }

    public function store(StoreCourseRequest $request, CourseManagementService $service): RedirectResponse
    {
        $course = $service->create($request->user(), $request->validated());

        return redirect()->route('owner.courses.edit', $course)
            ->with('success', 'دوره ساخته شد. حالا کاور عمومی و تریلر را در همین صفحه اضافه کن.');
    }

    public function show(Course $course, CourseManagementService $service, OwnerLearningAnalyticsService $analytics): View
    {
        abort_unless($service->canView(request()->user(), $course), 403);

        $course->load([
            'academy:id,name',
            'teachers:id,name',
            'sections.lessons',
            'media' => fn ($query) => $query->orderByPivot('sort_order'),
            'exams' => fn ($query) => $query
                ->with('teacher:id,name')
                ->withCount(['questions', 'attempts'])
                ->latest()
                ->limit(8),
            'assignments' => fn ($query) => $query
                ->with('teacher:id,name')
                ->withCount([
                    'submissions',
                    'submissions as pending_submissions_count' => fn ($submissions) => $submissions
                        ->whereNotNull('submitted_at')
                        ->whereNull('graded_at'),
                ])
                ->latest()
                ->limit(8),
        ]);

        $course->loadCount([
            'enrollments as active_students_count' => fn ($query) => $query->where('status', 'active'),
            'assignments',
            'exams',
            'liveClasses',
        ]);

        $averageProgress = (float) ($analytics->courseProgress([$course->id])->get($course->id) ?? 0);

        $canPermanentlyDeleteCourse = $course->status === 'archived'
            && $course->enrollments()->doesntExist()
            && $course->sections()->doesntExist()
            && $course->classrooms()->doesntExist()
            && $course->assignments()->doesntExist()
            && $course->exams()->doesntExist()
            && $course->liveClasses()->doesntExist()
            && $course->learningResources()->doesntExist()
            && $course->spotplayerLicenses()->doesntExist();

        return view('owner.courses.show', [
            'course' => $course,
            'averageProgress' => round((float) $averageProgress, 1),
            'canManageCourse' => $service->canManage(request()->user(), $course),
            'canPermanentlyDeleteCourse' => $canPermanentlyDeleteCourse,
        ]);
    }

    public function edit(Course $course, CourseManagementService $service): View
    {
        abort_unless($service->canManage(request()->user(), $course), 403);

        return view('owner.courses.form', [
            'course' => $course->load([
                'academy',
                'media' => fn ($query) => $query->orderByPivot('sort_order'),
            ]),
            'academies' => $service->accessibleAcademies(request()->user()),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course, CourseManagementService $service): RedirectResponse
    {
        $service->update($request->user(), $course, $request->validated());

        return redirect()->route('owner.courses.show', $course)
            ->with('success', 'دوره با موفقیت به‌روزرسانی شد.');
    }

    public function archive(Course $course, CourseManagementService $service): RedirectResponse
    {
        $service->archive(request()->user(), $course);

        return redirect()->route('owner.courses.index')
            ->with('success', 'دوره از فهرست فعال خارج شد؛ سوابق ثبت‌نام و آموزشی حفظ شده‌اند.');
    }

    /**
     * Hard-delete only an archived course that has never accumulated learning,
     * financial, classroom or provider history. Everything else remains archived.
     */
    public function destroy(
        Request $request,
        Course $course,
        CourseManagementService $service,
        MediaService $media,
    ): RedirectResponse {
        abort_unless($service->canManage($request->user(), $course), 403);

        if ($course->status !== 'archived') {
            throw ValidationException::withMessages([
                'course' => 'برای حذف دائمی، ابتدا دوره را آرشیو کن.',
            ]);
        }

        $files = collect();

        DB::transaction(function () use ($request, $course, $service, $media, &$files): void {
            $locked = Course::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();

            abort_unless($service->canManage($request->user(), $locked), 403);

            $hasHistory = $locked->enrollments()->exists()
                || $locked->sections()->exists()
                || $locked->classrooms()->exists()
                || $locked->assignments()->exists()
                || $locked->exams()->exists()
                || $locked->liveClasses()->exists()
                || $locked->learningResources()->exists()
                || $locked->spotplayerLicenses()->exists();

            if ($locked->status !== 'archived' || $hasHistory) {
                throw ValidationException::withMessages([
                    'course' => 'این دوره سابقهٔ آموزشی، ثبت‌نام، کلاس یا لایسنس دارد؛ حذف دائمی مجاز نیست و باید آرشیو بماند.',
                ]);
            }

            $files = $locked->media()->get();
            foreach ($files as $file) {
                $media->detach($file, $locked);
            }

            $locked->teachers()->detach();
            $locked->grades()->detach();

            if (method_exists($locked, 'seoMeta')) {
                $locked->seoMeta()->delete();
            }

            $locked->delete();
        }, 3);

        foreach ($files->unique('id') as $file) {
            if ($file->attachments()->doesntExist()) {
                $media->delete($file);
            }
        }

        app(\App\Services\CourseCatalogService::class)->clearPublicCache();

        return redirect()->route('owner.courses.index')
            ->with('success', 'دورهٔ خالی و آرشیوشده به‌طور دائمی حذف شد.');
    }

    public function restore(Course $course, CourseManagementService $service): RedirectResponse
    {
        $service->restoreArchived(request()->user(), $course);

        return redirect()->route('owner.courses.index')
            ->with('success', 'دوره به‌صورت پیش‌نویس بازیابی شد؛ برای نمایش دوباره باید آن را منتشر کنی.');
    }
}
