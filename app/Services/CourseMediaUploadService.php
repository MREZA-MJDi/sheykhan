<?php

namespace App\Services;

use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
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

        try {
            $this->storeUploaded(
                $course,
                $request->file('media'),
                $collection,
                $allowPublicCover,
                (int) $request->user()->id,
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

    /**
     * Store a validated file from either the standard multipart form or the
     * resumable chunk assembler. Visibility remains role-aware: only owners
     * can publish the course cover; all other course media stays private.
     */
    public function storeUploaded(
        Course $course,
        UploadedFile $file,
        string $collection,
        bool $allowPublicCover,
        int $uploadedBy,
    ): Media {
        $isCover = $collection === 'course-cover';

        return $this->media->upload(
            $file,
            $course,
            [
                'disk' => 'local',
                'directory' => 'courses/' . $course->id,
                'collection' => $collection,
                'visibility' => $allowPublicCover && $isCover ? 'public' : 'private',
                'sort_order' => $isCover ? 0 : 10,
                'uploaded_by' => $uploadedBy,
            ],
        );
    }
}
