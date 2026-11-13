<?php

namespace App\Services;

/**
 * The public homepage intentionally loads only the content it renders.
 * Operational dashboards, store inventory, hero campaigns and platform
 * statistics have their own pages and should not add queries to this route.
 */
final class HomeService
{
    public function __construct(
        private readonly CourseCatalogService $courses,
        private readonly BlogService $blog,
    ) {
    }

    public function getData(): array
    {
        return [
            'courseCards' => $this->courses->featuredCards(3),
            'latestPosts' => $this->blog->latest(3),
        ];
    }
}
