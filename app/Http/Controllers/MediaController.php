<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use App\Services\SpotPlayerMediaDeliveryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function servePublic(Media $media, \App\Policies\MediaPolicy $policy): Response
    {
        abort_unless($media->status === 'active' && $media->visibility === 'public', 404);

        // Visibility mistakes must not turn a lesson, assessment, recording or
        // course handout into an unauthenticated public download.
        abort_if($policy->requiresProtectedDelivery($media), 404);
        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        $disk = Storage::disk($media->disk);

        if (method_exists($disk, 'path')) {
            return response()->file($disk->path($media->path), [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        return $disk->response($media->path, $media->original_name, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function view(
        Media $media,
        MediaService $mediaService,
        SpotPlayerMediaDeliveryService $spotPlayerDelivery,
        \App\Policies\MediaPolicy $policy,
    ): Response {
        abort_unless($media->status === 'active', 404);

        $user = auth()->user();

        if ($spotPlayerDelivery->requiresProtectedPlayer($media)) {
            if ($user?->hasRole('student')) {
                abort_unless(
                    $policy->canViewThroughSpotPlayer($user, $media),
                    403,
                    'دسترسی این فایل برای حساب شما فعال نیست.'
                );

                $course = $spotPlayerDelivery->courseForMedia($media);
                abort_unless($course, 409, 'این ویدئو هنوز به یک دورهٔ شیخان متصل نشده است.');

                if (blank($course->spotplayer_course_id)) {
                    abort(409, 'برای پخش این ویدئو، مدیر باید شناسهٔ واقعی دوره در پنل SpotPlayer را ثبت کند.');
                }

                    return redirect()->to(route('student.courses.show', $course) . '#spotplayer-access');
            }

            if ($user?->hasRole('parent')) {
                abort_unless(
                    $policy->canViewThroughSpotPlayer($user, $media),
                    403,
                    'دسترسی این فایل برای حساب شما فعال نیست.'
                );

                abort(403, 'برای حفظ دسترسی اختصاصی فرزند، ویدئوی محافظت‌شده باید از حساب دانش‌آموز پخش شود.');
            }
        }
        try {
            Gate::forUser(auth()->user())->authorize('view', $media);
        } catch (AuthorizationException) {
            abort(403, 'دسترسی این فایل برای حساب شما فعال نیست.');
        }

        if ($spotPlayerDelivery->requiresProtectedPlayer($media)) {
            abort(409, 'این فایل ویدئویی باید از پلیر رسمی SpotPlayer پخش شود؛ فایل خام برای هیچ نقش کاربری ارائه نمی‌شود.');
        }

        if (
            $policy->requiresProtectedDelivery($media)
            && ! \App\Models\ProductFile::query()->where('media_id', $media->id)->where('is_preview', false)->exists()
        ) {
            return redirect()->route('protected-learning-media.view', $media)
                ->header('Cache-Control', 'no-store, private')
                ->header('Referrer-Policy', 'no-referrer');
        }

        return $mediaService->inline($media);
    }

    public function download(Media $media, MediaService $mediaService): Response
    {
        abort_unless($media->status === 'active', 404);

        try {
            Gate::forUser(auth()->user())->authorize('download', $media);
        } catch (AuthorizationException) {
            abort(403, 'دسترسی این فایل برای حساب شما فعال نیست. ممکن است این محتوا نیاز به خرید یا عضویت در کلاس مرتبط داشته باشد.');
        }

        return $mediaService->download($media);
    }
}
