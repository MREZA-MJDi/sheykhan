<?php

namespace App\Services;

use App\Models\HomeHeroSetting;
use App\Models\HomeHeroSlide;
use App\Models\Media;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class HomeHeroService
{
    private const DEFAULT_IMAGE = 'https://images.unsplash.com/photo-1556761175-b413da4baf72?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1920&q=85';

    /**
     * Public, active Hero slides. This source is intentionally independent from HomeBanner.
     */
    public function slides(): Collection
    {
        return HomeHeroSlide::query()
            ->where('is_active', true)
            ->with(['media', 'mobileMedia'])
            ->orderBy('sort_order')
            ->orderBy('slot')
            ->get()
            ->map(function (HomeHeroSlide $slide): ?array {
                $image = $this->publicImage($slide->media);

                if (! $image) {
                    return null;
                }

                return [
                    'id' => $slide->id,
                    'slot' => $slide->slot,
                    'academy' => 'شیخان؛ آموزش، رشد، آینده',
                    'image' => $image,
                    'mobileImage' => $this->publicImage($slide->mobileMedia),
                    'title' => $slide->title,
                    'description' => $slide->description,
                    'ctaLabel' => $slide->cta_label,
                    'ctaUrl' => $slide->cta_url,
                    'cropX' => (int) ($slide->crop_x ?? 50),
                    'cropY' => (int) ($slide->crop_y ?? 46),
                    'mobileCropX' => (int) ($slide->mobile_crop_x ?? 54),
                    'mobileCropY' => (int) ($slide->mobile_crop_y ?? 50),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * Kept for any older caller that still renders the legacy single-image settings.
     */
    public function publicConfig(): array
    {
        $settings = HomeHeroSetting::query()
            ->with(['media', 'mobileMedia'])
            ->find(1);

        $image = $this->publicImage($settings?->media);
        $mobileImage = $this->publicImage($settings?->mobileMedia);

        return [
            'image' => $image ?? self::DEFAULT_IMAGE,
            'defaultImage' => self::DEFAULT_IMAGE,
            'mobileImage' => $mobileImage,
            'cropX' => $settings?->crop_x ?? 50,
            'cropY' => $settings?->crop_y ?? 46,
            'mobileCropX' => $settings?->mobile_crop_x ?? 54,
            'mobileCropY' => $settings?->mobile_crop_y ?? 50,
        ];
    }

    public function forgetCache(): void
    {
        Cache::forget('public:home:data:v5');
    }

    private function publicImage(?Media $media): ?string
    {
        if (
            ! $media
            || $media->visibility !== 'public'
            || $media->status !== 'active'
            || ! str_starts_with((string) $media->mime_type, 'image/')
        ) {
            return null;
        }

        try {
            $disk = Storage::disk($media->disk);

            if (! $disk->exists($media->path)) {
                return null;
            }

            return $media->url();
        } catch (\Throwable $exception) {
            // A storage outage must not take the whole public homepage down.
            report($exception);

            return null;
        }
    }
}
