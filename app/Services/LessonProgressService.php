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
        ?float $fromSeconds,
        float $toSeconds,
        bool $completed = false
    ): LessonProgress {
        if (!app(CourseAccessService::class)->canAccessLesson($student, $lesson)) {
            throw new AccessDeniedHttpException('دسترسی به این درس مجاز نیست.');
        }

        $duration = max(0, (int) $lesson->duration_seconds);
        $to = $this->clampPosition($toSeconds, $duration);
        $from = $fromSeconds === null
            ? max(0, $to - 5)
            : $this->clampPosition($fromSeconds, $duration);

        // A sudden jump is treated as a seek, not watched time.
        // Only a small playback window is accepted per heartbeat.
        if ($to >= $from && ($to - $from) > 15) {
            $from = max(0, $to - 5);
        }

        if ($to < $from) {
            $from = $to;
        }

        return DB::transaction(function () use (
            $student,
            $lesson,
            $duration,
            $from,
            $to,
            $completed
        ): LessonProgress {
            $progress = LessonProgress::query()->firstOrNew([
                'lesson_id' => $lesson->id,
                'user_id' => $student->id,
            ]);

            $segments = $this->normalizeSegments($progress->watched_segments ?? []);

            if ($to > $from) {
                $segments = $this->mergeSegment(
                    $segments,
                    (int) floor($from),
                    (int) ceil($to),
                );
            }

            $watchedSeconds = $this->coveredSeconds($segments);
            $progressPercent = $duration > 0
                ? min(100, round(($watchedSeconds / $duration) * 100, 2))
                : 0;

            $isComplete = $duration === 0
                ? $completed
                : $progressPercent >= 95
                    || (
                        $completed
                        && $to >= max(0, $duration - 2)
                        && $progressPercent >= 90
                    );

            $progress->watched_segments = $segments;
            $progress->progress_percent = $progressPercent;
            $progress->seconds_watched = $watchedSeconds;
            $progress->last_position_seconds = $to;
            $progress->last_watched_at = now();

            if ($isComplete) {
                $progress->completed_at ??= now();
                $progress->progress_percent = 100;
            }

            $progress->save();

            return $progress->refresh();
        });
    }

    private function clampPosition(float $position, int $duration): int
    {
        $value = max(0, (int) round($position));

        return $duration > 0
            ? min($value, $duration)
            : $value;
    }

    private function normalizeSegments(array $segments): array
    {
        $normalized = [];

        foreach ($segments as $segment) {
            if (!is_array($segment) || count($segment) < 2) {
                continue;
            }

            $start = max(0, (int) ($segment[0] ?? 0));
            $end = max($start, (int) ($segment[1] ?? $start));

            if ($end > $start) {
                $normalized[] = [$start, $end];
            }
        }

        usort($normalized, fn (array $a, array $b) => $a[0] <=> $b[0]);

        $merged = [];

        foreach ($normalized as [$start, $end]) {
            if (!$merged) {
                $merged[] = [$start, $end];
                continue;
            }

            $last = &$merged[array_key_last($merged)];

            if ($start <= $last[1]) {
                $last[1] = max($last[1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        return $merged;
    }

    private function mergeSegment(array $segments, int $start, int $end): array
    {
        return $this->normalizeSegments([
            ...$segments,
            [$start, $end],
        ]);
    }

    private function coveredSeconds(array $segments): int
    {
        return array_sum(
            array_map(
                fn (array $segment) => max(0, (int) $segment[1] - (int) $segment[0]),
                $segments
            )
        );
    }
}
