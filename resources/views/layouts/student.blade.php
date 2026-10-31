<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل دانش‌آموز') · شیخان</title>
    @include('layouts.partials.panel-theme-init')
    @vite(['resources/css/student.css', 'resources/js/student.js'])
    @stack('head')
    @stack('styles')
</head>
<body class="student-shell">
@php
    $studentShellUser = auth()->user();
    $studentShellUser?->loadMissing([
        'studentProfile.media' => fn ($query) => $query
            ->wherePivot('collection', 'student-avatar')
            ->orderByPivot('sort_order'),
    ]);
    $studentShellAvatar = $studentShellUser?->studentProfile?->media?->first();
    $studentShellAvatarUrl = $studentShellAvatar?->visibility === 'public'
        ? $studentShellAvatar->url()
        : ($studentShellAvatar ? route('media.view', $studentShellAvatar) : null);
@endphp

@include('student.partials.sprites')
<a class="skip" href="#content">رفتن به محتوا</a>
<div class="app">
    @include('student.partials.sidebar')
    <main class="main" id="content">
        @include('student.partials.topbar')
        @if (session('success') || session('status'))
            <div class="alert g" role="status">{{ session('success') ?? session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert r" role="alert">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</div>
@include('student.partials.tabbar')
@stack('dialogs')
@stack('scripts')
<script>
(() => {
  'use strict';
  document.querySelectorAll('[data-filter]').forEach((group) => {
    const key = group.dataset.filter;
    const items = [...document.querySelectorAll('[data-filter-item][data-' + key + ']')];
    const empty = document.querySelector('[data-filter-empty="' + key + '"]');
    group.addEventListener('click', (event) => {
      const button = event.target.closest('[data-value]');
      if (!button || !group.contains(button)) return;
      group.querySelectorAll('[data-value]').forEach((item) => {
        const active = item === button;
        item.classList.toggle('on', active);
        item.setAttribute('aria-pressed', String(active));
      });
      let visible = 0;
      items.forEach((item) => {
        const show = button.dataset.value === 'all' || item.dataset[key] === button.dataset.value;
        item.hidden = !show;
        if (show) visible++;
      });
      if (empty) empty.hidden = visible > 0;
    });
  });
})();
</script>
</body>
</html>
