<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$defaultShowTestAccounts = in_array($appEnvironment, ['local', 'testing'], true);
$showTestAccounts = filter_var(
    env('SHOW_TEST_ACCOUNTS', $defaultShowTestAccounts),
    FILTER_VALIDATE_BOOLEAN
);
$seedUserPassword = trim((string) env('SEED_USER_PASSWORD', ''));
$testPasswordAvailable = $showTestAccounts
    && ($seedUserPassword !== '' || in_array($appEnvironment, ['local', 'testing'], true));

return [
    'dashboard_routes' => [
        'seo-specialist' => 'owner.seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Local/testing gets a documented disposable password; other environments
    // must set a unique SEED_USER_PASSWORD before demo credentials are rendered.
    'show_test_accounts' => $showTestAccounts,
    'test_password' => $testPasswordAvailable
        ? ($seedUserPassword !== '' ? $seedUserPassword : 'Sheykhan@12345')
        : null,
    'allowed_test_ips' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TEST_ACCOUNTS_ALLOWED_IPS', ''))
    ))),

    'test_accounts' => [
        ['role' => 'مدیر آموزشگاه', 'role_key' => 'academy-owner', 'name' => 'مدیر آموزشگاه', 'username' => 'owner@sheykhan.test'],
        ['role' => 'معلم', 'role_key' => 'teacher', 'name' => 'سمیه احمدی · ریاضی', 'username' => 'teacher.math@sheykhan.test'],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'آرین محمدی', 'username' => 'student.armin@sheykhan.test'],
        ['role' => 'اولیا', 'role_key' => 'parent', 'name' => 'مریم محمدی', 'username' => 'parent.armin@sheykhan.test'],
        ['role' => 'متخصص SEO', 'role_key' => 'seo-specialist', 'name' => 'متخصص SEO', 'username' => 'seo@sheykhan.test'],
    ],
];
