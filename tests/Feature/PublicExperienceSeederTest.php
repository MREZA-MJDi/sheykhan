<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\BlogPost;
use App\Models\Course;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicExperienceSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_seed_builds_a_populated_public_academy_and_can_be_repeated(): void
    {
        Storage::fake('local');
        config(['legacy_students.import_on_db_seed' => false]);

        // DatabaseSeeder deliberately skips demo content under APP_ENV=testing.
        // Switch only for this test so the same local-only guard is exercised.
        $this->app->detectEnvironment(static fn (): string => 'local');

        try {
            $this->seed();

            $this->assertDatabaseCount('home_hero_slides', 3);
            $this->assertDatabaseCount('home_banners', 3);
            $this->assertDatabaseCount('courses', 3);
            $this->assertDatabaseCount('products', 6);
            $this->assertDatabaseCount('product_files', 6);
            $this->assertDatabaseHas('media', [
                'disk' => 'local',
                'path' => 'showcase-artwork/hero-curiosity.svg',
                'visibility' => 'public',
                'status' => 'active',
            ]);

            $this->get(route('home'))
                ->assertOk()
                ->assertSee('هر قدم کوچک')
                ->assertSee('آینده از یک سؤال شروع می‌شود')
                ->assertSee('ریاضی پایه هفتم')
                ->assertSee('سمیه احمدی')
                ->assertSee('برنامه مطالعه‌ای که واقعاً قابل اجراست')
                ->assertSee('دفتر کار ریتم مطالعه');

            // Verify seeded detail routes, not just hard-coded headings.
            $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();
            $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
            $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
            $post = BlogPost::query()->where('status', 'published')->firstOrFail();
            $product = Product::query()->where('slug', 'study-rhythm-workbook')->firstOrFail();
            $article = AcademyContent::query()
                ->where('academy_id', $academy->id)
                ->where('slug', 'like', 'showcase-students-%')
                ->firstOrFail();

            $this->get(route('courses.show', $course))->assertOk();
            $this->get(route('teachers.show', $teacher))->assertOk();
            $this->get(route('blog.show', $post->slug))->assertOk();
            $this->get(route('store.product.show', ['product' => $product->slug]))->assertOk();
            $this->get(route('academy.content.show', ['academy' => $academy->slug, 'content' => $article->slug]))->assertOk();

            $this->get(route('store.index', ['category' => 'booklets']))
                ->assertOk()
                ->assertSee('فیلتر فعال')
                ->assertSee('پاک کردن فیلتر')
                ->assertSee('دفتر کار ریتم مطالعه');

            $orderCountBeforePublicReseed = Order::query()->count();

            // The public-only seeder must not alter commerce order records.
            $this->seed(\Database\Seeders\PublicExperienceSeeder::class);
            $this->assertSame($orderCountBeforePublicReseed, Order::query()->count());

            // Re-seeding the full local fixture must also be idempotent.
            $this->seed();

            $this->assertDatabaseCount('home_hero_slides', 3);
            $this->assertDatabaseCount('home_banners', 3);
            $this->assertDatabaseCount('courses', 3);
            $this->assertDatabaseCount('products', 3);
            $this->assertDatabaseCount('product_files', 3);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
        }
    }

    public function test_local_showcase_seeder_refuses_to_run_outside_local_environment(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('PublicExperienceSeeder is local-demo content only');

        $this->seed(\Database\Seeders\PublicExperienceSeeder::class);
    }
}
