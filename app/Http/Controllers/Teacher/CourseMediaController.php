<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use App\Models\Media;
use App\Services\CourseManagementService;
use App\Services\CourseMediaUploadService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CourseMediaController extends Controller
{
    public function store(
        CourseMediaRequest $request,
        Course $course,
        CourseMediaUploadService $uploads,
    ): RedirectResponse|JsonResponse {
        return $uploads->handle($request, $course, false);
    }

    public function destroy(
        Course $course,
        Media $media,
        CourseManagementService $courses,
        MediaService $mediaService,
    ): RedirectResponse {
        abort_unless($courses->canManage(request()->user(), $course), 403);

        abort_if($media->collection === 'course-cover', 403);

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
