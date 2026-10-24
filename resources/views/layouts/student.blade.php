<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل دانش‌آموز') · شیخان</title>
    <script>
        try {
            const savedStudentTheme = localStorage.getItem('sheykhan.panel.theme') || localStorage.getItem('sheykhan.student.theme') || localStorage.getItem('theme');
            if (savedStudentTheme === 'dark' || savedStudentTheme === 'light') {
                document.documentElement.dataset.theme = savedStudentTheme;
            }
        } catch (_) {}
    </script>
    @vite(['resources/css/app.css', 'resources/css/student.css', 'resources/css/panel-design-system.css', 'resources/js/app.js', 'resources/js/student.js'])
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
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <defs>
    <symbol id="avatar" viewBox="0 0 120 120"><ellipse cx="60" cy="118" rx="52" ry="34" fill="#1f2f55"/><path d="M38 96q22 14 44 0l6 20H32z" fill="#27396a"/><rect x="51" y="78" width="18" height="16" rx="7" fill="#e3b48f"/><circle cx="60" cy="54" r="26" fill="#f2cba8"/><path d="M32 50q-2-26 28-28t30 26q-6-14-30-14T32 50z" fill="#1f2a4a"/><path d="M31 52q-1-34 29-34t29 34" fill="none" stroke="#2a3a5e" stroke-width="5" stroke-linecap="round"/><rect x="22" y="44" width="11" height="22" rx="5.5" fill="#33496f"/><rect x="87" y="44" width="11" height="22" rx="5.5" fill="#33496f"/><path d="M46 56q5 4 10 0M64 56q5 4 10 0" fill="none" stroke="#3a2a2a" stroke-width="2.4" stroke-linecap="round"/><path d="M52 68q8 6 16 0" fill="none" stroke="#b0705a" stroke-width="2.4" stroke-linecap="round"/><ellipse cx="44" cy="63" rx="4" ry="2.4" fill="#eba48f" opacity=".6"/><ellipse cx="76" cy="63" rx="4" ry="2.4" fill="#eba48f" opacity=".6"/></symbol>
    <symbol id="i-home" viewBox="0 0 24 24"><path d="M3 11l9-8 9 8v9a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1zM9 21v-6h6v6"/></symbol>
    <symbol id="i-book" viewBox="0 0 24 24"><path d="M4 4h12a4 4 0 014 4v12H8a4 4 0 01-4-4zM8 4v16"/></symbol>
    <symbol id="i-video" viewBox="0 0 24 24"><path d="M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2zM15 10l6-3v10l-6-3z"/></symbol>
    <symbol id="i-task" viewBox="0 0 24 24"><path d="M6 4h12a1 1 0 011 1v15a1 1 0 01-1 1H6a1 1 0 01-1-1V5a1 1 0 011-1zM9 13l2 2 4-4"/></symbol>
    <symbol id="i-exam" viewBox="0 0 24 24"><path d="M6 3h12a1 1 0 011 1v16a1 1 0 01-1 1H6a1 1 0 01-1-1V4a1 1 0 011-1zM9 8h6M9 12h6M9 16h3"/></symbol>
    <symbol id="i-file" viewBox="0 0 24 24"><path d="M6 3h8l5 5v13H6zM14 3v5h5"/></symbol>
    <symbol id="i-cart" viewBox="0 0 24 24"><path d="M3 4h3l2 11h10l2-8H7M10 20h.01M17 20h.01"/></symbol>
    <symbol id="i-msg" viewBox="0 0 24 24"><path d="M4 5h16v11H9l-5 4z"/></symbol>
    <symbol id="i-chart" viewBox="0 0 24 24"><path d="M4 20V4M4 20h16M8 16v-5M13 16V8M18 16v-3"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><path d="M4 6h16v14H4zM4 10h16M8 3v4M16 3v4"/></symbol>
    <symbol id="i-trophy" viewBox="0 0 24 24"><path d="M8 4h8v5a4 4 0 01-8 0zM8 6H4v1a4 4 0 004 4M16 6h4v1a4 4 0 01-4 4M12 13v4M8 20h8"/></symbol>
    <symbol id="i-note" viewBox="0 0 24 24"><path d="M5 4h14v16H5zM9 9h6M9 13h6"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol>
    <symbol id="i-play" viewBox="0 0 24 24"><path d="M7 5l12 7-12 7z"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/></symbol>
  </defs>
