<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_role_demo_accounts_without_seeding_synthetic_catalog_in_production(): void
    {
        config(['legacy_students.import_on_db_seed' => false]);
        $this->app->detectEnvironment(static fn (): string => 'production');

        try {
            $this->artisan('db:seed', ['--force' => true])->assertExitCode(0);

            foreach ([
                'owner@sheykhan.test',
                (string) config('role_access.seo_specialist_email'),
                'teacher.math@sheykhan.test',
                'student.armin@sheykhan.test',
                'parent.armin@sheykhan.test',
            ] as $email) {
                $this->assertDatabaseHas('users', ['email' => $email]);
            }

            $this->assertDatabaseCount('courses', 0);
            $this->assertDatabaseCount('products', 0);
            $this->assertDatabaseCount('home_hero_slides', 0);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
        }
    }
}
