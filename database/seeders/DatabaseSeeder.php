<?php

namespace Database\Seeders;

use Illuminate\DatabaseSeeder;

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