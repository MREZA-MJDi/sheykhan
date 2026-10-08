<?php

namespace App\Services;

use App\Models\HomeBanner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class HomeBannerService
{
    public function featured(int $limit = 3): Collection
    {
        return Cache::remember(
            "public:home:banners:v1:{$limit}",
            now()->addMinutes(2),
            fn () => HomeBanner::query()
                ->where('is_active', true)
                ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
                ->whereHas('media', fn ($query) => $query
                    ->where('visibility', 'public')
                    ->where('status', 'active')
                    ->where('mime_type', 'like', 'image/%'))
                ->with([
                    'academy:id,name',
                    'media' => fn ($query) => $query
                        ->where('visibility', 'public')
                        ->where('status', 'active')
                        ->where('mime_type', 'like', 'image/%')
                        ->orderByPivot('sort_order'),
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($limit)
                ->get()
                ->map(fn (HomeBanner $banner) => [
                    'id' => $banner->id,
                    'academy' => $banner->academy?->name,
                    'image' => $banner->media?->url(),
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'ctaLabel' => $banner->cta_label,
                    'ctaUrl' => $banner->cta_url,
                    'cropX' => (int) ($banner->crop_x ?? 50),
                    'cropY' => (int) ($banner->crop_y ?? 50),
                ])
                ->filter(fn (array $banner) => filled($banner['image']))
                ->values(),
        );
    }

    public function forgetCache(): void
    {
        Cache::forget('public:home:data:v3');

        foreach ([1, 2, 3] as $limit) {
            Cache::forget("public:home:banners:v1:{$limit}");
        }
    }
}
