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
            ->assertSee('site-skip-link', false)
            ->assertSee(route('store.index'), false)
            ->assertSee('هر قدم کوچک')
            ->assertSee('home-academy-story', false)
            ->assertSee('home-paths__intro', false)
            ->assertSee('دسترسی سریع به بخش‌های شیخان')
            ->assertSee('sheykhan-home-hero', false)
            ->assertSee('data-home-hero', false)
            ->assertSee('shx-public-hero-title', false)
            ->assertSee('public-announcement', false)
            ->assertSee('site-navbar', false)
            ->assertSee('public-footer', false)
            ->assertSee('public-journey', false)
            ->assertSee('public-faq', false)
            ->assertSeeInOrder([
                'class="sheykhan-home-hero"',
                'id="upcoming-classes"',
                'class="public-proof-stats"',
                'class="public-comparison',
                'class="public-journey',
                'id="public-destinations"',
                'id="featured-courses"',
                'id="teachers-section"',
                'class="public-parent-preview',
                'class="public-faq',
                'id="start-learning"',
            ], false)
            ->assertDontSee('data-tiamir-intro', false)
            ->assertDontSee('data-tiamir-canvas', false)
            ->assertDontSee('pater/pater.css')
            ->assertDontSee('js/demo4.js')
            ->assertDontSee('home-banner-slider');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('public-page-intro--courses', false);
        $this->get(route('teachers.index'))
            ->assertOk()
            ->assertSee('public-page-intro', false);
        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('public-page-intro--editorial', false);
        $this->get(route('store.index'))
            ->assertOk()
            ->assertSee('public-page-intro', false);
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

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('home-banner-grid', false)
            ->assertSee('بنر واقعی صفحه اصلی');
    }

    public function test_unknown_public_path_returns_not_found(): void
    {
        $this->get('/this-page-definitely-does-not-exist')->assertNotFound();
    }

    public function test_missing_course_returns_not_found(): void
    {
        $this->get('/courses/999999999')->assertNotFound();
    }


    public function test_public_layout_uses_one_isolated_asset_pipeline_and_a_small_flat_design_system(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/public.blade.php'));
        $entry = file_get_contents(resource_path('css/public.css'));
        $tokens = file_get_contents(resource_path('css/public/tokens.css'));
        $base = file_get_contents(resource_path('css/public/base.css'));
        $components = file_get_contents(resource_path('css/public/components.css'));
        $sections = file_get_contents(resource_path('css/public/sections.css'));
        $publicJs = file_get_contents(resource_path('js/public.js'));
        $vite = file_get_contents(base_path('vite.config.js'));

        foreach ([$layout, $entry, $tokens, $base, $components, $sections, $publicJs, $vite] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString("resources/css/public.css", $layout);
        $this->assertStringContainsString("resources/js/public.js", $layout);
        foreach ([
            'resources/css/app.css',
            'resources/css/home.css',
            'resources/css/public-experience.css',
            'resources/css/public-site.css',
            'resources/css/public-design-system.css',
            'resources/js/app.js',
        ] as $legacyAsset) {
            $this->assertStringNotContainsString($legacyAsset, $layout);
            $this->assertStringNotContainsString($legacyAsset, $vite);
        }

        $this->assertStringContainsString("@import './public/tokens.css';", $entry);
        $this->assertStringContainsString("@import './public/base.css';", $entry);
        $this->assertStringContainsString("@import './public/components.css';", $entry);
        $this->assertStringContainsString("@import './public/sections.css';", $entry);
        $this->assertStringContainsString("@source '../../resources/views/pages';", $entry);
        $this->assertStringNotContainsString("@source '../../resources/views';", $entry);
        $this->assertStringContainsString('--public-ink:#0f172a', $tokens);
        $this->assertStringContainsString('--public-blue:#1f4e9e', $tokens);
        $this->assertStringContainsString('--public-green:#0b7a63', $tokens);
        $this->assertStringContainsString('--public-violet:#5b3fc4', $tokens);
        $this->assertStringContainsString('--public-orange:#b25a06', $tokens);
        $this->assertStringContainsString('--public-rose:#b72b58', $tokens);
        $this->assertStringContainsString('.public-site .sheykhan-home-hero', $components);
        $this->assertStringContainsString('.public-course-card', $components);
        $this->assertStringContainsString('.public-teacher-card', $components);
        $this->assertStringContainsString('.public-store-product-card', $components);
        $this->assertStringContainsString('.public-blog-card', $components);
        $this->assertStringContainsString('.public-next-step__card', $components);
        $this->assertStringContainsString('.public-site .home-banner-grid', $sections);
        $this->assertStringContainsString('.public-site .home-banner-media img', $sections);
        $this->assertStringContainsString('sheykhan-hero-image-drift', $sections);
        $this->assertStringContainsString('prefers-reduced-motion', $base);
        $this->assertStringContainsString("import './home.js';", $publicJs);
        $this->assertStringContainsString("import './public-site.js';", $publicJs);
        $this->assertStringNotContainsString('panel-runtime', $publicJs);
        $this->assertStringNotContainsString("@stack('styles')", $layout);

        $cssFiles = [$entry, $tokens, $base, $components, $sections];
        $this->assertLessThan(100_000, array_sum(array_map('strlen', $cssFiles)));

        foreach ($cssFiles as $css) {
            $this->assertStringNotContainsString('!important', $css);
            $this->assertStringNotContainsString('gradient(', $css);
            $this->assertStringNotContainsString('backdrop-filter', $css);
            $this->assertStringNotContainsString('box-shadow', $css);
        }

        foreach (['home.css', 'public-experience.css', 'public-site.css', 'public-design-system.css'] as $legacyFile) {
            $this->assertFileDoesNotExist(resource_path('css/' . $legacyFile));
        }

        $blade = file_get_contents(resource_path('views/pages/home/partials/hero.blade.php'));
        $this->assertIsString($blade);
        $this->assertStringContainsString('sheykhan-home-hero', $blade);
        $this->assertStringContainsString('data-home-hero-slide', $blade);
        $this->assertStringContainsString('aria-labelledby', $blade);
        $this->assertStringNotContainsString('style=', $blade);
    }
}
