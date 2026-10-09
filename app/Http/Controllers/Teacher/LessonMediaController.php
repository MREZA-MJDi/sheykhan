<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\LessonMediaRequest;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\Media;
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
        MediaService $media
    ): RedirectResponse {
        $lesson->loadMissing('section.course.academy');
        $course = $lesson->section?->course;

        // The teacher must still have an active membership in this course's
        // academy. A course-teacher pivot can outlive an archived membership.
        abort_unless(
            $course && app(TeacherAccessService::class)->canTeachCourse($request->user(), $course),
            403
        );

        $uploaded = null;

        try {
            DB::transaction(function () use ($request, $lesson, $media, &$uploaded): void {
                $uploaded = $media->upload(
                $request->file('media'),
                $lesson,
                [
                    'disk' => 'local',
                    'directory' => 'lessons/' . $lesson->id,
                    'collection' => $request->string('collection')->toString() ?: 'lesson-assets',
                    'visibility' => 'private',
                ]
                );

                LearningResource::create([
                'academy_id' => $lesson->section->course->academy_id,
                'course_id' => $lesson->section->course_id,
                'lesson_id' => $lesson->id,
                'media_id' => $uploaded->id,
                'uploaded_by' => $request->user()->id,
                'title' => $lesson->title . ' — ' . $uploaded->original_name,
                'description' => $lesson->summary,
                'resource_type' => $this->resourceType($uploaded),
                'visibility' => 'enrolled_students',
                'release_at' => $lesson->status === 'published' ? now() : null,
                'downloadable' => (bool) $request->boolean('downloadable', true),
                'status' => $lesson->status === 'published' ? 'active' : 'draft',
                    'sort_order' => $lesson->sort_order ?? 0,
                ]);
            });
        } catch (Throwable $exception) {
            if ($uploaded) {
                $media->detach($uploaded, $lesson);

                if ($uploaded->attachments()->doesntExist()) {
                    $media->delete($uploaded);
                }
            }

            throw $exception;
        }

        return back()->with('success', 'محتوای درس آپلود شد و برای دانش‌آموزان همین دوره ثبت شد.');
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
