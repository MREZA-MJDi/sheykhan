<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="csrf-token" content="{{ csrf_token() }}"><x-seo.head />@vite(['resources/css/home.css','resources/js/home.js'])@stack('styles')</head>
<body class="sheykhan-site"><div id="app"><x-navigation.navbar /><main id="main-content" class="home-page"><x-ui.flash-messages />@yield('content')</main><x-navigation.footer /></div><div id="toast-container" aria-live="polite" aria-atomic="true"></div>@stack('scripts')</body></html>