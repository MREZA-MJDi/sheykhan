<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AcademicReferenceSeeder::class,
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
        ]);
    }
}
