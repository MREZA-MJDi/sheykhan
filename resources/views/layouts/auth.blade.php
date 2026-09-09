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
        @yield('title', 'ورود | فرزین')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body class="min-h-screen bg-[var(--color-background)] text-[var(--color-text)]">

    <div class="min-h-screen">

        <header class="border-b border-[var(--color-border)] bg-white">
            <x-layout.container>
                <div class="flex h-16 items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-[var(--radius-lg)] bg-[var(--color-primary-600)] font-black text-white">
                            ف
                        </span>

                        <span class="font-black">
                            فرزین
                        </span>
                    </a>
                </div>
            </x-layout.container>
        </header>

        <main class="flex min-h-[calc(100vh-4rem)] items-center justify-center px-4 py-10">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </main>

    </div>

    @stack('scripts')

</body>
</html>
