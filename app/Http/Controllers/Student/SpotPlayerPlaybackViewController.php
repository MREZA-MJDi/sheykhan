<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\SpotPlayerLicense;
use App\Models\SpotPlayerPlaybackView;
use App\Services\SpotPlayerService;
use App\Services\StudentAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SpotPlayerPlaybackViewController extends Controller
{
    public function store(
        Request $request,
        Course $course,
        SpotPlayerService $spotPlayer,
        StudentAccessService $access,
    ): JsonResponse {
        $user = $request->user();

        abort_unless($user && $user->hasRole('student'), 404);
        abort_unless($access->course($user, $course), 404);
        abort_unless($spotPlayer->isConfigured(), 503, 'اتصال SpotPlayer پیکربندی نشده است.');

        $externalCourseId = trim((string) $course->spotplayer_course_id);
        abort_if($externalCourseId === '', 409, 'شناسهٔ دوره در SpotPlayer تنظیم نشده است.');

        $license = SpotPlayerLicense::query()
            ->where('student_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->where('provider_course_id', $externalCourseId)
            ->where('provider_mode', $spotPlayer->mode())
            ->firstOrFail();

        $event = SpotPlayerPlaybackView::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'spot_player_license_id' => $license->id,
            'event_type' => 'player_open',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'viewed_at' => now(),
        ]);

        return response()->json([
            'view_id' => $event->id,
            'reference' => 'VIEW-' . $event->id,
        ], 201)->header('Cache-Control', 'no-store, private');
    }
}
