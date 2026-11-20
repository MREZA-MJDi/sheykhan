<?php

namespace App\Services;

use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CourseMediaUploadService
{
    public function __construct(private readonly MediaService $media)
    {
    }

    public function handle(
        CourseMediaRequest $request,
        Course $course,
        bool $allowPublicCover,
    ): RedirectResponse|JsonResponse {
        $collection = $request->string('collection')->toString() ?: 'course-assets';
        $isCover = $collection === 'course-cover';

        try {
            $this->media->upload(
                $request->file('media'),
                $course,
                [
                    'disk' => 'local',
                    'directory' => 'courses/' . $course->id,
                    'collection' => $collection,
                    'visibility' => $allowPublicCover && $isCover ? 'public' : 'private',
                    'sort_order' => $isCover ? 0 : 10,
                ],
            );
        } catch (Throwable $exception) {
            Log::error('Course media upload failed', [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'exception' => $exception,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'ذخیره فایل در سرور انجام نشد. تنظیمات فضای ذخیره‌سازی و گزارش خطا را بررسی کنید.',
                ], 500);
            }

            return back()->with('error', 'آپلود فایل انجام نشد. فایل را بررسی و دوباره تلاش کنید.');
        }

        if ($request->expectsJson()) {
            $editRoute = $request->routeIs('owner.*')
                ? 'owner.courses.edit'
                : 'teacher.courses.edit';

            return response()->json([
                'message' => 'فایل با موفقیت آپلود شد.',
                'redirect_url' => route($editRoute, $course),
            ]);
        }

        return back()->with('success', 'فایل با موفقیت آپلود شد.');
    }
}
