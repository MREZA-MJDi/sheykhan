<?php

namespace App\Services;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class TestimonialService
{
    public function featured(int $limit = 6): array
    {
        return Cache::remember("public:home:testimonials:{$limit}", now()->addMinutes(10), fn () =>
            Testimonial::query()
                ->where('status', 'approved')
                ->whereNotNull('publication_consent_at')
                ->whereNotNull('publication_consent_reference')
                ->where('is_featured', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->with([
                    'media' => fn ($query) => $query
                        ->where('visibility', 'public')
                        ->where('status', 'active')
                        ->orderByPivot('sort_order'),
                ])
                ->orderBy('sort_order')
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get()
                ->map(fn (Testimonial $testimonial) => [
                    'name' => $testimonial->display_name,
                    'role' => $testimonial->role,
                    'text' => $testimonial->content_text,
                    'image' => $testimonial->media->firstWhere('pivot.collection', 'image')?->url(),
                    'audio' => $testimonial->media->first(fn ($media) => $media->pivot?->collection === 'audio' && str_starts_with((string) $media->mime_type, 'audio/'))?->url(),
                    'video' => $testimonial->media->first(fn ($media) => $media->pivot?->collection === 'video' && str_starts_with((string) $media->mime_type, 'video/'))?->url(),
                ])
                ->all()
        );
    }
}
