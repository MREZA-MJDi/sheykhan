<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function servePublic(Media $media): Response
    {
        abort_unless($media->status === 'active' && $media->visibility === 'public', 404);
        abort_unless(Storage::disk($media->disk)->exists($media->path), 404);

        $disk = Storage::disk($media->disk);

        if (method_exists($disk, 'path')) {
            return response()->file($disk->path($media->path), [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        return $disk->response($media->path, $media->original_name, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    public function download(Media $media, MediaService $mediaService): StreamedResponse
    {
        abort_unless($media->status === 'active', 404);
        Gate::forUser(auth()->user())->authorize('download', $media);

        return $mediaService->download($media);
    }
}
