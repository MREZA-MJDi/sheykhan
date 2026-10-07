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
        @yield('title', 'شیخان | آموزش، رشد، آینده')
    </title>

    <meta
        name="description"
        content="@yield(
            'description',
            'شیخان؛ پلتفرم یکپارچه آموزش آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت.'
        )"
    >

    @vite([
    'resources/css/home.css',
    'resources/js/home.js',
    ])

    @stack('styles')
</head>

<body class="sheykhan-site">

<div id="app">

    {{-- Global Navigation --}}
    <x-navigation.navbar />

    {{-- Public Home / Pages --}}
    <main
        id="main-content"
        class="home-page"
    >
        @yield('content')
    </main>

    {{-- Global Footer --}}
    <x-navigation.footer />

</div>

<div
    id="toast-container"
    aria-live="polite"
    aria-atomic="true"
></div>

@stack('scripts')

</body>
</html>
