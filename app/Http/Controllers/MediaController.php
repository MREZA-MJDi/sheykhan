<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function download(Media $media, MediaService $mediaService): StreamedResponse
    {
        abort_unless(auth()->check(), 401);
        abort_unless($media->status === 'active', 404);

        return $mediaService->download($media);
    }
}
