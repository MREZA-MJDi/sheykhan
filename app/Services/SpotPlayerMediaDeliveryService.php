<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\Media;

final class SpotPlayerMediaDeliveryService
{
    /**
     * Educational video files must never be returned as a raw MP4/WebM/etc.
     * Students play mapped course material through SpotPlayer instead.
     *
     * Unrelated public marketing videos and student-submitted homework are not
     * classified as SpotPlayer course media by this rule.
     */
    public function requiresProtectedPlayer(Media $media): bool
    {
        if (!str_starts_with(strtolower((string) $media->mime_type), 'video/')) {
            return false;
        }

        if (
            LearningResource::query()->where('media_id', $media->id)->exists()
            || LiveClass::query()->where('recording_media_id', $media->id)->exists()
        ) {
            return true;
        }

        foreach ($media->attachments()->with('mediable')->get() as $attachment) {
            $model = $attachment->mediable;

            if (
                $model instanceof Course
                || $model instanceof Lesson
                || $model instanceof Classroom
                || $model instanceof LiveClass
            ) {
                return true;
            }
        }

        return false;
    }

    public function courseForResource(LearningResource $resource): ?Course
    {
        // The access service may have loaded these relationships with a narrower
        // select list. Reload them so the external provider mapping is available.
        $resource->load([
            'course:id,academy_id,title,slug,spotplayer_course_id',
            'classroom.course:id,academy_id,title,slug,spotplayer_course_id',
            'lesson.section.course:id,academy_id,title,slug,spotplayer_course_id',
        ]);

        return $resource->course
            ?? $resource->classroom?->course
            ?? $resource->lesson?->section?->course;
    }

    public function courseForMedia(Media $media): ?Course
    {
        $courses = collect();

        $resources = LearningResource::query()
            ->where('media_id', $media->id)
            ->with([
                'course:id,academy_id,title,slug,spotplayer_course_id',
                'classroom.course:id,academy_id,title,slug,spotplayer_course_id',
                'lesson.section.course:id,academy_id,title,slug,spotplayer_course_id',
            ])
            ->orderByDesc('id')
            ->get();

        foreach ($resources as $resource) {
            $course = $this->courseForResource($resource);
            if ($course) {
                $courses->push($course);
            }
        }

        $recordings = LiveClass::query()
            ->where('recording_media_id', $media->id)
            ->with('course:id,academy_id,title,slug,spotplayer_course_id')
            ->orderByDesc('id')
            ->get();

        foreach ($recordings as $recording) {
            if ($recording->course) {
                $courses->push($recording->course);
            }
        }

        foreach ($media->attachments()->with('mediable')->get() as $attachment) {
            $model = $attachment->mediable;
            $course = match (true) {
                $model instanceof Course => $model->fresh(['academy:id']) ?? $model,
                $model instanceof Lesson => $model->load('section.course:id,academy_id,title,slug,spotplayer_course_id')->section?->course,
                $model instanceof Classroom => $model->load('course:id,academy_id,title,slug,spotplayer_course_id')->course,
                $model instanceof LiveClass => $model->load('course:id,academy_id,title,slug,spotplayer_course_id')->course,
                default => null,
            };

            if ($course) {
                $courses->push($course);
            }
        }

        $courses = $courses->filter()->unique('id')->values();

        // Prefer an explicitly configured external course, but never fabricate
        // a mapping from Sheykhan's internal numeric course ID.
        return $courses->first(fn (Course $course): bool => filled($course->spotplayer_course_id))
            ?? $courses->first();
    }

}
