<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateLessonProgressRequest;
use App\Models\Lesson;
use App\Services\StudentAccessService;
use Illuminate\Http\JsonResponse;

class LessonProgressController extends Controller
{
    public function update(
        UpdateLessonProgressRequest $request,
        Lesson $lesson,
        StudentAccessService $access,
    ): JsonResponse {
        abort_unless($access->lesson($request->user(), $lesson), 404);

        $incomingPercent = (float) $request->validated('progress_percent');
        $incomingSeconds = (int) $request->validated('seconds_watched', 0);

        $existing = $lesson->progress()
            ->where('user_id', $request->user()->id)
            ->first();

        $percent = max($incomingPercent, (float) ($existing?->progress_percent ?? 0));
        $seconds = max($incomingSeconds, (int) ($existing?->seconds_watched ?? 0));
        $completedAt = $percent >= 100
            ? ($existing?->completed_at ?? now())
            : $existing?->completed_at;

        $row = $lesson->progress()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'progress_percent' => $percent,
                'seconds_watched' => $seconds,
                'completed_at' => $completedAt,
                'last_watched_at' => now(),
            ],
        );

        return response()->json([
            'ok' => true,
            'progress_percent' => (float) $row->progress_percent,
            'seconds_watched' => (int) $row->seconds_watched,
            'completed' => (float) $row->progress_percent >= 100,
        ]);
    }
}
