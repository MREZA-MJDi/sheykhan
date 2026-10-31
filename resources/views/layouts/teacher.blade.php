<!doctype html>
<html lang="fa" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدرس | شیخان')</title>
    @vite(['resources/css/teacher.css', 'resources/js/teacher.js'])
    @stack('styles')
</head>
<body class="teacher-panel">
@php
    $teacherUser = auth()->user();
    $teacherUser?->loadMissing([
        'teacherProfile.media' => function ($query) {
            $query->wherePivot('collection', 'teacher-avatar')
                ->orderByPivot('sort_order');
        },
    ]);
    $avatarMedia = $teacherUser?->teacherProfile?->media?->first();
    $avatarUrl = $avatarMedia?->visibility === 'public' ? $avatarMedia->url() : ($avatarMedia ? route('media.view', $avatarMedia) : null);
    $initial = \Illuminate\Support\Str::substr(trim((string) ($teacherUser?->name ?? 'م')), 0, 1) ?: 'م';
    $navGroups = [
      ['label'=>'اصلی','items'=>[
        ['label'=>'داشبورد','icon'=>'⌂','route'=>'teacher.dashboard','permission'=>'dashboard.view'],
        ['label'=>'برنامه هفتگی','icon'=>'◷','route'=>'teacher.schedule.index','permission'=>'live_classes.view'],
        ['label'=>'کلاس‌ها','icon'=>'▤','route'=>'teacher.classrooms.index','permission'=>'classrooms.view'],
        ['label'=>'دوره‌ها','icon'=>'▣','route'=>'teacher.courses.index','permission'=>'courses.view'],
      ]],
      ['label'=>'آموزش و ارزیابی','items'=>[
        ['label'=>'تکالیف','icon'=>'✎','route'=>'teacher.assignments.index','permission'=>'assignments.view'],
        ['label'=>'آزمون‌ها','icon'=>'✓','route'=>'teacher.exams.index','permission'=>'exams.view'],
        ['label'=>'حضور و غیاب','icon'=>'☰','route'=>'teacher.attendance.index','permission'=>'attendance.view'],
        ['label'=>'دانش‌آموزان','icon'=>'♥','route'=>'teacher.students.index','permission'=>'students.view'],
      ]],
      ['label'=>'ارتباط و پیگیری','items'=>[
        ['label'=>'پیام‌های اولیا','icon'=>'✉','route'=>'teacher.messages.index','permission'=>'parent_messages.view'],
        ['label'=>'پیام‌های دانش‌آموزان','icon'=>'✉','route'=>'teacher.student-messages.index','permission'=>'student_messages.view'],
        ['label'=>'پیام مستقیم','icon'=>'✉','route'=>'teacher.direct-messages.index','permission'=>'parent_messages.manage'],
        ['label'=>'اطلاعیه‌ها','icon'=>'⚑','route'=>'teacher.communications.index','permission'=>'parent_messages.manage'],
        ['label'=>'جلسات اولیا','icon'=>'◉','route'=>'teacher.meetings.index','permission'=>'live_classes.view'],
        ['label'=>'درخواست مشاوره','icon'=>'✦','route'=>'teacher.counseling-requests.index','permission'=>'live_classes.view'],
        ['label'=>'کلاس آنلاین','icon'=>'▹','route'=>'teacher.live-classes.index','permission'=>'live_classes.view'],
      ]],
      ['label'=>'حساب','items'=>[
        ['label'=>'گزارش عملکرد','icon'=>'≣','route'=>'teacher.dashboard','permissions'=>['reports.view','dashboard.view'],'anchor'=>'#teacher-progress'],
        ['label'=>'پرداخت‌ها','icon'=>'＄','route'=>'teacher.payments.index','permission'=>'teacher_payments.view'],
        ['label'=>'تنظیمات پروفایل','icon'=>'⚙','route'=>'teacher.profile.edit','permission'=>'profile.view'],
      ]],
    ];
    $pageTitle = trim($__env->yieldContent('page_title')) ?: match (true) {
      request()->routeIs('teacher.dashboard') => 'داشبورد مدرس',
      request()->routeIs('teacher.schedule.*') => 'برنامه هفتگی',
      request()->routeIs('teacher.classrooms.attendance.*') || request()->routeIs('teacher.attendance.*') => 'حضور و غیاب',
      request()->routeIs('teacher.classrooms.*') => 'کلاس‌ها',
      request()->routeIs('teacher.courses.*') => 'دوره‌ها',
      request()->routeIs('teacher.assignments.*') => 'تکالیف',
      request()->routeIs('teacher.exams.*') || request()->routeIs('teacher.exam-attempts.*') => 'آزمون‌ها',
      request()->routeIs('teacher.students.*') => 'دانش‌آموزان',
      request()->routeIs('teacher.messages.*') || request()->routeIs('teacher.direct-messages.*') || request()->routeIs('teacher.student-messages.*') => 'پیام‌ها',
      request()->routeIs('teacher.profile.*') => 'تنظیمات پروفایل',
      request()->routeIs('teacher.live-classes.*') => 'کلاس‌های آنلاین',
      request()->routeIs('teacher.meetings.*') => 'جلسات اولیا',
      request()->routeIs('teacher.counseling-requests.*') => 'درخواست مشاوره',
      request()->routeIs('teacher.communications.*') => 'اطلاعیه‌ها',
      request()->routeIs('teacher.payments.*') => 'پرداخت‌ها',
      default => 'پنل مدرس'
    };