</svg>
<div class="app">
 <aside class="side">
  <div class="me">
   <div class="av">
    @if($studentShellAvatarUrl)
      <img data-avatar-image src="{{ $studentShellAvatarUrl }}" alt="تصویر پروفایل {{ $studentShellUser?->name }}" width="104" height="104" loading="eager" decoding="async" fetchpriority="high">
      <svg data-avatar-fallback hidden viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
    @else
      <svg data-avatar-fallback viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#avatar"/></svg>
    @endif
   </div>
   <h3>سلام {{ auth()->user()->name }}!</h3>
  </div>
  <nav class="nav" aria-label="منوی دانش‌آموز">
   <a href="{{ route('student.dashboard') }}" class="{{ request()->routeIs('student.dashboard') ? 'on' : '' }}" @if(request()->routeIs('student.dashboard')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-home"/></svg>خانه یادگیری</a>
   <h6>یادگیری</h6>
   <a href="{{ route('student.courses.index') }}" class="{{ request()->routeIs('student.courses.*','student.lessons.*') ? 'on' : '' }}" @if(request()->routeIs('student.courses.*','student.lessons.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-book"/></svg>دوره‌های من</a>
   <a href="{{ route('student.live-classes.index') }}" class="{{ request()->routeIs('student.live-classes.*','student.schedule.join') ? 'on' : '' }}" @if(request()->routeIs('student.live-classes.*','student.schedule.join')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-video"/></svg>کلاس‌های من</a>
   <a href="{{ route('student.assignments.index') }}" class="{{ request()->routeIs('student.assignments.*') ? 'on' : '' }}" @if(request()->routeIs('student.assignments.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-task"/></svg>تکالیف</a>
   <a href="{{ route('student.exams.index') }}" class="{{ request()->routeIs('student.exams.*','student.exam-attempts.*') ? 'on' : '' }}" @if(request()->routeIs('student.exams.*','student.exam-attempts.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-exam"/></svg>آزمون‌ها</a>
   <a href="{{ route('student.resources.index') }}" class="{{ request()->routeIs('student.resources.*') ? 'on' : '' }}" @if(request()->routeIs('student.resources.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-file"/></svg>جزوه‌ها و منابع</a>
   <a href="{{ route('store.index') }}" class="{{ request()->routeIs('store.*') ? 'on' : '' }}" @if(request()->routeIs('store.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-book"/></svg>کتاب‌ها و محصولات</a>
   <a href="{{ route('library.index') }}"><svg class="i" aria-hidden="true"><use href="#i-cart"/></svg>کتابخانه و خریدها</a>
   <h6>ارتباط</h6>
   <a href="{{ route('student.messages.index') }}" class="{{ request()->routeIs('student.messages.*','student.teacher-messages.*') ? 'on' : '' }}" @if(request()->routeIs('student.messages.*','student.teacher-messages.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-msg"/></svg>پیام به مدرس‌ها</a>
   <h6>عملکرد و حساب</h6>
   <a href="{{ route('student.results.index') }}" class="{{ request()->routeIs('student.results.*') ? 'on' : '' }}" @if(request()->routeIs('student.results.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-chart"/></svg>نمرات و عملکرد</a>
   <a href="{{ route('student.attendance.index') }}" class="{{ request()->routeIs('student.attendance.*') ? 'on' : '' }}" @if(request()->routeIs('student.attendance.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-cal"/></svg>حضور و غیاب</a>
   <a href="{{ route('student.achievements.index') }}" class="{{ request()->routeIs('student.achievements.*') ? 'on' : '' }}" @if(request()->routeIs('student.achievements.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-trophy"/></svg>دستاوردها</a>
   <a href="{{ route('student.notes.index') }}" class="{{ request()->routeIs('student.notes.*') ? 'on' : '' }}" @if(request()->routeIs('student.notes.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-note"/></svg>یادداشت‌ها</a>
   <a href="{{ route('student.profile.edit') }}" class="{{ request()->routeIs('student.profile.*') ? 'on' : '' }}" @if(request()->routeIs('student.profile.*')) aria-current="page" @endif><svg class="i" aria-hidden="true"><use href="#i-user"/></svg>حساب کاربری</a>
  </nav>
  <div class="prem">
   <h4>مسیر یادگیری</h4>
   <p>درس‌ها، برنامه کلاس‌ها و تکالیف خودت را از همین‌جا دنبال کن.</p>
   <a class="btn s" href="{{ route('student.live-classes.index') }}">کلاس‌های من</a><br>
   <a class="lo" href="{{ route('home') }}">مشاهده سایت</a><br>
   <form method="POST" action="{{ route('logout') }}">@csrf<button class="lo" type="submit">خروج از حساب</button></form>
  </div>
 </aside>
 <main class="main" id="main">
  <header class="top">
   <h1>@yield('header-title', 'مرکز یادگیری')</h1>
   <form class="search" role="search" action="{{ route('student.courses.index') }}" method="GET">
    <svg class="i" aria-hidden="true"><use href="#i-search"/></svg>
    <input type="search" name="q" value="{{ request('q') }}" placeholder="جست‌وجوی دوره‌های من..." aria-label="جست‌وجوی دوره‌های من">
   </form>
   <button class="ib pr" type="button" data-student-profile-open aria-haspopup="dialog" aria-controls="student-profile-modal" aria-label="باز کردن تنظیمات حساب کاربری">
      @if($studentShellAvatarUrl)
        <img data-avatar-image src="{{ $studentShellAvatarUrl }}" alt="" width="46" height="46" loading="eager" decoding="async">
        <svg data-avatar-fallback hidden class="i" aria-hidden="true"><use href="#i-user"/></svg>
      @else
        <svg data-avatar-fallback class="i" aria-hidden="true"><use href="#i-user"/></svg>
      @endif
   </button>
  </header>
  @if(session('success') || session('status'))
   <div class="card flash" role="status">{{ session('success') ?? session('status') }}</div>
  @endif
  @if($errors->any())
   <div class="card flash err" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
  @endif
  @yield('content')
 </main>
</div>

<x-navigation.role-mobile-nav role="student" />

<div id="student-profile-modal" class="student-profile-modal" hidden>
    <button type="button" class="student-profile-backdrop" data-student-profile-close aria-label="بستن منوی حساب"></button>
    <section class="student-profile-dialog" role="dialog" aria-modal="true" aria-labelledby="student-profile-dialog-title" tabindex="-1">
        <header class="student-profile-dialog-head">
            <div class="student-profile-dialog-person">
                <span class="student-profile-dialog-avatar">
                    @if($studentShellAvatarUrl)
                        <img src="{{ $studentShellAvatarUrl }}" alt="" width="48" height="48" loading="eager">
                    @else
                        <svg class="i" aria-hidden="true"><use href="#i-user"/></svg>
                    @endif
                </span>
                <span>
                    <strong id="student-profile-dialog-title">{{ $studentShellUser?->name }}</strong>
                    <small>حساب دانش‌آموز</small>
                </span>
            </div>
            <button class="student-profile-dialog-close" type="button" data-student-profile-close aria-label="بستن پنجره">×</button>
        </header>
        <div class="student-profile-dialog-actions">
            <a href="{{ route('student.profile.edit') }}">
                <svg class="i" aria-hidden="true"><use href="#i-user"/></svg>
                <span><strong>پروفایل و اطلاعات حساب</strong><small>ویرایش اطلاعات شخصی و تصویر پروفایل</small></span>
                <span aria-hidden="true">←</span>
            </a>
            <button type="button" data-student-theme-toggle aria-pressed="false">
                <svg class="i" aria-hidden="true"><use href="#i-note"/></svg>
                <span><strong>حالت نمایش</strong><small data-student-theme-label>تغییر بین تم روشن و تیره</small></span>
                <span class="student-profile-theme-state" data-student-theme-state>روشن</span>
            </button>
            <a href="{{ route('home') }}">
                <svg class="i" aria-hidden="true"><use href="#i-home"/></svg>
                <span><strong>مشاهده سایت</strong><small>رفتن به صفحه عمومی شیخان</small></span>
                <span aria-hidden="true">↗</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="danger">
                    <svg class="i" aria-hidden="true"><use href="#i-user"/></svg>
                    <span><strong>خروج از حساب</strong><small>پایان نشست فعلی</small></span>
                    <span aria-hidden="true">←</span>
                </button>
            </form>
        </div>
    </section>
</div>

@stack('scripts')
</body>
</html>
