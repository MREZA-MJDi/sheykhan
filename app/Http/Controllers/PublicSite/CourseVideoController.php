<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Media;
use App\Services\CourseAccessService;
use App\Services\CourseCatalogService;
use App\Services\CourseVideoStreamService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CourseVideoController extends Controller
{
    public function stream(
        Request $request,
        Course $course,
        Media $media,
        CourseCatalogService $catalog,
        CourseVideoStreamService $stream,
    ): StreamedResponse {
        $course = $catalog->findPublished($course);

        abort_unless(
            $media->status === 'active'
            && str_starts_with(strtolower((string) $media->mime_type), 'video/')
            && in_array(strtolower((string) $media->extension), ['mp4', 'webm'], true),
            404
        );

        $attachedToCourse = $media->attachments()
            ->where('mediable_type', Course::class)
            ->where('mediable_id', $course->id)
            ->where('collection', 'course-assets')
            ->exists();

        abort_unless($attachedToCourse, 404);

        $user = $request->user();
        abort_unless($user && $user->status === 'active', 403);

        // This endpoint is deliberately limited to free courses. Paid-course
        // video delivery remains on the protected SpotPlayer workflow.
        abort_unless($course->isFree(), 403);

        return $stream->stream($media, $request);
    }
}
