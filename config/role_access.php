<?php

$mobileForSeedKey = static fn (string $key): string => '0912' . str_pad(
    (string) (abs(crc32($key)) % 10000000),
    7,
    '0',
    STR_PAD_LEFT
);

// Keep the quick-login directory aligned with the idempotent demo-account seeders.
// The persisted mobile is resolved by email at login-page render time, so legacy
// numbers remain usable without shipping environment-specific stale phone numbers.
$isLocalEnvironment = in_array((string) env('APP_ENV', 'local'), ['local', 'testing'], true);
$demoPassword = (string) env('SEED_USER_PASSWORD', $isLocalEnvironment ? 'password' : '');
$showDemoAccounts = $demoPassword !== ''
    && (bool) env('SHOW_TEST_ACCOUNTS', env('SHOW_DEMO_ACCOUNTS', $isLocalEnvironment));

$testAccounts = [
    [
        'role' => 'مدیر آموزشگاه',
        'role_key' => 'academy-owner',
        'name' => 'مدیر آموزشگاه',
        'email' => 'owner@sheykhan.test',
        'username' => $mobileForSeedKey('owner'),
    ],
    [
        'role' => 'متخصص SEO',
        'role_key' => 'seo-specialist',
        'name' => 'متخصص SEO',
        'email' => env('SEO_SPECIALIST_EMAIL', 'seo@sheykhan.com'),
        'username' => $mobileForSeedKey('seo-specialist'),
    ],
    [
        'role' => 'مدرس',
        'role_key' => 'teacher',
        'name' => 'سمیه احمدی',
        'email' => 'teacher.math@sheykhan.test',
        'username' => $mobileForSeedKey('teacher-math'),
    ],
    [
        'role' => 'دانش‌آموز',
        'role_key' => 'student',
        'name' => 'آرین محمدی',
        'email' => 'student.armin@sheykhan.test',
        'username' => $mobileForSeedKey('student-armin'),
    ],
    [
        'role' => 'والد',
        'role_key' => 'parent',
        'name' => 'مریم محمدی',
        'email' => 'parent.armin@sheykhan.test',
        'username' => $mobileForSeedKey('parent-armin'),
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

    // This value is shared by seeders and the quick-login UI; never duplicate it.
    'show_test_accounts' => $showDemoAccounts,
    'seed_password' => $demoPassword,
    'test_password' => $demoPassword,
    'test_accounts' => $testAccounts,
];
