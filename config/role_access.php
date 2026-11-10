<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$mobileForSeedKey = static fn (string $key): string => '0912' . str_pad(
    (string) (abs(crc32($key)) % 10000000),
    7,
    '0',
    STR_PAD_LEFT
);

// Tests and local development seed accounts with deterministic fixture mobiles.
// Production uses the persisted demo users' real mobile numbers.
$testAccounts = in_array($appEnvironment, ['local', 'testing'], true)
    ? [
        [
            'role' => 'مدیر آموزشگاه',
            'role_key' => 'academy-owner',
            'name' => 'مدیر آموزشگاه',
            'username' => $mobileForSeedKey('owner'),
        ],
        [
            'role' => 'متخصص SEO',
            'role_key' => 'seo-specialist',
            'name' => 'متخصص SEO',
            'username' => $mobileForSeedKey('seo-specialist'),
        ],
        [
            'role' => 'مدرس',
            'role_key' => 'teacher',
            'name' => 'سمیه احمدی',
            'username' => $mobileForSeedKey('teacher-math'),
        ],
        [
            'role' => 'دانش‌آموز',
            'role_key' => 'student',
            'name' => 'آرین محمدی',
            'username' => $mobileForSeedKey('student-SH-1001'),
        ],
        [
            'role' => 'والد',
            'role_key' => 'parent',
            'name' => 'مریم محمدی',
            'username' => $mobileForSeedKey('parent-0'),
        ],
    ]
    : [
        [
            'role' => 'مدیر آموزشگاه',
            'role_key' => 'academy-owner',
            'name' => 'مدیر آموزشگاه',
            'username' => '091779234175',
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
    ];

return [
    'seo_specialist_email' => env('SEO_SPECIALIST_EMAIL', 'seo@sheykhan.com'),

    'dashboard_routes' => [
        'seo-specialist' => 'seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Demo credentials are intentionally available in every environment.
    'show_test_accounts' => true,
    'seed_password' => 'password',
    'test_password' => 'password',

    'test_accounts' => $testAccounts,
];