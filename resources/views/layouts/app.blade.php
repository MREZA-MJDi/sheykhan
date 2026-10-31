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
                @if(session()->hasAny(['success', 'error', 'warning', 'info']) || $errors->any())
    <div class="ui-flash-stack" data-ui-flash-stack>
        @foreach([
            'success' => ['title' => 'انجام شد', 'icon' => '✓'],
            'error' => ['title' => 'نیاز به توجه', 'icon' => '!'],
            'warning' => ['title' => 'توجه', 'icon' => '⚠'],
            'info' => ['title' => 'اطلاع', 'icon' => 'i'],
        ] as $type => $meta)
            @if(session($type))
                <div data-ui-dismissible class="ui-flash ui-flash-{{ $type }}" role="{{ $type === 'error' ? 'alert' : 'status' }}" aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}">
                    <span class="ui-flash-icon" aria-hidden="true">{{ $meta['icon'] }}</span>
                    <div class="ui-flash-copy"><strong>{{ $meta['title'] }}</strong><span>{{ session($type) }}</span></div>
                    <button type="button" class="ui-flash-close" data-ui-dismiss aria-label="بستن پیام">×</button>
                </div>
            @endif
        @endforeach
        @if($errors->any())
            <div data-ui-dismissible class="ui-flash ui-flash-error" role="alert" aria-live="assertive">
                <span class="ui-flash-icon" aria-hidden="true">!</span>
                <div class="ui-flash-copy"><strong>اطلاعات بررسی نشد</strong><span>{{ $errors->first() }}</span></div>
                <button type="button" class="ui-flash-close" data-ui-dismiss aria-label="بستن پیام">×</button>
            </div>
        @endif
    </div>
@endif
            @endif
            @yield('content')
        </main>
        <x-navigation.footer />
    </div>

    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
    @stack('scripts')
</body>
</html>
