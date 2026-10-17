<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Production-safe foundations only. Real people and learning records must
        // be imported/created intentionally; demo learners must never appear in a
        // production database as a side effect of "php artisan db:seed".
        $this->call([
            RolePermissionSeeder::class,
            AcademicReferenceSeeder::class,
        ]);

        // Automated tests need repeatable fixtures. Keep demo identities and
        // fake learning activity confined to the isolated testing environment.
        if (app()->environment('testing')) {
            $this->call(TestFixtureSeeder::class);
        }
    }
}
