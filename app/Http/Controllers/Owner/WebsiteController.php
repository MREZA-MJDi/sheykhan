<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\OwnerWorkspaceService;
use Illuminate\View\View;

final class WebsiteController extends Controller
{
    public function __invoke(OwnerWorkspaceService $workspace): View
    {
        $owner = request()->user();
        $academies = $workspace->academies($owner)->load([
            'homeBanners.media',
            'media' => fn ($query) => $query
                ->where('visibility', 'public')
                ->where('status', 'active')
                ->where('mime_type', 'like', 'image/%')
                ->orderByDesc('id'),
        ]);

        return view('owner.website.index', [
            'academies' => $academies,
        ]);
    }
}
