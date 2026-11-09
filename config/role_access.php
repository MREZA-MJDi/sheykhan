<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$mobileForSeedKey = static fn (string $key): string => '0912' . str_pad(
    (string) (abs(crc32($key)) % 10000000),
    7,
    '0',
    STR_PAD_LEFT
);

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

    // Exactly one visible demo login for each supported role.
    // These keys match the role-specific seeders and AcademyFoundationSeeder.
    'test_accounts' => [
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
            'username' => $mobileForSeedKey($appEnvironment === 'testing' ? 'student-SH-1001' : 'student-armin'),
        ],
        [
            'role' => 'والد',
            'role_key' => 'parent',
            'name' => 'مریم محمدی',
            'username' => $mobileForSeedKey($appEnvironment === 'testing' ? 'parent-0' : 'parent-armin'),
        ],
    ],
];
