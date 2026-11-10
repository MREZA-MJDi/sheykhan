<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'فرم مدیریت | شیخان')</title>
</head>
<body class="owner-shell owner-admin-v2 owner-modal-page">
    @yield('content')
    @stack('scripts')
</body>
</html>
