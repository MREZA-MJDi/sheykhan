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
        'seo-specialist' => 'seo-specialist.index',
        'academy-owner' => 'owner.dashboard',
        'teacher' => 'teacher.dashboard',
        'student' => 'student.dashboard',
        'parent' => 'parent.dashboard',
    ],


];
