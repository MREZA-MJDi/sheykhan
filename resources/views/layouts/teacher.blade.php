<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل استاد | شیخان')</title>
    @vite(['resources/css/academy-teacher.css', 'resources/js/academy-teacher.js'])
    @stack('styles')
</head>
<body class="teacher-shell">
    <div class="role-layout">
        <x-navigation.teacher-sidebar />

        <div class="role-content">
            <x-navigation.panel-topbar role="teacher" title="@yield('header-title', 'پنل استاد')" />
            <main class="role-main">@yield('content')</main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
