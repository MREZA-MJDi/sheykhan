<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Synthetic fixtures used only by automated tests.
 *
 * Do not call this from a production deployment. DatabaseSeeder invokes this
 * only when APP_ENV=testing.
 */
class TestFixtureSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AcademyFoundationSeeder::class,
            LearningSeeder::class,
            CommerceSeeder::class,
            FinanceSeeder::class,
            LegalSeeder::class,
            PublicContentSeeder::class,
            HomeContentSeeder::class,
            BlogSeeder::class,
            OnboardingSeeder::class,
            AuditSeeder::class,
        ]);
    }
}
