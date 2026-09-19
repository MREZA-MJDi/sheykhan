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
            <x-navigation.panel-topbar title="@yield('header-title', 'پنل مدرس')" />

            <main class="min-w-0 p-4 sm:p-6 lg:p-8 panel-page-enter">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
