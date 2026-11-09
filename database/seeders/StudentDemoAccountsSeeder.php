<?php

namespace Database\\Seeders;

use Database\\Seeders\\Concerns\\CreatesDemoRoleAccounts;
use Illuminate\\Database\\Seeder;

class StudentDemoAccountsSeeder extends Seeder
{
    use CreatesDemoRoleAccounts;

    public function run(): void
    {
        foreach ([
            ['student.armin@sheykhan.test', 'آرین محمدی', 'student-armin'],
            ['student.nika@sheykhan.test', 'نیکا حسینی', 'student-nika'],
            ['student.parsa@sheykhan.test', 'پارسا رضایی', 'student-parsa'],
            ['student.ava@sheykhan.test', 'آوا محمدی', 'student-ava'],
            ['student.matin@sheykhan.test', 'متین احمدی', 'student-matin'],
            ['student.tara@sheykhan.test', 'تارا کریمی', 'student-tara'],
        ] as [$email, $name, $mobileKey]) {
            $this->createDemoRoleAccount($email, $name, $mobileKey, 'student');
        }
    }
}
