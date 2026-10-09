<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role dashboard routes
    |--------------------------------------------------------------------------
    |
    | Keep role entry points in one place so authentication UI and future
    | navigation do not drift away from the actual route names.
    |
    */
    'dashboard_routes' => [
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Test accounts
    |--------------------------------------------------------------------------
    |
    | Usernames are seeded accounts. The password is read from .env so the
    | credential never has to be committed to source control.
    |
    */
    'test_accounts' => [
        [
            'role' => 'مدیر آموزشگاه',
            'role_key' => 'academy-owner',
            'name' => 'مدیر آکادمی شیخان',
            'username' => 'owner@sheykhan.test',
        ],
        [
            'role' => 'مدرس',
            'role_key' => 'teacher',
            'name' => 'سمیه احمدی',
            'username' => 'teacher.math@sheykhan.test',
        ],
        [
            'role' => 'دانش‌آموز',
            'role_key' => 'student',
            'name' => 'آرین محمدی',
            'username' => 'student.armin@sheykhan.test',
        ],
        [
            'role' => 'والد',
            'role_key' => 'parent',
            'name' => 'مریم محمدی',
            'username' => 'parent.armin@sheykhan.test',
        ],
    ],

    'test_password' => env('SEED_USER_PASSWORD'),

];
