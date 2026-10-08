<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicSeoOutputTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_course_renders_managed_seo_in_public_html(): void
    {
        $owner = User::factory()->create();
        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی SEO',
            'slug' => 'seo-academy-' . Str::random(6),
            'status' => 'active',
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست سئو',
            'slug' => 'seo-course-' . Str::random(6),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'short_description' => 'توضیح پیش‌فرض',
            'published_at' => now()->subMinute(),
        ]);

        $meta = SeoMeta::create([
            'seoable_type' => Course::class,
            'seoable_id' => $course->id,
            'title' => 'عنوان اختصاصی گوگل',
            'description' => 'توضیح اختصاصی برای موتورهای جستجو',
            'canonical_url' => route('courses.show', $course),
            'robots' => 'index,follow',
            'og_title' => 'عنوان اشتراک‌گذاری',
            'og_description' => 'توضیح اشتراک‌گذاری',
        ]);

        $response = $this->get(route('courses.show', $course));

        $response
            ->assertOk()
            ->assertSee('<title>' . e($meta->title) . '</title>', false)
            ->assertSee('name="description" content="' . e($meta->description) . '"', false)
            ->assertSee('name="robots" content="index,follow"', false)
            ->assertSee('rel="canonical" href="' . e($meta->canonical_url) . '"', false)
            ->assertSee('property="og:title" content="' . e($meta->og_title) . '"', false);
    }

    public function test_robots_and_sitemap_are_real_public_endpoints(): void
    {
        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: ' . url('/sitemap.xml'), false)
            ->assertSee('Disallow: /owner', false);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->assertSee('<loc>' . url('/') . '</loc>', false);
    }

    public function test_custom_json_ld_is_script_safe(): void
    {
        $owner = User::factory()->create();
        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی امن',
            'slug' => 'safe-seo-' . Str::random(8),
            'status' => 'active',
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره امنیت',
            'slug' => 'safe-course-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'short_description' => 'توضیح',
            'published_at' => now()->subMinute(),
        ]);

        SeoMeta::create([
            'seoable_type' => Course::class,
            'seoable_id' => $course->id,
            'title' => 'SEO',
            'robots' => 'index,follow',
            'schema_json' => [
                '@context' => 'https://schema.org',
                'description' => '</script><script>window.__xss=1</script>',
            ],
        ]);

        $response = $this->get(route('courses.show', $course))->assertOk();

        $response->assertSee('\\u003C/script\\u003E\\u003Cscript\\u003Ewindow.__xss=1\\u003C/script\\u003E', false);
        $response->assertDontSee('</script><script>window.__xss=1</script>', false);
    }

}
