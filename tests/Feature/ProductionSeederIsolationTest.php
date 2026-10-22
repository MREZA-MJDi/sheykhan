<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionSeederIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_does_not_create_demo_people_or_catalog_in_production(): void
    {
        config(['legacy_students.import_on_db_seed' => false]);
        $this->app->detectEnvironment(static fn (): string => 'production');

        try {
            $this->seed();

            $this->assertDatabaseMissing('users', ['email' => 'owner@sheykhan.test']);
            $this->assertDatabaseCount('courses', 0);
            $this->assertDatabaseCount('products', 0);
            $this->assertDatabaseCount('home_hero_slides', 0);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
        }
    }
}
