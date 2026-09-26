<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','مدیریت آموزشگاه | شیخان')</title>
    @vite(['resources/css/owner.css','resources/js/owner.js'])
    @stack('styles')
</head>
<body class="owner-shell">
<div class="role-layout">
<x-navigation.owner-sidebar />
<div class="role-content">
<x-navigation.panel-topbar role="owner"  />
<main class="role-main"><x-owner.feedback />@yield('content')</main>
</div>
</div>
@stack('scripts')
</body>
</html>
