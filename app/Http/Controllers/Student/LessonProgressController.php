<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\LessonProgressRequest;
use App\Models\Lesson;
use App\Services\LessonProgressService;
use Illuminate\Http\JsonResponse;

class LessonProgressController extends Controller
{
    public function store(
        LessonProgressRequest $request,
        Lesson $lesson,
        LessonProgressService $progress
    ): JsonResponse {
        $saved = $progress->saveVideoProgress(
            $request->user(),
            $lesson,
            $request->validated('from_seconds'),
            (float) $request->validated('to_seconds'),
            (bool) $request->boolean('completed')
        );

        return response()->json([
            'success' => true,
            'progress_percent' => (float) $saved->progress_percent,
            'seconds_watched' => (int) $saved->seconds_watched,
            'last_position_seconds' => (int) $saved->last_position_seconds,
            'completed' => $saved->completed_at !== null,
            'last_watched_at' => optional($saved->last_watched_at)->toIso8601String(),
        ]);
    }
}
