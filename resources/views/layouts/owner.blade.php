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
    <x-navigation.owner-sidebar/>
    <div class="role-content">
        @php
            $isSeoOnlyUser = auth()->user()?->hasRole('seo-specialist') && !auth()->user()?->hasRole('academy-owner');
            $ownerPanelRole = $isSeoOnlyUser ? 'seo' : 'owner';
            $ownerHeaderTitle = trim($__env->yieldContent('header-title', $isSeoOnlyUser ? 'SEO و دیده‌شدن' : 'مدیریت آموزشگاه'));
            $mobileNavigationRole = $ownerPanelRole;
        @endphp
        <x-navigation.panel-topbar :role="$ownerPanelRole" :title="$ownerHeaderTitle" />
        <main class="role-main">
            <x-owner.feedback/>@yield('content')</main>
    </div>
</div>
<x-navigation.role-mobile-nav :role="$mobileNavigationRole" />
@stack('scripts')
</body>
</html>
