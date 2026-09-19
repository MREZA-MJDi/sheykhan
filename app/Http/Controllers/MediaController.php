<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function download(Media $media, MediaService $mediaService): StreamedResponse
    {
        abort_unless($media->status === 'active', 404);
        Gate::forUser(auth()->user())->authorize('download', $media);

        return $mediaService->download($media);
    }
}
