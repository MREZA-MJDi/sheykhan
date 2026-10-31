<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل دانش‌آموز') · شیخان</title>
    @include('layouts.partials.panel-theme-init')
    @vite(['resources/css/student.css', 'resources/js/student.js'])
    <style>
      :root {
        --bg1:#b4c4d2; --bg2:#8ea4b8;
        --side:#2c4764; --side2:#223a54;
        --teal:#3f8e9c; --teal-d:#2f7483;
      }
      .hero { background:linear-gradient(135deg,#4a6580,#374f69); }
      .hero:after { background:linear-gradient(180deg,#5d7791,#3d566f); }
      .ib.pr img { display:block; }
    </style>
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
  document.querySelectorAll('[data-open-dialog]').forEach((button) => {
    button.addEventListener('click', () => document.getElementById(button.dataset.openDialog)?.showModal());
  });
})();
</script>
</body>
</html>
