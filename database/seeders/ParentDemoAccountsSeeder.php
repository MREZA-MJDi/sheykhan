<?php

namespace Database\\Seeders;

use Database\\Seeders\\Concerns\\CreatesDemoRoleAccounts;
use Illuminate\\Database\\Seeder;

class ParentDemoAccountsSeeder extends Seeder
{
    use CreatesDemoRoleAccounts;

    public function run(): void
    {
        $this->createDemoRoleAccount(
            'parent.armin@sheykhan.test',
            'مریم محمدی',
            'parent-armin',
            'parent',
        );
    }
}
