<?php

namespace DatabaseSeeders;

use IlluminateDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DemoContentSeeder::class,
            DemoFinanceSeeder::class,
        ]);
    }
}