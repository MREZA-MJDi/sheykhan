<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل دانش‌آموز | شیخان')</title>
    @vite(['resources/css/student.css', 'resources/js/student.js'])
    @stack('styles')
</head>
<body>
    <div class="role-layout">
        <main class="role-main">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
