<?php

namespace Tests\Feature;

use App\Services\PublicHomeCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class PublicHomeCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_invalidation_clears_homepage_and_all_featured_fragments(): void
    {
        $keys = [
            'public:home:data:v3',
            'public:home:data:v4',
            'public:home:data:v5',
            'public:home:data:v6',
            'public:home:courses:3',
            'public:home:courses:6',
            'public:home:live-classes:3:v1',
            'public:home:live-classes:6:v1',
            'public:home:products:3',
            'public:home:product-categories',
            'public:home:hero-slides:v1',
            'public:home:teachers:4:v2',
            'public:home:teachers:6:v2',
            'public:home:blog:latest:3:v1',
            'public:home:blog:latest:6:v1',
            'public:platform:stats:v1',
            'public:seo:sitemap:v1',
            'public:home:banners:v1:1',
            'public:home:banners:v1:2',
            'public:home:banners:v1:3',
        ];

        foreach ($keys as $key) {
            Cache::put($key, ['stale' => true], now()->addHour());
        }

        PublicHomeCache::forget();

        foreach ($keys as $key) {
            $this->assertFalse(Cache::has($key), "Expected {$key} to be invalidated.");
        }
    }
}
