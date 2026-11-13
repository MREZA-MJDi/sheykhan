<?php

namespace App\Services;

/**
 * The public homepage loads only the content it actually renders:
 * fixed hero copy, managed visual highlights/banners, featured courses and
 * the latest articles. Operational dashboards and store inventory stay off
 * this request path.
 */
final class HomeService
{
    public function __construct(
        private readonly HomeHeroService $hero,
        private readonly HomeBannerService $banners,
        private readonly CourseCatalogService $courses,
        private readonly BlogService $blog,
    ) {
    }

    public function getData(): array
    {
        return [
            'homeHeroSlides' => $this->hero->slides(),
            'homeBanners' => $this->banners->featured(3),
            'courseCards' => $this->courses->featuredCards(3),
            'latestPosts' => $this->blog->latest(3),
        ];
    }
}
