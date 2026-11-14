<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use App\Models\Media;
use App\Services\CourseManagementService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class CourseMediaController extends Controller
{
    public function store(
        CourseMediaRequest $request,
        Course $course,
        CourseManagementService $courses,
        MediaService $media,
    ): RedirectResponse {
        abort_unless($courses->canManage($request->user(), $course), 403);

        try {
            $collection = $request->string('collection')->toString() ?: 'course-assets';
            $uploaded = $media->upload(
                $request->file('media'),
                $course,
                [
                    'disk' => 'local',
                    'directory' => 'courses/' . $course->id,
                    'collection' => $collection,
                    'visibility' => in_array($collection, ['course-cover', 'course-trailer'], true) ? 'public' : 'private',
                    'sort_order' => $collection === 'course-cover' ? 0 : ($collection === 'course-trailer' ? 1 : 10),
                ],
            );

            // Keep a single current public teaser. Upload succeeds before an older
            // trailer is detached, so a failed upload never removes the active one.
            if ($collection === 'course-trailer') {
                $previousTrailers = $course->media()
                    ->wherePivot('collection', 'course-trailer')
                    ->where('media.id', '!=', $uploaded->id)
                    ->get();

                foreach ($previousTrailers as $previousTrailer) {
                    $media->detach($previousTrailer, $course);

                    if ($previousTrailer->attachments()->doesntExist()) {
                        $media->delete($previousTrailer);
                    }
                }
            }
        } catch (Throwable $exception) {
            Log::error('Owner course media upload failed', [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'exception' => $exception,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'آپلود فایل انجام نشد. فایل را بررسی و دوباره تلاش کنید.',
                ], 422);
            }

            return back()->with('error', 'آپلود فایل انجام نشد. فایل را بررسی و دوباره تلاش کنید.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'فایل با موفقیت آپلود شد.',
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

        try {
            $mediaService->detach($media, $course);

            if ($media->attachments()->doesntExist()) {
                $mediaService->delete($media);
            }
        } catch (Throwable $exception) {
            Log::error('Owner course media deletion failed', [
                'course_id' => $course->id,
                'media_id' => $media->id,
                'user_id' => request()->user()->id,
                'exception' => $exception,
            ]);

            return back()->with('error', 'حذف فایل انجام نشد. دوباره تلاش کنید.');
        }

        return back()->with('success', 'فایل حذف شد.');
    }
}