<!DOCTYPE html>
<html
    lang="fa"
    dir="rtl"
>
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @hasSection('title')
            @yield('title') | فرزین
        @else
            ورود به فرزین
        @endif
    </title>

    <meta
        name="description"
        content="@yield('description', 'ورود و عضویت در سامانه آموزشی فرزین.')"
    >

    <link
        rel="icon"
        href="{{ asset('favicon.ico') }}"
    >

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    ])

    @stack('head')
</head>

<body class="min-h-screen bg-[var(--color-background)] text-[var(--color-text-primary)]">

<div class="relative flex min-h-screen flex-col">

    {{-- Minimal Brand Header --}}
    <header class="border-b border-[var(--color-border)] bg-[var(--color-surface)]">
        <div class="container-farzin">
            <div class="flex h-20 items-center justify-between">

                <a
                    href="{{ route('home') }}"
                    class="group flex items-center gap-3"
                    aria-label="بازگشت به صفحه اصلی فرزین"
                >
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-brand-600)] text-base font-black text-white transition-transform duration-200 group-hover:-translate-y-0.5"
                            >
                                ف
                            </span>

                    <span class="leading-tight">
                                <span class="block text-sm font-extrabold text-[var(--color-text-primary)]">
                                    فرزین
                                </span>

                                <span class="block text-xs text-[var(--color-text-muted)]">
                                    مرکز آموزش تخصصی
                                </span>
                            </span>
                </a>

                <a
                    href="{{ route('home') }}"
                    class="text-sm font-medium text-[var(--color-text-secondary)] transition-colors duration-200 hover:text-[var(--color-brand-600)]"
                >
                    بازگشت به سایت
                </a>
            </div>
        </div>
    </header>

    {{-- Main --}}
    <main class="flex flex-1 items-center py-10 sm:py-14">
        <div class="container-farzin w-full">
            <div class="mx-auto w-full max-w-md">
                @yield('content')
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-[var(--color-border)] bg-[var(--color-surface)]">
        <div class="container-farzin py-5">
            <p class="text-center text-xs text-[var(--color-text-muted)]">
                © تمامی حقوق برای فرزین محفوظ است.
            </p>
        </div>
    </footer>

</div>

{{-- Global Toast --}}
<div
    id="toast-container"
    class="pointer-events-none fixed inset-x-4 top-4 z-[var(--z-toast)] flex flex-col items-center gap-3 sm:inset-x-auto sm:right-4 sm:items-end"
    aria-live="polite"
    aria-atomic="true"
>
    @if(session('success'))
        <div class="pointer-events-auto">
            <x-feedback.toast
                type="success"
                :message="session('success')"
            />
        </div>
    @endif

    @if(session('error'))
        <div class="pointer-events-auto">
            <x-feedback.toast
                type="danger"
                :message="session('error')"
            />
        </div>
    @endif

    @if(session('warning'))
        <div class="pointer-events-auto">
            <x-feedback.toast
                type="warning"
                :message="session('warning')"
            />
        </div>
    @endif

    @if(session('info'))
        <div class="pointer-events-auto">
            <x-feedback.toast
                type="info"
                :message="session('info')"
            />
        </div>
    @endif
</div>

@stack('scripts')
</body>
</html>