@endphp
<div class="shell">
  <aside class="side" id="side" aria-label="منوی پنل مدرس">
    <a class="logo" href="{{ route('teacher.dashboard') }}"><i>◆</i><span>آکادمی شیخان</span></a>
    @if($teacherUser?->hasPermission('profile.view'))
      <a class="teacher-profile-mini" href="{{ route('teacher.profile.edit') }}">
        <span class="av">@if($avatarUrl)<img src="{{ $avatarUrl }}" alt="" width="36" height="36">@else{{ $initial }}@endif</span>
        <span style="min-width:0"><b>{{ $teacherUser?->name }}</b><small>پنل مدرس</small></span>
      </a>
    @else
      <div class="teacher-profile-mini">
        <span class="av">@if($avatarUrl)<img src="{{ $avatarUrl }}" alt="" width="36" height="36">@else{{ $initial }}@endif</span>
        <span style="min-width:0"><b>{{ $teacherUser?->name }}</b><small>پنل مدرس</small></span>
      </div>
    @endif
    @foreach($navGroups as $group)
      @php
          $visibleItems = array_values(array_filter($group['items'], function ($item) use ($teacherUser) {
              $permissions = collect($item['permissions'] ?? [$item['permission']]);
              return $permissions->every(function (string $permission) use ($teacherUser) {
                  return $teacherUser?->hasPermission($permission);
              });
          }));
      @endphp
      @if(count($visibleItems))
        <div class="side-group-label">{{ $group['label'] }}</div>
        @foreach($visibleItems as $item)
          @php
              $activeRoutePattern = $item['route'] === 'teacher.dashboard'
                  ? $item['route']
                  : $item['route'] . '.*';
              $active = request()->routeIs($activeRoutePattern) && !isset($item['anchor']);
          @endphp
          <a class="nv {{ $active ? 'on' : '' }}" href="{{ route($item['route']) }}{{ $item['anchor'] ?? '' }}" @if($active) aria-current="page" @endif>
            <i aria-hidden="true">{{ $item['icon'] }}</i><span>{{ $item['label'] }}</span>
          </a>
        @endforeach
      @endif
    @endforeach
    <div class="side-footer">
      <a class="nv" href="{{ route('home') }}"><i>↗</i><span>مشاهده سایت</span></a>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="nv" type="submit"><i>⇥</i><span>خروج از حساب</span></button></form>
    </div>
  </aside>
  <button class="ov" id="teacher-overlay" type="button" data-teacher-menu-close aria-label="بستن منو" aria-hidden="true"></button>
  <main class="main workspace-content">
    <header class="top">
      <button class="burger" type="button" data-teacher-menu-open aria-label="بازکردن منو">☰</button>
      <div><h1>@yield('page_title', $pageTitle)</h1><small>@yield('page_subtitle', 'مدیریت کلاس‌ها، آموزش و ارتباط با دانش‌آموزان')</small></div>
      <span class="sp"></span>
      <form class="teacher-top-search" role="search" onsubmit="return false" style="max-width:250px;width:100%">
        <input class="in" id="gs" type="search" data-teacher-search placeholder="جست‌وجو در این صفحه…" aria-label="جست‌وجو در صفحه">
      </form>
      <button class="ib" id="th" type="button" data-teacher-theme aria-label="تغییر تم" aria-pressed="false" title="تغییر تم">◐</button>
      @if($teacherUser?->hasPermission('profile.view'))
        <a class="av" href="{{ route('teacher.profile.edit') }}" aria-label="پروفایل مدرس">@if($avatarUrl)<img src="{{ $avatarUrl }}" alt="" width="36" height="36">@else{{ $initial }}@endif</a>
      @else
        <span class="av" aria-label="حساب مدرس">@if($avatarUrl)<img src="{{ $avatarUrl }}" alt="" width="36" height="36">@else{{ $initial }}@endif</span>
      @endif
    </header>
    <p class="dashboard-search-empty" data-dashboard-search-empty role="status" aria-live="polite" hidden>موردی با این عبارت پیدا نشد. عبارت دیگری را امتحان کن.</p>
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert warning" role="status">{{ session('warning') }}</div>@endif
    @if($errors->any())
      <div class="validation-errors" role="alert"><strong>لطفاً موارد زیر را اصلاح کنید:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
  </main>
</div>
<nav class="bt" aria-label="میانبرهای پنل">
  @foreach([
    ['route'=>'teacher.dashboard','icon'=>'⌂','label'=>'خانه','permission'=>'dashboard.view'],
    ['route'=>'teacher.assignments.index','icon'=>'✎','label'=>'تکالیف','permission'=>'assignments.view'],
    ['route'=>'teacher.exams.index','icon'=>'✓','label'=>'آزمون','permission'=>'exams.view'],
    ['route'=>'teacher.messages.index','icon'=>'✉','label'=>'پیام','permission'=>'parent_messages.view']
  ] as $mobileItem)
    @if($teacherUser?->hasPermission($mobileItem['permission']))
      <a class="{{ request()->routeIs($mobileItem['route']) ? 'on' : '' }}" href="{{ route($mobileItem['route']) }}"><i>{{ $mobileItem['icon'] }}</i>{{ $mobileItem['label'] }}</a>
    @endif
  @endforeach
  <button type="button" data-teacher-menu-open><i>☰</i>منو</button>
</nav>
@stack('scripts')
</body>
</html>