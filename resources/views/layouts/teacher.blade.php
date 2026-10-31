<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#e6e9f7">
    <title>@yield('title', 'پنل مدرس') · {{ config('app.name', 'شیخان') }}</title>
    @vite(['resources/css/teacher/teacher.css', 'resources/js/teacher/teacher.js'])
    @stack('head')
    @stack('styles')
</head>
@php
    $tpUser = auth()->user();
    $tpUser?->loadMissing([
        'teacherProfile.media' => fn ($query) => $query
            ->wherePivotIn('collection', ['teacher-avatar', 'teacher-cover'])
            ->orderByPivot('sort_order'),
    ]);
    $tpProfile = $tpUser?->teacherProfile;
    $tpAvatar = $tpProfile?->media?->firstWhere('pivot.collection', 'teacher-avatar');
    $tpCover = $tpProfile?->media?->firstWhere('pivot.collection', 'teacher-cover');
    $tpAvatarUrl = $tpAvatar
        ? ($tpAvatar->visibility === 'public' ? $tpAvatar->url() : route('media.view', $tpAvatar))
        : null;
    $tpCoverUrl = $tpCover
        ? ($tpCover->visibility === 'public' ? $tpCover->url() : route('media.view', $tpCover))
        : null;
    $tpInitial = \Illuminate\Support\Str::substr(trim((string) ($tpUser?->name ?? 'م')), 0, 1) ?: 'م';
    $tpNav = [
        ['route' => 'teacher.dashboard', 'active' => 'teacher.dashboard', 'label' => 'داشبورد', 'icon' => '⌂', 'permission' => 'dashboard.view'],
        ['route' => 'teacher.schedule.index', 'active' => 'teacher.schedule.*', 'label' => 'برنامه هفتگی', 'icon' => '◷', 'permission' => 'live_classes.view'],
        ['route' => 'teacher.classrooms.index', 'active' => 'teacher.classrooms.*', 'label' => 'کلاس‌ها', 'icon' => '▤', 'permission' => 'classrooms.view'],
        ['route' => 'teacher.courses.index', 'active' => 'teacher.courses.*', 'label' => 'دوره‌ها و محتوا', 'icon' => '▣', 'permission' => 'courses.view'],
        ['route' => 'teacher.assignments.index', 'active' => 'teacher.assignments.*', 'label' => 'تکالیف', 'icon' => '✎', 'permission' => 'assignments.view'],
        ['route' => 'teacher.exams.index', 'active' => 'teacher.exams.*', 'label' => 'آزمون‌ها', 'icon' => '✓', 'permission' => 'exams.view'],
        ['route' => 'teacher.attendance.index', 'active' => 'teacher.attendance.*|teacher.classrooms.attendance.*', 'label' => 'حضور و غیاب', 'icon' => '☷', 'permission' => 'attendance.view'],
        ['route' => 'teacher.students.index', 'active' => 'teacher.students.*', 'label' => 'دانش‌آموزان', 'icon' => '♙', 'permission' => 'students.view'],
        ['route' => 'teacher.messages.index', 'active' => 'teacher.messages.*', 'label' => 'پیام‌های اولیا', 'icon' => '✉', 'permission' => 'parent_messages.view'],
        ['route' => 'teacher.student-messages.index', 'active' => 'teacher.student-messages.*', 'label' => 'پیام‌های دانش‌آموزان', 'icon' => '✉', 'permission' => 'student_messages.view'],
        ['route' => 'teacher.direct-messages.index', 'active' => 'teacher.direct-messages.*', 'label' => 'پیام مستقیم', 'icon' => '↗', 'permission' => 'parent_messages.manage'],
        ['route' => 'teacher.communications.index', 'active' => 'teacher.communications.*', 'label' => 'اطلاعیه‌ها', 'icon' => '⚑', 'permission' => 'parent_messages.manage'],
        ['route' => 'teacher.meetings.index', 'active' => 'teacher.meetings.*', 'label' => 'جلسات اولیا', 'icon' => '◉', 'permission' => 'live_classes.view'],
        ['route' => 'teacher.counseling-requests.index', 'active' => 'teacher.counseling-requests.*', 'label' => 'درخواست مشاوره', 'icon' => '✦', 'permission' => 'live_classes.view'],
        ['route' => 'teacher.live-classes.index', 'active' => 'teacher.live-classes.*', 'label' => 'کلاس آنلاین', 'icon' => '▷', 'permission' => 'live_classes.view'],
        ['route' => 'teacher.payments.index', 'active' => 'teacher.payments.*', 'label' => 'پرداخت‌ها', 'icon' => '＄', 'permission' => 'teacher_payments.view'],
        ['route' => 'teacher.report', 'active' => 'teacher.report', 'label' => 'گزارش عملکرد', 'icon' => '≣', 'permission' => 'dashboard.view'],
        ['route' => 'teacher.profile.edit', 'active' => 'teacher.profile.*', 'label' => 'تنظیمات پروفایل', 'icon' => '⚙', 'permission' => 'profile.view'],
    ];
    $tpNav = array_values(array_filter($tpNav, fn ($item) => $tpUser?->hasPermission($item['permission']) && \Illuminate\Support\Facades\Route::has($item['route'])));
    $tpQuick = array_values(array_filter([
        ['✓', 'ثبت حضور', 'teacher.attendance.index', '#22b573', 'attendance.view'],
        ['✎', 'تکلیف جدید', 'teacher.assignments.create', '#ff9f43', 'assignments.manage'],
        ['◈', 'ساخت آزمون', 'teacher.exams.create', '#8b5cf6', 'exams.manage'],
        ['✦', 'پیام مستقیم', 'teacher.direct-messages.index', '#14b8c4', 'parent_messages.manage'],
        ['◉', 'برنامه هفتگی', 'teacher.schedule.index', '#4a5fe0', 'live_classes.view'],
        ['♙', 'دانش‌آموزان', 'teacher.students.index', '#f0566b', 'students.view'],
    ], fn ($item) => $tpUser?->hasPermission($item[4]) && \Illuminate\Support\Facades\Route::has($item[2])));
    $tpQuick = array_map(fn ($item) => [$item[0], $item[1], route($item[2]), $item[3]], $tpQuick);
@endphp
<body class="tp">
<div class="tp-shell">
    @include('teacher.partials.sidebar')
    <div class="tp-overlay" data-tp-drawer-close></div>
    <main class="tp-main">
        @include('teacher.partials.topbar')
        @if (session('success') || session('status'))
            <div class="tp-flash tp-flash--success" role="status">{{ session('success') ?? session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="tp-flash tp-flash--warning" role="status">{{ session('warning') }}</div>
        @endif
        @if (session('error') || $errors->any())
            <div class="tp-flash tp-flash--error" role="alert">{{ session('error') ?? $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</div>
@include('teacher.partials.bottom-nav')
@include('teacher.partials.fab')
@stack('scripts')
</body>
</html>