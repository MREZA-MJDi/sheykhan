<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\AcademyContentCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\CourseCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicReleaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_store_renders_real_product_destination(): void
    {
        $category = ProductCategory::create([
            'name' => 'جزوه',
            'slug' => 'notes',
            'description' => 'منابع آموزشی',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'جزوه تستی',
            'slug' => 'test-note-' . Str::random(8),
            'product_type' => 'digital',
            'delivery_type' => 'download',
            'price' => 250000,
            'currency' => 'IRR',
            'status' => 'published',
            'is_featured' => true,
            'published_at' => now()->subMinute(),
        ]);

        $response = $this->get(route('store.index'));

        $response
            ->assertOk()
            ->assertSee(route('store.product.show', $product), false)
            ->assertSee('۲۵۰٬۰۰۰ تومان', false);
    }

    public function test_sitemap_excludes_content_that_public_route_rejects(): void
    {
        $owner = User::factory()->create();

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی انتشار',
            'slug' => 'release-academy-' . Str::random(8),
            'status' => 'active',
        ]);

        $category = AcademyContentCategory::create([
            'academy_id' => $academy->id,
            'title' => 'دسته غیرفعال',
            'slug' => 'inactive-' . Str::random(8),
            'is_active' => false,
        ]);

        $content = AcademyContent::create([
            'academy_id' => $academy->id,
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'نباید در sitemap باشد',
            'slug' => 'not-indexable-' . Str::random(8),
            'body' => 'متن',
            'status' => 'published',
            'published_at' => now()->subMinute(),
            'created_by' => $owner->id,
        ]);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertDontSee(route('academy.content.show', [$academy, $content]), false);
    }

    public function test_public_course_catalog_does_not_hydrate_section_lesson_trees_for_cards(): void
    {
        $this->seed();

        $paginator = app(CourseCatalogService::class)->paginate();

        if ($paginator->count() === 0) {
            $this->markTestSkipped('No published course fixture is available.');
        }

        $course = $paginator->first();

        $this->assertFalse($course->relationLoaded('sections'));
        $this->assertTrue($course->relationLoaded('lessons_count') || array_key_exists('lessons_count', $course->getAttributes()));
    }

    public function test_public_teacher_profile_links_to_visible_courses(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->first();

        if (!$teacher) {
            $this->markTestSkipped('No public teacher fixture is available.');
        }

        $course = $teacher->taughtCourses()
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->first();

        if (!$course) {
            $this->markTestSkipped('No published teacher course fixture is available.');
        }

        $response = $this->get(route('teachers.show', $teacher));

        $response
            ->assertOk()
            ->assertSee(route('courses.show', $course), false)
            ->assertSee($course->title, false);
    }
}
