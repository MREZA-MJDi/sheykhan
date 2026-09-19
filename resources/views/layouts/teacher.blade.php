<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'پنل مدرس | شیخان')</title>

    @vite([
        'resources/css/academy-teacher.css',
        'resources/js/academy-teacher.js',
    ])

    @stack('styles')
</head>

<body class="panel-shell">
    <div class="flex min-h-screen">
        <x-navigation.sidebar title="پنل مدرس">
            @yield('sidebar')
        </x-navigation.sidebar>

        <div class="min-w-0 flex-1">
            <header class="panel-topbar sticky top-0 z-[var(--z-sticky)] flex h-16 items-center justify-between border-b px-4 sm:px-6">
                <div class="min-w-0">
                    <h1 class="truncate text-base font-bold text-[var(--color-text)] sm:text-lg">
                        @yield('header-title', 'پنل مدرس')
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    @yield('header-actions')
                </div>
            </header>

            <main class="min-w-0 p-4 sm:p-6 lg:p-8 panel-page-enter">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
