<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Shared reference data only. Production must never acquire demo identities
        // or fake learning activity merely because "php artisan db:seed" was run.
        $this->call([
            RolePermissionSeeder::class,
            AcademicReferenceSeeder::class,
        ]);

        if (app()->environment('testing')) {
            $this->call(TestFixtureSeeder::class);
            return;
        }

        // Legacy identities are imported only with explicit private-environment opt-in.
        if (config('legacy_students.import_on_db_seed')) {
            $this->call(LegacyStudentsSeeder::class);
        }

        // Keep synthetic showcase users and records confined to local development.
        if (app()->environment('local')) {
            $this->call([
                AcademyFoundationSeeder::class,
                LearningSeeder::class,
                CommerceSeeder::class,
                FinanceSeeder::class,
                LegalSeeder::class,
                PublicContentSeeder::class,
                HomeContentSeeder::class,
                BlogSeeder::class,
                SystemSeeder::class,
                OnboardingSeeder::class,
                AuditSeeder::class,
                // Local-only art and catalog content for reviewing the public site.
                // The seeder is idempotent and preserves owner-uploaded media.
                PublicExperienceSeeder::class,
            ]);
        }
    }
}
