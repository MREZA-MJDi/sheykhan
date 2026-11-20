<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Media;
use App\Services\CourseCatalogService;
use App\Services\CourseAccessService;
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
        CourseAccessService $access,
        CourseVideoStreamService $stream,
    ): StreamedResponse {
        $course = $catalog->findPublished($course);

        abort_unless(
            $media->status === 'active'
            && $media->visibility === 'private'
            && in_array(strtolower((string) $media->mime_type), ['video/mp4', 'video/webm'], true)
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

        // Keep free-course byte-range playback, but require the same active
        // enrollment, academy membership, teacher assignment or owner access
        // already enforced by the rest of the learning workspace.
        abort_unless($access->canAccess($user, $course), 403);

        // Paid-course video delivery remains on the protected SpotPlayer workflow.
        abort_unless($course->isFree(), 403);

        return $stream->stream($media, $request);
    }
}
