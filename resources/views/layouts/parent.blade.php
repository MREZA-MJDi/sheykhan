<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل والد | شیخان')</title>
    @vite(['resources/css/parent.css', 'resources/js/parent.js'])
    @stack('styles')
</head>
<body class="parent-shell">
    <div class="role-layout">
        <x-navigation.parent-sidebar />

        <div class="role-content">
            <x-navigation.panel-topbar role="parent" title="@yield('header-title', 'پنل والد')" />
            <main class="role-main">@yield('content')</main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
