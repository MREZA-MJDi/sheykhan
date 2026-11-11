<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'مرکز مدیریت SEO | شیخان')</title>
    @vite(['resources/css/seo-dashboard.css', 'resources/css/admin-white-system.css', 'resources/js/seo-dashboard.js'])
    @stack('styles')
</head>
<body class="owner-shell owner-admin-v2 seo-specialist-shell">
@php
    $seoUser = auth()->user();
    $seoAcademies = \App\Models\Academy::query()
        ->where('status', 'active')
        ->orderBy('name')
        ->get(['id', 'name', 'slug']);
    $seoRouteAcademy = request()->route('academy');
    $seoRouteAcademyId = $seoRouteAcademy instanceof \App\Models\Academy
        ? (int) $seoRouteAcademy->getKey()
        : (int) ($seoRouteAcademy ?? 0);
    $seoNavGroups = [
        [
            'title' => 'عملکرد جست‌وجو',
            'links' => [
                ['route' => 'seo.index', 'label' => 'نمای کلی و ممیزی صفحات', 'icon' => 'chart', 'active' => ['seo.index', 'seo.pages.*']],
                ['route' => 'seo.keywords.index', 'label' => 'کلمات کلیدی و رتبه‌ها', 'icon' => 'search', 'active' => ['seo.keywords.*']],
            ],
        ],
        [
            'title' => 'مدیریت محتوا',
            'links' => [
                ['route' => 'seo.blog.index', 'label' => 'بلاگ و مقالات', 'icon' => 'doc', 'active' => ['seo.blog.*']],
                ['route' => 'seo.content.index', 'label' => 'محتوای عمومی', 'icon' => 'book', 'active' => ['seo.content.*']],
            ],
        ],
        [
            'title' => 'تجربه و حضور سایت',
            'links' => [
                ['route' => 'seo.website.index', 'label' => 'مرکز سایت', 'icon' => 'globe', 'active' => ['seo.website.index']],
                ['route' => 'seo.website.hero.edit', 'label' => 'اسلایدر صفحه اصلی', 'icon' => 'image', 'active' => ['seo.website.hero.*']],
            ],
        ],
    ];
    $seoPageTitle = trim($__env->yieldContent('header-title')) ?: 'مرکز مدیریت SEO';
@endphp
@include('owner.partials.dashboard-icons')
<div class="seo-app">
    <aside class="seo-sidebar" id="seo-side" aria-label="ناوبری مرکز SEO">
        <a class="seo-brand" href="{{ route('seo.index') }}">
            <span class="seo-brand-mark" aria-hidden="true">ش</span>
            <span><strong>شیخان</strong><small>SEO WORKSPACE</small></span>
        </a>
        <div class="seo-sidebar-label">فضای کاری</div>
        <nav class="seo-navigation" aria-label="منوی اصلی">
            @foreach($seoNavGroups as $group)
                <section class="seo-nav-group">
                    <h2>{{ $group['title'] }}</h2>
                    @foreach($group['links'] as $link)
                        <a href="{{ route($link['route']) }}"
                           class="seo-nav-link {{ collect($link['active'])->contains(fn ($pattern) => request()->routeIs($pattern)) ? 'is-active' : '' }}"
                           @if(collect($link['active'])->contains(fn ($pattern) => request()->routeIs($pattern))) aria-current="page" @endif>
                            <svg class="ic" aria-hidden="true"><use href="#i-{{ $link['icon'] }}"/></svg>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endforeach
                </section>
            @endforeach
            @if($seoAcademies->isNotEmpty())
                <section class="seo-nav-group">
                    <h2>صفحات آموزشگاه</h2>
                    @foreach($seoAcademies as $seoAcademy)
                        <a href="{{ route('seo.academy.banners.edit', $seoAcademy) }}"
                           class="seo-nav-link {{ request()->routeIs('seo.academy.banners.*') && $seoRouteAcademyId === (int) $seoAcademy->id ? 'is-active' : '' }}"
                           @if(request()->routeIs('seo.academy.banners.*') && $seoRouteAcademyId === (int) $seoAcademy->id) aria-current="page" @endif>
                            <svg class="ic" aria-hidden="true"><use href="#i-image"/></svg>
                            <span>بنرهای {{ $seoAcademy->name }}</span>
                        </a>
                        <a href="{{ route('seo.showcase.index', $seoAcademy) }}"
                           class="seo-nav-link {{ request()->routeIs('seo.showcase.*') && $seoRouteAcademyId === (int) $seoAcademy->id ? 'is-active' : '' }}"
                           @if(request()->routeIs('seo.showcase.*') && $seoRouteAcademyId === (int) $seoAcademy->id) aria-current="page" @endif>
                            <svg class="ic" aria-hidden="true"><use href="#i-users"/></svg>
                            <span>افتخارآفرینان {{ $seoAcademy->name }}</span>
                        </a>
                    @endforeach
                </section>
            @endif
        </nav>
        <div class="seo-sidebar-footer">
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">
                <svg class="ic" aria-hidden="true"><use href="#i-globe"/></svg><span>مشاهده وب‌سایت</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button type="submit"><svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg><span>خروج از حساب</span></button>
            </form>
        </div>
    </aside>
    <div class="seo-workspace">
        <header class="seo-topbar">
            <button class="seo-menu-toggle" type="button" aria-label="باز کردن منو" aria-controls="seo-side" aria-expanded="false" data-seo-menu-toggle>
                <svg class="ic" aria-hidden="true"><use href="#i-menu"/></svg>
            </button>
            <div class="seo-breadcrumb"><span>شیخان</span><i>/</i><strong>{{ $seoPageTitle }}</strong></div>
            <div class="seo-topbar-actions">
                <a class="seo-view-site" href="{{ route('home') }}" target="_blank" rel="noopener noreferrer">مشاهده سایت <span aria-hidden="true">↗</span></a>
                <div class="seo-user-chip"><span class="seo-user-avatar">{{ mb_substr(trim((string) ($seoUser?->name ?? 'س')), 0, 1) }}</span><span><strong>{{ $seoUser?->name ?? 'متخصص SEO' }}</strong><small>متخصص بهینه‌سازی جست‌وجو</small></span></div>
            </div>
        </header>
        <main class="main main-content seo-main" id="main" tabindex="-1">
            @if(session('success') || session('error') || $errors->any())
                <section class="seo-feedback" aria-live="polite">
                    @if(session('success'))<div class="seo-alert is-success" role="status">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="seo-alert is-error" role="alert">{{ session('error') }}</div>@endif
                    @if($errors->any())<div class="seo-alert is-error" role="alert"><strong>اطلاعات فرم نیاز به اصلاح دارد.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                </section>
            @endif
            @yield('content')
            <footer class="seo-footer"><span>شیخان · مرکز مدیریت دیده‌شدن در جست‌وجو</span><span>داده‌های عملکرد فقط از منابع متصل و معتبر نمایش داده می‌شوند.</span></footer>
        </main>
    </div>
</div>
<div class="seo-sidebar-backdrop" data-seo-menu-close hidden></div>
@stack('scripts')
</body>
</html>
