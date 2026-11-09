<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\CreatesDemoRoleAccounts;
use Illuminate\Database\Seeder;

class AcademyOwnerDemoAccountSeeder extends Seeder
{
    use CreatesDemoRoleAccounts;

    public function run(): void
    {
        $this->createDemoRoleAccount(
            'owner@sheykhan.test',
            'مدیر آکادمی شیخان',
            'owner',
            'academy-owner',
        );
    }
}
