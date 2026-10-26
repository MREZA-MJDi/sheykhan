<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'داشبورد آکادمی شیخان')</title>
    @vite(['resources/css/owner-dashboard-v2.css', 'resources/js/owner-dashboard.js'])
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>
