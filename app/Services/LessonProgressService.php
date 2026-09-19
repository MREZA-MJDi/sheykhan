<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class LessonProgressService
{
    public function saveVideoProgress(
        User $student,
        Lesson $lesson,
        float $secondsWatched,
        bool $completed = false
    ): LessonProgress {
        $lesson->loadMissing('section.course');

        $course = $lesson->section?->course;

        if (
            !$course
            || !$course->isPublished()
            || $lesson->status !== 'published'
            || !app(CourseAccessService::class)->canAccess($student, $course)
        ) {
            throw new AccessDeniedHttpException('دسترسی به این درس مجاز نیست.');
        }

        $duration = max(0, (int) $lesson->duration_seconds);
        $secondsWatched = max(0, min(
            (int) round($secondsWatched),
            $duration > 0 ? $duration : PHP_INT_MAX
        ));

        $progressPercent = $duration > 0
            ? min(100, round(($secondsWatched / $duration) * 100, 2))
            : 0;

        $isCompleted = $completed || ($duration > 0 && $progressPercent >= 99.5);

        return DB::transaction(function () use (
            $student,
            $lesson,
            $secondsWatched,
            $progressPercent,
            $isCompleted
        ): LessonProgress {
            $progress = LessonProgress::query()->firstOrNew([
                'lesson_id' => $lesson->id,
                'user_id' => $student->id,
            ]);

            $progress->progress_percent = max(
                (float) ($progress->progress_percent ?? 0),
                $progressPercent
            );
            $progress->seconds_watched = max(
                (int) ($progress->seconds_watched ?? 0),
                $secondsWatched
            );
            $progress->last_watched_at = now();

            if ($isCompleted) {
                $progress->completed_at ??= now();
                $progress->progress_percent = 100;
            }

            $progress->save();

            return $progress->refresh();
        });
    }
}
