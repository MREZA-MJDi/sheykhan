<?php

$appEnvironment = (string) env('APP_ENV', 'production');
$seoDemoEmail = $appEnvironment === 'testing'
    ? env('SEO_SPECIALIST_EMAIL', 'seo@sheykhan.com')
    : 'seo.demo@sheykhan.test';

return [
    'seo_specialist_email' => env('SEO_SPECIALIST_EMAIL', $seoDemoEmail),

    'dashboard_routes' => [
        'seo-specialist' => 'owner.seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Stable identities locate demo users; the login identifier is always the
    // mobile number currently stored on that user, never a generated config value.
    'show_test_accounts' => true,
    'seed_password' => 'password',
    'test_password' => 'password',
    'test_accounts' => [
        [
            'role' => 'مدیر آموزشگاه',
            'role_key' => 'academy-owner',
            'email' => 'owner@sheykhan.test',
        ],
        [
            'role' => 'متخصص SEO',
            'role_key' => 'seo-specialist',
            'email' => $seoDemoEmail,
        ],
        [
            'role' => 'مدرس',
            'role_key' => 'teacher',
            'email' => 'teacher.math@sheykhan.test',
        ],
        [
            'role' => 'دانش‌آموز',
            'role_key' => 'student',
            'email' => 'student.armin@sheykhan.test',
        ],
        [
            'role' => 'والد',
            'role_key' => 'parent',
            'email' => 'parent.armin@sheykhan.test',
        ],
    ],
];
