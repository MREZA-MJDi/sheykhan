<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Services\CourseManagementService;
use App\Notifications\TeacherLessonSubmittedForReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class CourseLessonReviewController extends Controller
{
    public function publish(
        Request $request,
        Course $course,
        Lesson $lesson,
        CourseManagementService $courses,
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasPermission('courses.manage')
                && $courses->isAcademyOwner($request->user(), $course),
            403,
        );

        abort_if($course->status === 'archived', 409);

        DB::transaction(function () use ($course, $lesson): void {
            $lesson->loadMissing('section');

            abort_unless(
                $lesson->section && (int) $lesson->section->course_id === (int) $course->id,
                404,
            );

            $locked = Lesson::query()
                ->whereKey($lesson->id)
                ->lockForUpdate()
                ->firstOrFail();

            $locked->loadMissing('section');
            abort_unless(
                $locked->section && (int) $locked->section->course_id === (int) $course->id,
                404,
            );
            abort_if($locked->status === 'archived', 409);

            $locked->forceFill([
                'status' => 'published',
                'published_at' => now(),
            ])->save();

            LearningResource::query()
                ->where('lesson_id', $locked->id)
                ->update([
                    'status' => 'active',
                    'release_at' => now(),
                ]);
        }, 3);

        $request->user()->notifications()
            ->where('type', TeacherLessonSubmittedForReview::class)
            ->where('data->lesson_id', $lesson->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'درس پس از بازبینی مالک آموزشگاه منتشر شد.');
    }
}
