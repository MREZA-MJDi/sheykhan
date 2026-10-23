<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدرس | شیخان')</title>
    @vite(['resources/css/app.css', 'resources/css/teacher.css', 'resources/js/app.js', 'resources/js/teacher.js'])
    @stack('styles')
</head>
<body class="teacher-shell">
    <div class="role-layout">
        <x-navigation.teacher-sidebar />

        <div class="role-content">
            @php($teacherHeaderTitle = trim($__env->yieldContent('header-title', 'پنل مدرس')))
            <x-navigation.panel-topbar role="teacher" :title="$teacherHeaderTitle" />

            <main class="role-main">
                @if (! request()->routeIs('teacher.profile.edit'))
                    <x-ui.flash-messages />
                @endif

                @if (request()->routeIs('teacher.dashboard'))
                    <x-education.workflow-guide role="teacher" />
                @endif

                @yield('content')
            </main>
        </div>

        <x-navigation.role-mobile-nav role="teacher" />
    </div>

    @stack('scripts')
</body>
</html>
