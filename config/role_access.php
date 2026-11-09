<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$seedUserPassword = trim((string) env('SEED_USER_PASSWORD', ''));
$localOrTesting = in_array($appEnvironment, ['local', 'testing'], true);

// Local/testing gets a disposable password. Outside those environments, seeded
// credentials are shown only when an explicit password has been configured.
$defaultShowTestAccounts = $localOrTesting || $seedUserPassword !== '';
$showTestAccounts = filter_var(
    env('SHOW_TEST_ACCOUNTS', $defaultShowTestAccounts),
    FILTER_VALIDATE_BOOLEAN
);
$seedPasswordAvailable = $seedUserPassword !== '' || $localOrTesting;
$seedPassword = $seedPasswordAvailable
    ? ($seedUserPassword !== '' ? $seedUserPassword : 'Sheykhan@12345')
    : null;

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

    // Configuring SEED_USER_PASSWORD enables the visible role panel by default.
    // Set SHOW_TEST_ACCOUNTS=false to explicitly hide it in any environment.
    'show_test_accounts' => $showTestAccounts,
    'seed_password' => $seedPassword,
    'test_password' => $showTestAccounts && $seedPasswordAvailable ? $seedPassword : null,

    // Keep this list in sync with AcademyFoundationSeeder. The login controller
    // only renders accounts that exist, are active, have the expected role, and
    // still accept the configured seed password.
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
