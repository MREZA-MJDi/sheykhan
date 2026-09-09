<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'پنل والدین | فرزین')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body class="min-h-screen bg-[var(--color-background)] text-[var(--color-text)]">

    <div class="flex min-h-screen">

        <x-navigation.sidebar title="پنل والدین">
            @yield('sidebar')
        </x-navigation.sidebar>

        <div class="min-w-0 flex-1">

            <header class="sticky top-0 z-[var(--z-sticky)] flex h-16 items-center justify-between border-b border-[var(--color-border)] bg-white/95 px-4 backdrop-blur sm:px-6">

                <div class="min-w-0">
                    <h1 class="truncate text-base font-bold sm:text-lg">
                        @yield('header-title', 'پنل والدین')
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    @yield('header-actions')
                </div>

            </header>

            <main class="min-w-0 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')

</body>
</html>
