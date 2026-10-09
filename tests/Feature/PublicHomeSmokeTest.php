<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\HomeBanner;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PublicHomeSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_primary_public_destinations_are_reachable(): void
    {
        $home = $this->get(route('home'));

        $home->assertOk()
            ->assertSee('آموزش خوب')
            ->assertSee('data-frame-hero', false)
            ->assertSee('home-frame-hero__title', false)
            ->assertDontSee('pater/pater.css')
            ->assertDontSee('js/demo4.js')
            ->assertDontSee('home-banner-slider');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('public-page-intro--courses', false);
        $this->get(route('teachers.index'))->assertOk();
        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('public-page-intro--editorial', false);
        $this->get(route('store.index'))->assertOk();
    }

    public function test_homepage_remains_available_when_a_real_public_banner_is_configured(): void
    {
        $owner = User::factory()->create();

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی بنر',
            'slug' => 'banner-academy',
            'status' => 'active',
        ]);

        $media = Media::create([
            'uploaded_by' => $owner->id,
            'disk' => 'local',
            'path' => 'academies/' . $academy->id . '/home-banners/banner.webp',
            'original_name' => 'banner.webp',
            'file_name' => 'banner.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 1024,
            'checksum' => hash('sha256', 'banner'),
            'visibility' => 'public',
            'collection' => 'home-banners',
            'metadata' => [],
            'status' => 'active',
        ]);

        $academy->media()->attach($media->id, [
            'collection' => 'home-banners',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        HomeBanner::create([
            'academy_id' => $academy->id,
            'media_id' => $media->id,
            'slot' => 1,
            'title' => 'بنر واقعی صفحه اصلی',
            'sort_order' => 1,
            'crop_x' => 50,
            'crop_y' => 50,
            'is_active' => true,
        ]);

        Cache::flush();

        $this->get(route('home'))->assertOk();
    }

    public function test_unknown_public_path_returns_not_found(): void
    {
        $this->get('/this-page-definitely-does-not-exist')->assertNotFound();
    }

    public function test_missing_course_returns_not_found(): void
    {
        $this->get('/courses/999999999')->assertNotFound();
    }
}
