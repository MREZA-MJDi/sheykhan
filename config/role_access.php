<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Role dashboard routes
    |--------------------------------------------------------------------------
    |
    | Keep role entry points in one place so authentication UI and future
    | navigation do not drift away from the actual route names.
    |
    */
    'dashboard_routes' => [
        'seo-specialist' => 'owner.seo.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],

    // Demo credentials are available only on local/test environments.
    'test_password' => in_array(env('APP_ENV', 'production'), ['local', 'testing'], true)
        ? (env('SEED_USER_PASSWORD') ?: 'Sheykhan@12345')
        : null,

    'test_accounts' => [
        ['role' => 'مدیر آموزشگاه / منشی', 'role_key' => 'academy-owner', 'name' => 'مدیر آموزشگاه', 'username' => 'owner@sheykhan.test'],
        ['role' => 'مدرس', 'role_key' => 'teacher', 'name' => 'سمیه احمدی · ریاضی', 'username' => 'teacher.math@sheykhan.test'],
        ['role' => 'دانش‌آموز', 'role_key' => 'student', 'name' => 'آرین محمدی', 'username' => 'student.armin@sheykhan.test'],
        ['role' => 'والد', 'role_key' => 'parent', 'name' => 'مریم محمدی', 'username' => 'parent.armin@sheykhan.test'],
        ['role' => 'متخصص SEO', 'role_key' => 'seo-specialist', 'name' => 'متخصص SEO', 'username' => 'seo@sheykhan.test'],
    ],


];
