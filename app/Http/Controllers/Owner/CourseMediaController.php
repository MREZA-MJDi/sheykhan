<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\Course\CourseMediaRequest;
use App\Models\Course;
use App\Models\Media;
use App\Services\CourseManagementService;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;

class CourseMediaController extends Controller
{
    public function store(
        CourseMediaRequest $request,
        Course $course,
        CourseManagementService $courses,
        MediaService $media,
    ): RedirectResponse {
        abort_unless($courses->canManage($request->user(), $course), 403);

        $validated = $request->validated();

        if ($validated['access'] === 'paid' && $course->isFree()) {
            return back()->withErrors([
                'access' => 'فایل پولی فقط برای دوره‌ای قابل استفاده است که مدل دسترسی آن پولی باشد.',
            ]);
        }

        $media->upload(
            $request->file('media'),
            $course,
            [
                'disk' => 'local',
                'directory' => 'courses/' . $course->id,
                'collection' => $validated['collection'] ?? 'course-assets',
                'visibility' => 'private',
                'metadata' => [
                    'access' => $validated['access'],
                    'downloadable' => (bool) ($validated['downloadable'] ?? true),
                ],
            ],
        );

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
