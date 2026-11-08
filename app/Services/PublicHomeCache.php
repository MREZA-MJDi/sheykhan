<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Central invalidation for every cached fragment rendered on the public homepage.
 *
 * Content editors must invalidate the fragment cache as well as the aggregate
 * page data; otherwise a refreshed homepage can still contain stale cards.
 */
final class PublicHomeCache
{
    public static function forget(): void
    {
        foreach ([
            'public:home:data:v3',
            'public:home:data:v4',
            'public:home:data:v5',
            'public:home:courses:3',
            'public:home:courses:6',
            'public:home:live-classes:3:v1',
            'public:home:live-classes:6:v1',
            'public:home:teachers:4:v2',
            'public:home:teachers:6:v2',
            'public:home:blog:latest:3:v1',
            'public:home:blog:latest:6:v1',
            'public:platform:stats:v1',
            'public:seo:sitemap:v1',
            'public:home:banners:v1:1',
            'public:home:banners:v1:2',
            'public:home:banners:v1:3',
            'public:home:hero-slides:v1',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
