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

return [
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

    'test_accounts' => [
        ['role' => 'مدیر آموزشگاه', 'role_key' => 'academy-owner', 'name' => 'مدیر آموزشگاه', 'username' => 'owner@sheykhan.test'],
        ['role' => 'معلم', 'role_key' => 'teacher', 'name' => 'سمیه احمدی · ریاضی', 'username' => 'teacher.math@sheykhan.test'],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'آرین محمدی', 'username' => 'student.armin@sheykhan.test'],
        ['role' => 'اولیا', 'role_key' => 'parent', 'name' => 'مریم محمدی', 'username' => 'parent.armin@sheykhan.test'],
        ['role' => 'متخصص SEO', 'role_key' => 'seo-specialist', 'name' => 'متخصص SEO', 'username' => 'seo@sheykhan.test'],
    ],
];
