<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
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
    <div class="min-h-screen">
        <header class="home-glass-nav sticky top-0 z-[var(--z-sticky)] border-b border-[var(--color-border)]">
            <x-layout.container size="wide">
                <div class="flex min-h-16 items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="شیخان">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-slate-900)] text-sm font-black text-white">ش</span>
                        <span class="leading-tight">
                            <span class="block text-sm font-black text-[var(--color-text)]">شیخان</span>
                            <span class="block text-[11px] text-[var(--color-text-muted)]">آموزش، رشد، آینده</span>
                        </span>
                    </a>

                    <a href="{{ route('home') }}" class="text-sm font-bold text-[var(--color-text-secondary)] transition hover:text-[var(--color-primary-600)]">
                        بازگشت به خانه
                    </a>
                </div>
            </x-layout.container>
        </header>

        <main class="min-h-[calc(100vh-4rem)] px-4 py-10 sm:px-6 sm:py-14">
            <div class="mx-auto w-full max-w-md">
                @if(session('success'))
                    <div class="mb-5 rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] px-4 py-3 text-sm font-semibold text-[var(--color-success-700)]">
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
