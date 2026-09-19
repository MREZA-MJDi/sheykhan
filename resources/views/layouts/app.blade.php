<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'شیخان | آموزش، رشد، آینده')</title>
    <meta name="description" content="@yield('description', 'شیخان؛ پلتفرم یکپارچه آموزش آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت.')">

    @vite([
        'resources/css/home.css',
        'resources/js/home.js',
    ])

    @stack('styles')
</head>
<body class="sheykhan-site">
    <div id="app" class="min-h-screen">
        <x-navigation.navbar />

        <main id="main-content" class="min-h-[calc(100vh-4rem)] home-noise">
            @yield('content')
        </main>

        <x-navigation.footer />
    </div>

    <div id="toast-container"></div>

    @stack('scripts')
</body>
</html>
