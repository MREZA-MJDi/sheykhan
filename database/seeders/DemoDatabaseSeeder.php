<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DemoContentSeeder::class,
        ]);
    }
}
