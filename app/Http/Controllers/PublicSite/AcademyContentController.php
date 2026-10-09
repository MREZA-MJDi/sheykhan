<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\AcademyContent;
use Illuminate\View\View;

final class AcademyContentController extends Controller
{
    public function show(Academy $academy, AcademyContent $content): View
    {
        abort_unless(
            $academy->status === 'active'
                && (int) $content->academy_id === (int) $academy->id
                && $content->status === 'published'
                && $content->published_at
                && $content->published_at->lte(now())
                && $content->category?->is_active,
            404
        );

        $content->load([
            'category:id,title,slug',
            'creator:id,name',
            'media' => fn ($query) => $query
                ->where('visibility', 'public')
                ->where('status', 'active')
                ->orderByPivot('sort_order'),
            'seoMeta',
        ]);

        if ($content->type === 'video') {
            abort_unless($content->media->contains(
                fn ($media) => $media->pivot?->collection === 'video'
                    && $media->collection === 'video'
                    && $media->visibility === 'public'
                    && $media->status === 'active'
            ), 404);
        }

        return view('pages.academy-content.show', [
            'academy' => $academy,
            'content' => $content,
            'seoMeta' => $content->seoMeta,
        ]);
    }
}
