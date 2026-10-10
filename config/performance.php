<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Critical Route Timing
    |--------------------------------------------------------------------------
    |
    | These routes expose application response time via Server-Timing. Slow
    | responses are logged with route/status/duration only, never user input.
    |
    */
    'timed_routes' => [
        'home',
        'login',
        'login.store',
        'register',
        'register.store',
        'dashboard',
        'owner.dashboard',
        'teacher.dashboard',
        'student.dashboard',
        'parent.dashboard',
    ],

    'slow_request_threshold_ms' => (int) env('SLOW_REQUEST_THRESHOLD_MS', 1500),
];
