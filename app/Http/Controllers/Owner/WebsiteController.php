<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use Illuminate\View\View;

final class WebsiteController extends Controller
{
    public function __invoke(): View
    {
        $academies = Academy::query()
            ->where('status', 'active')
            ->with([
                'homeBanners.media',
                'media' => fn ($query) => $query
                    ->where('visibility', 'public')
                    ->where('status', 'active')
                    ->where('mime_type', 'like', 'image/%')
                    ->orderByDesc('id'),
            ])
            ->orderBy('name')
            ->get();

        return view('owner.website.index', [
            'academies' => $academies,
            'canManagePublicHero' => $academies->isNotEmpty(),
        ]);
    }
}
