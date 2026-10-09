<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo.head :seo-meta="$seoMeta ?? null" />

    @php
    $pageAssets = [
        'resources/css/public.css',
        'resources/js/public.js',
    ];

    if (request()->routeIs('home')) {
        $pageAssets = array_merge($pageAssets, [
            'resources/css/home.css',
            'resources/css/home-meraki-hero.css',
            'resources/css/tiamir-intro.css',
            'resources/js/home.js',
            'resources/js/tiamir-intro.js',
        ]);
    }
@endphp
@vite($pageAssets)
    @stack('styles')
</head>
<body class="sheykhan-site">
    @if (request()->routeIs('home'))
        @include('components.branding.tiamir-intro')
    @endif

    <div id="app">
        <x-navigation.navbar />
        <main id="main-content" class="{{ request()->routeIs('home') ? 'home-page' : 'public-page' }}">
            <x-ui.flash-messages />
            @yield('content')
        </main>
        <x-navigation.footer />
    </div>

    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
    @stack('scripts')
</body>
</html>
