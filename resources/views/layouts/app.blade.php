<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo.head :seo-meta="$seoMeta ?? null" />

    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js', 'resources/js/home.js'])
    @stack('styles')
</head>
<body class="sheykhan-site">
    <a class="site-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <div id="app">
        <x-navigation.navbar />
        <main id="main-content" class="home-page">
            @if(!request()->routeIs('checkout.course.show', 'orders.show'))
                <x-ui.flash-messages />
            @endif
            @yield('content')
        </main>
        <x-navigation.footer />
    </div>

    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
    @stack('scripts')
</body>
</html>
