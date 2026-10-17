<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'مرکز SEO | شیخان')</title>
    @vite(['resources/css/seo-specialist.css', 'resources/js/seo-specialist.js'])
    @stack('styles')
</head>
<body class="seo-specialist-shell">
    <header class="seo-specialist-header">
        <a href="{{ route('seo-specialist.index') }}" class="seo-specialist-brand">
            <span class="seo-specialist-brand-mark">ش</span>
            <span><strong>شیخان</strong><small>مرکز تخصصی SEO</small></span>
        </a>
        <nav class="seo-specialist-nav" aria-label="منوی متخصص SEO">
            <a href="{{ route('seo-specialist.index') }}" @if(request()->routeIs('seo-specialist.index')) aria-current="page" @endif>وضعیت صفحات</a>
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">مشاهده سایت ↗</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">خروج</button>
            </form>
        </nav>
    </header>
    <main class="seo-specialist-main">
        <x-owner.feedback />
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
