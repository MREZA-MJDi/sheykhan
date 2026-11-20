<?php

namespace App\Http\Controllers;

use App\Http\Requests\Owner\Course\CourseMediaChunkUploadRequest;
use App\Models\Course;
use App\Services\CourseManagementService;
use App\Services\CourseMediaChunkUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CourseMediaChunkController extends Controller
{
    public function store(
        CourseMediaChunkUploadRequest $request,
        Course $course,
        CourseMediaChunkUploadService $uploads,
    ): JsonResponse {
        $result = $uploads->storeChunk(
            $request->user(),
            $course,
            $request->validated(),
            $request->file('chunk'),
        );

        return response()->json($result);
    }

    public function status(
        Request $request,
        Course $course,
        string $uploadId,
        CourseManagementService $courses,
        CourseMediaChunkUploadService $uploads,
    ): JsonResponse {
        $this->authorizeCourse($request, $course, $courses);
        $result = $uploads->status($request->user(), $course, $uploadId);
        $this->authorizeCollection($request, (string) $result['collection']);

        return response()->json($result);
    }

    public function complete(
        Request $request,
        Course $course,
        string $uploadId,
        CourseManagementService $courses,
        CourseMediaChunkUploadService $uploads,
    ): JsonResponse {
        $this->authorizeCourse($request, $course, $courses);

        try {
            $status = $uploads->status($request->user(), $course, $uploadId);
            $this->authorizeCollection($request, (string) $status['collection']);

            $media = $uploads->complete(
                $request->user(),
                $course,
                $uploadId,
                $this->allowPublicCover($request),
            );
        } catch (Throwable $exception) {
            Log::error('Chunked course media upload finalization failed', [
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
                'upload_id' => $uploadId,
                'exception' => $exception,
            ]);

            throw $exception;
        }

        $editRoute = $request->routeIs('owner.*')
            ? 'owner.courses.edit'
            : 'teacher.courses.edit';

        return response()->json([
            'message' => 'فایل با موفقیت دریافت و ذخیره شد.',
            'media_id' => $media->id,
            'redirect_url' => route($editRoute, $course),
        ]);
    }

    public function abort(
        Request $request,
        Course $course,
        string $uploadId,
        CourseManagementService $courses,
        CourseMediaChunkUploadService $uploads,
    ): JsonResponse {
        $this->authorizeCourse($request, $course, $courses);
        $uploads->abort($request->user(), $course, $uploadId);

        return response()->json(['message' => 'آپلود نیمه‌تمام پاک شد.']);
    }

    private function authorizeCourse(Request $request, Course $course, CourseManagementService $courses): void
    {
        abort_unless($request->user() && $courses->canManage($request->user(), $course), 403);
    }

    private function authorizeCollection(Request $request, string $collection): void
    {
        abort_if(
            $collection === 'course-cover'
                && (! $request->routeIs('owner.*') || ! $request->user()->hasRole('academy-owner')),
            403,
        );
    }

    private function allowPublicCover(Request $request): bool
    {
        return $request->routeIs('owner.*') && $request->user()->hasRole('academy-owner');
    }
}
