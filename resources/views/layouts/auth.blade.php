<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js', 'resources/js/home.js'])
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'حساب کاربری | شیخان')</title>
    <meta name="description" content="@yield('description', 'ورود و ثبت‌نام در شیخان')">

    @vite([
        'resources/css/home.css',
        'resources/js/home.js',
    ])

    @stack('styles')
</head>
<body class="sheykhan-site min-h-screen">
    <a class="site-skip-link" href="#main-content">رفتن به محتوای اصلی</a>
    <div class="min-h-screen">
        {{-- Shared public navigation: same behavior on all public/auth pages. --}}
        <x-navigation.navbar />

        <main id="main-content" class="min-h-[calc(100vh-4rem)] px-4 py-10 sm:px-6 sm:py-14">
            <div class="mx-auto w-full max-w-md">
                @if(session('success'))
                    <div role="status" aria-live="polite" class="mb-5 rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] px-4 py-3 text-sm font-semibold text-[var(--color-success-700)]">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
