<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Shared reference data and role demo identities are safe to seed in every
        // non-testing environment; synthetic activity/content remains local-only.
        $this->call([
            RolePermissionSeeder::class,
            AcademicReferenceSeeder::class,
        ]);

        if (app()->environment('testing')) {
            $this->call(TestFixtureSeeder::class);
            return;
        }

        // Demo identities are optional in staging/production and require an explicit
        // private password. Never abort a production seed when the secret is absent.
        // Each role seeder remains idempotent and independent when enabled.
        if (filled(config('role_access.seed_password'))) {
            $this->call(DemoAccountsSeeder::class);
        }

        // Legacy identities are imported only with explicit private-environment opt-in.
        if (config('legacy_students.import_on_db_seed')) {
            $this->call(LegacyStudentsSeeder::class);
        }

        // Keep synthetic showcase users and records confined to local development.
        if (app()->environment('local')) {
            $this->call([
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
