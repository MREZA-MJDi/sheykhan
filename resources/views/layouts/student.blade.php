<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل دانش‌آموز | شیخان')</title>
    @vite(['resources/css/student-parent.css', 'resources/js/student-parent.js'])
    @stack('styles')
</head>
<body class="student-shell">
    <div class="role-layout">
        <x-navigation.student-sidebar />

        <div class="role-content">
            <x-navigation.panel-topbar role="student" title="@yield('header-title', 'پنل دانش‌آموز')" />
            <main class="role-main">@yield('content')</main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
