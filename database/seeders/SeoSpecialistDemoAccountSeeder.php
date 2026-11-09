<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\CreatesDemoRoleAccounts;
use Illuminate\Database\Seeder;

class SeoSpecialistDemoAccountSeeder extends Seeder
{
    use CreatesDemoRoleAccounts;

    public function run(): void
    {
        $this->createDemoRoleAccount(
            (string) config('role_access.seo_specialist_email'),
            'متخصص SEO',
            'seo-specialist',
            'seo-specialist',
        );
    }
}
