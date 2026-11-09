<?php

return [
    'seo_specialist_email' => env('SEO_SPECIALIST_EMAIL', 'seo@sheykhan.com'),

    'dashboard_routes' => [
        'seo-specialist' => 'owner.seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Demo credentials are intentionally available in every environment.
    'show_test_accounts' => true,
    'seed_password' => 'password',
    'test_password' => 'password',

    // These mobile numbers match the existing demo users in the database.
    'test_accounts' => [
        [
            'role' => 'مدیر آموزشگاه',
            'role_key' => 'academy-owner',
            'name' => 'مدیر آموزشگاه',
            'username' => '091779234175',
        ],
        [
            'role' => 'متخصص SEO',
            'role_key' => 'seo-specialist',
            'name' => 'متخصص SEO',
            'username' => '09124860735',
        ],
        [
            'role' => 'مدرس',
            'role_key' => 'teacher',
            'name' => 'سمیه احمدی',
            'username' => '091193581267',
        ],
        [
            'role' => 'دانش‌آموز',
            'role_key' => 'student',
            'name' => 'آرین محمدی',
            'username' => '091866242658',
        ],
        [
            'role' => 'والد',
            'role_key' => 'parent',
            'name' => 'مریم محمدی',
            'username' => '091207448926',
        ],
    ],
];
