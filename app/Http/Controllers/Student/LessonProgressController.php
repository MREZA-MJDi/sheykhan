<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateLessonProgressRequest;
use App\Models\Lesson;
use App\Models\Media;
use App\Services\StudentAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LessonProgressController extends Controller
{
    public function update(
        UpdateLessonProgressRequest $request,
        Lesson $lesson,
        StudentAccessService $access,
    ): JsonResponse {
        abort_unless($access->lesson($request->user(), $lesson), 404);

        $lesson->loadMissing([
            'media' => fn ($query) => $query->where('status', 'active'),
        ]);

        $hasPlayableVideo = $lesson->media->contains(
            fn (Media $media): bool => str_starts_with((string) $media->mime_type, 'video/')
        );
        $event = $request->validated('event');
        $now = now();

        $row = DB::transaction(function () use ($request, $lesson, $hasPlayableVideo, $event, $now) {
            $progress = $lesson->progress()
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            $secondsWatched = (int) ($progress?->seconds_watched ?? 0);
            $percent = (float) ($progress?->progress_percent ?? 0);
            $completedAt = $progress?->completed_at;

            if ($event === 'mark_read') {
                if ($lesson->type === 'video' || $hasPlayableVideo) {
                    throw ValidationException::withMessages([
                        'event' => 'درس ویدئویی باید با پخش‌کننده دنبال شود؛ ثبت دستی تکمیل برای آن فعال نیست.',
                    ]);
                }

                // Personal study checklist only. This does not create an exam
                // grade, assignment result, or teacher-authored assessment.
                $percent = 100;
                $completedAt ??= $now;
            } elseif ($event === 'heartbeat') {
                if (! $hasPlayableVideo || $lesson->type !== 'video') {
                    throw ValidationException::withMessages([
                        'event' => 'برای این درس پخش ویدئویی قابل‌ردیابی وجود ندارد.',
                    ]);
                }

                // Count elapsed server time between playback heartbeats, with a
                // short cap. A stale tab or long offline gap cannot grant hours
                // of progress in a single request.
                $elapsed = 0;
                if ($progress?->last_watched_at) {
                    $gap = (int) $progress->last_watched_at->diffInSeconds($now);
                    if ($gap < 60) {
                        $elapsed = min(30, max(0, $gap));
                    }
                }

                $secondsWatched += $elapsed;

                if ((int) $lesson->duration_seconds > 0) {
                    // Completion requires a separate ended event after the
                    // server has accumulated a plausible watch time.
                    $percent = max($percent, min(99, floor(
                        $secondsWatched / (int) $lesson->duration_seconds * 100
                    )));
                }
            } elseif ($event === 'playback_completed') {
                if (
                    ! $hasPlayableVideo
                    || $lesson->type !== 'video'
                    || (int) $lesson->duration_seconds < 1
                ) {
                    throw ValidationException::withMessages([
                        'event' => 'ویدئو یا مدت معتبر درس برای ثبت تکمیل در دسترس نیست.',
                    ]);
                }

                $minimumSeconds = (int) ceil((int) $lesson->duration_seconds * 0.8);
                if ($secondsWatched < $minimumSeconds) {
                    throw ValidationException::withMessages([
                        'event' => 'برای ثبت تکمیل، باید بخش عمده‌ای از زمان ویدئو در جلسه‌های پخش ثبت شده باشد.',
                    ]);
                }

                $percent = 100;
                $completedAt ??= $now;
            }

            return $lesson->progress()->updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                    'progress_percent' => min(100, max(0, $percent)),
                    'seconds_watched' => $secondsWatched,
                    'completed_at' => $completedAt,
                    'last_watched_at' => $now,
                ],
            );
        }, 3);

        return response()->json([
            'ok' => true,
            'progress_percent' => (float) $row->progress_percent,
            'seconds_watched' => (int) $row->seconds_watched,
            'completed' => (float) $row->progress_percent >= 100,
        ]);
    }
}
