<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

/**
 * Synchronize only the documented demo login identities and their configured
 * password. Safe to run explicitly after changing SEED_USER_PASSWORD.
 */
class DemoAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AcademyOwnerDemoAccountSeeder::class,
            SeoSpecialistDemoAccountSeeder::class,
            TeacherDemoAccountsSeeder::class,
            StudentDemoAccountsSeeder::class,
            ParentDemoAccountsSeeder::class,
            AcademyFoundationSeeder::class,
        ]);
    }
}
