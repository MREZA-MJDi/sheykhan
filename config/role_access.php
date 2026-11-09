<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$seedPassword = 'password';
$showTestAccounts = true;

$mobileForSeedKey = static fn (string $key): string => '0912' . str_pad(
    (string) (abs(crc32($key)) % 10000000),
    7,
    '0',
    STR_PAD_LEFT
);
$studentSeedKey = $appEnvironment === 'testing' ? 'student-SH-1001' : 'student-armin';
$parentSeedKey = $appEnvironment === 'testing' ? 'parent-0' : 'parent-armin';
$studentSeedKeyFor = static fn (string $localKey, string $testingKey): string =>
    $appEnvironment === 'testing' ? $testingKey : $localKey;

return [
    'seo_specialist_email' => env('SEO_SPECIALIST_EMAIL', 'seo@sheykhan.com'),

    'dashboard_routes' => [
        'seo-specialist' => 'owner.seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Demo role credentials are consistent across environments and shown on the login page.
    'show_test_accounts' => $showTestAccounts,
    'seed_password' => $seedPassword,
    'test_password' => $seedPassword,

    // Keep this list in sync with the role-specific demo account seeders. The login
    // controller verifies account existence, active status, role, and password.
    'test_accounts' => array_merge([
        ['role' => 'مدیر آموزشگاه', 'role_key' => 'academy-owner', 'name' => 'مدیر آموزشگاه', 'username' => $mobileForSeedKey('owner')],
        ['role' => 'معلم ریاضی', 'role_key' => 'teacher', 'name' => 'سمیه احمدی', 'username' => $mobileForSeedKey('teacher-math')],
        ['role' => 'معلم علوم', 'role_key' => 'teacher', 'name' => 'علی رضایی', 'username' => $mobileForSeedKey('teacher-science')],
        ['role' => 'معلم تیزهوشان', 'role_key' => 'teacher', 'name' => 'نگار کریمی', 'username' => $mobileForSeedKey('teacher-gifted')],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'آرین محمدی', 'username' => $mobileForSeedKey($studentSeedKey)],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'نیکا حسینی', 'username' => $mobileForSeedKey($studentSeedKeyFor('student-nika', 'student-SH-1002'))],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'پارسا رضایی', 'username' => $mobileForSeedKey($studentSeedKeyFor('student-parsa', 'student-SH-1003'))],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'آوا محمدی', 'username' => $mobileForSeedKey($studentSeedKeyFor('student-ava', 'student-SH-1004'))],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'متین احمدی', 'username' => $mobileForSeedKey($studentSeedKeyFor('student-matin', 'student-SH-1005'))],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'تارا کریمی', 'username' => $mobileForSeedKey($studentSeedKeyFor('student-tara', 'student-SH-1006'))],
        ['role' => 'اولیا', 'role_key' => 'parent', 'name' => 'مریم محمدی', 'username' => $mobileForSeedKey($parentSeedKey)],
        ['role' => 'متخصص SEO', 'role_key' => 'seo-specialist', 'name' => 'متخصص SEO', 'username' => $mobileForSeedKey('seo-specialist')],
    ], $appEnvironment === 'testing' ? [
        ['role' => 'اولیا', 'role_key' => 'parent', 'name' => 'حسین حسینی', 'username' => $mobileForSeedKey('parent-1')],
        ['role' => 'اولیا', 'role_key' => 'parent', 'name' => 'الهام رضایی', 'username' => $mobileForSeedKey('parent-2')],
    ] : []),
];
