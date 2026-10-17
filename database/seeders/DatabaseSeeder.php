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
            return;
        }

        // The 55 real legacy students are imported only after an explicit
        // operator opt-in and private-source/academy configuration.
        if (config('legacy_students.import_on_db_seed')) {
            $this->call(LegacyStudentsSeeder::class);
        }

        // A rich, image-backed public demo is useful for local design review.
        // This seeder has its own APP_ENV=local guard and is never run in production.
        if (app()->environment('local')) {
            $this->call([
                AcademyFoundationSeeder::class,
                PublicExperienceSeeder::class,
            ]);
        }
    }
}
