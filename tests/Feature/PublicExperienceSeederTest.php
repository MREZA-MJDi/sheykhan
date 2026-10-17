<?php

namespace Tests\Feature;

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
            $this->assertDatabaseCount('products', 3);
            $this->assertDatabaseCount('product_files', 3);
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

            // Re-seeding must update the showcase, not duplicate catalog rows.
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
