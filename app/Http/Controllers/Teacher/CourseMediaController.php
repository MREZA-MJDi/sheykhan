<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use App\Models\Media;
use App\Services\CourseManagementService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class CourseMediaController extends Controller
{
    public function store(
        CourseMediaRequest $request,
        Course $course,
        CourseManagementService $courses,
        MediaService $media,
    ): RedirectResponse|JsonResponse {
        abort_unless($courses->canManage($request->user(), $course), 403);

        try {
            $media->upload(
                $request->file('media'),
                $course,
                [
                    'disk' => 'local',
                    'directory' => 'courses/' . $course->id,
                    'collection' => $request->string('collection')->toString() ?: 'course-assets',
                    'visibility' => 'private',
                ],
            );
        } catch (Throwable $exception) {
            Log::error('Teacher course media upload failed', [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'exception' => $exception,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'ذخیره فایل در سرور انجام نشد. تنظیمات فضای ذخیره‌سازی و گزارش خطا را بررسی کنید.',
                ], 500);
            }

            return back()->with('error', 'آپلود فایل انجام نشد. دوباره تلاش کنید.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'فایل با موفقیت آپلود شد.',
                'redirect_url' => route('teacher.courses.edit', $course),
            ]);
        }

        return back()->with('success', 'فایل با موفقیت آپلود شد.');
    }

    public function destroy(
        Course $course,
        Media $media,
        CourseManagementService $courses,
        MediaService $mediaService,
    ): RedirectResponse {
        abort_unless($courses->canManage(request()->user(), $course), 403);

        $attached = $media->attachments()
            ->where('mediable_type', Course::class)
            ->where('mediable_id', $course->id)
            ->exists();

        abort_unless($attached, 404);

        $mediaService->detach($media, $course);

        if ($media->attachments()->doesntExist()) {
            $mediaService->delete($media);
        }

        return back()->with('success', 'فایل حذف شد.');
    }
}
