<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$mobileForSeedKey = static fn (string $key): string => '0912' . str_pad(
    (string) (abs(crc32($key)) % 10000000),
    7,
    '0',
    STR_PAD_LEFT
);

// Keep these fixture keys identical to AcademyFoundationSeeder and the role demo seeders.
// A mismatch makes AuthController correctly hide the credentials because no matching
// active user with the expected role/password exists.
$testAccounts = [
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
        'username' => $mobileForSeedKey('student-armin'),
    ],
    [
        'role' => 'والد',
        'role_key' => 'parent',
        'name' => 'مریم محمدی',
        'username' => $mobileForSeedKey('parent-armin'),
    ],
];

return [
    'seo_specialist_email' => env(
        'SEO_SPECIALIST_EMAIL',
        $appEnvironment === 'testing' ? 'seo@sheykhan.com' : 'seo.demo@sheykhan.test'
    ),

    'dashboard_routes' => [
        'seo-specialist' => 'owner.seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Quick-fill credentials are shown only for matching, active, persisted demo users.
    'show_test_accounts' => true,
    'seed_password' => 'password',
    'test_password' => 'password',
    'test_accounts' => $testAccounts,
];
