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
        $resource->loadMissing([
            'course:id,academy_id,title,slug,spotplayer_course_id',
            'classroom.course:id,academy_id,title,slug,spotplayer_course_id',
            'lesson.section.course:id,academy_id,title,slug,spotplayer_course_id',
        ]);

        return $resource->course
            ?? $resource->classroom?->course
            ?? $resource->lesson?->section?->course;
    }
}
