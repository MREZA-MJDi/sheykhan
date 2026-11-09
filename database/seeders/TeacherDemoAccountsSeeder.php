<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\CreatesDemoRoleAccounts;
use Illuminate\Database\Seeder;

class TeacherDemoAccountsSeeder extends Seeder
{
    use CreatesDemoRoleAccounts;

    public function run(): void
    {
        foreach ([
            ['teacher.math@sheykhan.test', 'سمیه احمدی', 'teacher-math'],
            ['teacher.science@sheykhan.test', 'علی رضایی', 'teacher-science'],
            ['teacher.gifted@sheykhan.test', 'نگار کریمی', 'teacher-gifted'],
        ] as [$email, $name, $mobileKey]) {
            $this->createDemoRoleAccount($email, $name, $mobileKey, 'teacher');
        }
    }
}
