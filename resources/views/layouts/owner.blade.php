<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php($isSeoOnlyUser = auth()->user()?->hasRole('seo-specialist') && ! auth()->user()?->hasRole('academy-owner'))
    <title>@yield('title', $isSeoOnlyUser ? 'پنل متخصص SEO | شیخان' : 'کارتابل ادمین / منشی | شیخان')</title>
    @vite(['resources/css/owner.css','resources/js/owner.js'])
    @stack('styles')
</head>
<body class="owner-shell">
<div class="role-layout">
    <x-navigation.owner-sidebar/>
    <div class="role-content">
        @php($ownerHeaderTitle = trim($__env->yieldContent('header-title', $isSeoOnlyUser ? 'پنل متخصص SEO' : 'کارتابل ادمین / منشی')))
        <x-navigation.panel-topbar role="owner" :title="$ownerHeaderTitle" />
        <main class="role-main">
            <x-owner.feedback/>@yield('content')</main>
    </div>
</div>
<x-navigation.role-mobile-nav role="owner" />
@stack('scripts')
</body>
</html>
