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
            فرزین | آموزش تخصصی دانش‌آموزان
        @endif
    </title>

    <meta
        name="description"
        content="@yield('description', 'فرزین؛ آموزش تخصصی برای دانش‌آموزان مستعد و داوطلبان آزمون تیزهوشان.')"
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

<body
    class="min-h-screen bg-[var(--color-background)] text-[var(--color-text-primary)]"
>
<div class="flex min-h-screen flex-col">

    {{-- Public Navigation --}}
    <header>
        <x-navigation.navbar />
    </header>

    {{-- Main --}}
    <main
        id="main-content"
        class="flex-1"
    >
        @yield('content')
    </main>

    {{-- Public Footer --}}
    <x-navigation.footer />

</div>

{{-- Mobile Navigation --}}
<x-navigation.mobile-nav />

{{-- Global Toast Area --}}
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
