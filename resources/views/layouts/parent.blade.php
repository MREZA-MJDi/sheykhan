<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل والد | شیخان')</title>
    @vite(['resources/css/parent.css', 'resources/js/parent.js'])
    @stack('styles')
</head>
<body class="parent-shell">
    <div class="role-layout">
        <x-navigation.parent-sidebar />

        <div class="role-content">
            @php
                $parentHeaderTitle = trim($__env->yieldContent('header-title', 'پنل والد'));
            @endphp
            <x-navigation.panel-topbar role="parent" :title="$parentHeaderTitle" />
            <main class="role-main">
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
                    <button type="button" class="ui-flash-close" data-ui-dismiss data-ui-flash-close aria-label="بستن پیام">×</button>
                </div>
            @endif
        @endforeach
        @if($errors->any())
            <div data-ui-dismissible class="ui-flash ui-flash-error" role="alert" aria-live="assertive">
                <span class="ui-flash-icon" aria-hidden="true">!</span>
                <div class="ui-flash-copy"><strong>اطلاعات بررسی نشد</strong><span>{{ $errors->first() }}</span></div>
                <button type="button" class="ui-flash-close" data-ui-dismiss data-ui-flash-close aria-label="بستن پیام">×</button>
            </div>
        @endif
    </div>
@endif
                @yield('content')
            </main>
        </div>
        <x-navigation.role-mobile-nav role="parent" />
    </div>
    @stack('scripts')
</body>
</html>
