<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\LessonMediaRequest;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\Media;
use App\Services\LessonReviewNotificationService;
use App\Services\MediaService;
use App\Services\TeacherAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class LessonMediaController extends Controller
{
    public function store(
        LessonMediaRequest $request,
        Lesson $lesson,
        MediaService $media,
        TeacherAccessService $teachers,
        LessonReviewNotificationService $notifications,
    ): RedirectResponse {
        $lesson->loadMissing('section.course.academy.owner');
        $course = $lesson->section?->course;

        abort_unless($course && $teachers->canTeachCourse($request->user(), $course), 403);

        $uploaded = null;

        try {
            DB::transaction(function () use ($request, $lesson, $course, $media, &$uploaded): void {
                $lockedLesson = Lesson::query()->whereKey($lesson->id)->lockForUpdate()->firstOrFail();

                $lockedLesson->forceFill([
                    'status' => 'draft',
                    'published_at' => null,
                ])->save();

                LearningResource::query()
                    ->where('lesson_id', $lockedLesson->id)
                    ->update([
                        'status' => 'draft',
                        'release_at' => null,
                    ]);

                $uploaded = $media->upload(
                    $request->file('media'),
                    $lockedLesson,
                    [
                        'disk' => 'local',
                        'directory' => 'lessons/' . $lockedLesson->id,
                        'collection' => $request->string('collection')->toString() ?: 'lesson-assets',
                        'visibility' => 'private',
                    ]
                );

                LearningResource::create([
                    'academy_id' => $course->academy_id,
                    'course_id' => $course->id,
                    'lesson_id' => $lockedLesson->id,
                    'media_id' => $uploaded->id,
                    'uploaded_by' => $request->user()->id,
                    'title' => $lockedLesson->title . ' — ' . $uploaded->original_name,
                    'description' => $lockedLesson->summary,
                    'resource_type' => $this->resourceType($uploaded),
                    'visibility' => 'enrolled_students',
                    'release_at' => null,
                    'downloadable' => (bool) $request->boolean('downloadable', true),
                    'status' => 'draft',
                    'sort_order' => $lockedLesson->sort_order ?? 0,
                ]);
            }, 3);
        } catch (Throwable $exception) {
            if ($uploaded) {
                $media->detach($uploaded, $lesson);

                if ($uploaded->attachments()->doesntExist()) {
                    $media->delete($uploaded);
                }
            }

            throw $exception;
        }

        $notifications->notifyOwner($request->user(), $course, $lesson->fresh());

        return back()->with('success', 'فایل به‌صورت خصوصی ذخیره شد و برای تأیید مالک آموزشگاه در صف بازبینی قرار گرفت.');
    }

    private function resourceType(Media $media): string
    {
        return match (true) {
            str_starts_with((string) $media->mime_type, 'video/') => 'video',
            str_starts_with((string) $media->mime_type, 'image/') => 'image',
            $media->mime_type === 'application/zip' => 'archive',
            default => 'document',
        };
    }
}
