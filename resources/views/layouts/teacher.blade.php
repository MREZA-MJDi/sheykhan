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
      ['label'=>'نمای کلی','items'=>[
        ['label'=>'خانه','icon'=>'⌂','route'=>'teacher.dashboard','permission'=>'dashboard.view'],
        ['label'=>'برنامه هفتگی','icon'=>'◷','route'=>'teacher.schedule.index','permission'=>'live_classes.view'],
      ]],
      ['label'=>'تدریس و کلاس‌ها','items'=>[
        ['label'=>'کلاس‌های من','icon'=>'▤','route'=>'teacher.classrooms.index','permission'=>'classrooms.view'],
        ['label'=>'کلاس آنلاین','icon'=>'▹','route'=>'teacher.live-classes.index','permission'=>'live_classes.view'],
        ['label'=>'دوره‌ها و درس‌ها','icon'=>'▣','route'=>'teacher.courses.index','permission'=>'courses.view'],
        ['label'=>'SpotPlayer و لایسنس‌ها','icon'=>'▶','route'=>'teacher.spotplayer.index','permission'=>'courses.view'],
      ]],
      ['label'=>'ارزیابی و پیگیری','items'=>[
        ['label'=>'تکالیف','icon'=>'✎','route'=>'teacher.assignments.index','permission'=>'assignments.view'],
        ['label'=>'آزمون‌ها','icon'=>'✓','route'=>'teacher.exams.index','permission'=>'exams.view'],
        ['label'=>'حضور و غیاب','icon'=>'☷','route'=>'teacher.attendance.index','permission'=>'attendance.view'],
        ['label'=>'دانش‌آموزان','icon'=>'♙','route'=>'teacher.students.index','permission'=>'students.view'],
      ]],
      ['label'=>'ارتباط و گزارش','items'=>[
        ['label'=>'پیام‌ها','icon'=>'✉','route'=>$teacherUser?->hasPermission('parent_messages.manage') ? 'teacher.communications.index' : ($teacherUser?->hasPermission('parent_messages.view') ? 'teacher.messages.index' : 'teacher.student-messages.index'),'permissions_any'=>['parent_messages.view','parent_messages.manage','student_messages.view']],
        ['label'=>'گزارش عملکرد','icon'=>'▥','route'=>'teacher.reports.index','permission'=>'reports.view'],
        ['label'=>'جلسات اولیا','icon'=>'◉','route'=>'teacher.meetings.index','permission'=>'live_classes.view'],
        ['label'=>'درخواست مشاوره','icon'=>'✦','route'=>'teacher.counseling-requests.index','permission'=>'live_classes.view'],
      ]],
      ['label'=>'حساب کاربری','items'=>[
        ['label'=>'پرداخت‌ها','icon'=>'＄','route'=>'teacher.payments.index','permission'=>'teacher_payments.view'],
        ['label'=>'پروفایل من','icon'=>'⚙','route'=>'teacher.profile.edit','permission'=>'profile.view'],
      ]],
    ];
    $pageTitle = trim($__env->yieldContent('page_title')) ?: match (true) {
      request()->routeIs('teacher.dashboard') => 'داشبورد مدرس',
      request()->routeIs('teacher.reports.*') => 'گزارش عملکرد',
      request()->routeIs('teacher.schedule.*') => 'برنامه هفتگی',
      request()->routeIs('teacher.classrooms.attendance.*') || request()->routeIs('teacher.attendance.*') => 'حضور و غیاب',
      request()->routeIs('teacher.classrooms.*') => 'کلاس‌ها',
      request()->routeIs('teacher.courses.*') => 'دوره‌ها',
      request()->routeIs('teacher.spotplayer.*') => 'SpotPlayer و لایسنس‌ها',
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
              if (isset($item['permissions_any'])) {
                  return collect($item['permissions_any'])->contains(fn (string $permission) => $teacherUser?->hasPermission($permission));
              }
              $permissions = collect($item['permissions'] ?? [$item['permission']]);
              return $permissions->every(function (string $permission) use ($teacherUser) {
                  return $teacherUser?->hasPermission($permission);
              });
          }));
      @endphp
      @if(count($visibleItems))
        <div class="side-group-label" data-nav-category="{{ $loop->index }}">{{ $group['label'] }}</div>
        @foreach($visibleItems as $item)
          @php
              $active = false;
              if (!isset($item['anchor'])) {
                  if ($item['route'] === 'teacher.dashboard') {
                      $active = request()->routeIs('teacher.dashboard');
                  } else {
                      $prefix = str_contains($item['route'], '.index')
                          ? str_replace('.index', '.*', $item['route'])
                          : (str_contains($item['route'], '.edit')
                              ? str_replace('.edit', '.*', $item['route'])
                              : $item['route'] . '.*');

                      $active = request()->routeIs($prefix);

                      if ($item['route'] === 'teacher.attendance.index' && request()->routeIs('teacher.classrooms.attendance.*')) {
                          $active = true;
                      } elseif ($item['route'] === 'teacher.classrooms.index' && request()->routeIs('teacher.classrooms.attendance.*')) {
                          $active = false;
                      } elseif ($item['route'] === 'teacher.exams.index' && request()->routeIs('teacher.exam-attempts.*')) {
                          $active = true;
                      }
                  }
              }
          @endphp
          <a class="nv {{ $active ? 'on' : '' }}" data-nav-category="{{ $loop->parent->index }}" href="{{ route($item['route']) }}{{ $item['anchor'] ?? '' }}" @if($active) aria-current="page" @endif>
            <i aria-hidden="true">{{ $item['icon'] }}</i><span>{{ $item['label'] }}</span>
          </a>
        @endforeach
      @endif
    @endforeach
    <div class="side-footer">
      <a class="nv" href="{{ route('account.guide') }}"><i>؟</i><span>راهنمای کار با پنل</span></a>
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
    @if(request()->routeIs('teacher.messages.*') || request()->routeIs('teacher.direct-messages.*') || request()->routeIs('teacher.student-messages.*') || request()->routeIs('teacher.communications.*') || request()->routeIs('teacher.counseling-requests.*'))
      <nav class="teacher-context-nav" aria-label="بخش‌های پیام‌رسانی">
        @if($teacherUser?->hasPermission('parent_messages.manage'))
          <a href="{{ route('teacher.direct-messages.index') }}" class="{{ request()->routeIs('teacher.direct-messages.*') ? 'is-active' : '' }}">ارسال پیام</a>
        @endif
        @if($teacherUser?->hasPermission('parent_messages.view'))
          <a href="{{ route('teacher.messages.index') }}" class="{{ request()->routeIs('teacher.messages.*') ? 'is-active' : '' }}">پیام خانواده‌ها</a>
        @endif
        @if($teacherUser?->hasPermission('student_messages.view'))
          <a href="{{ route('teacher.student-messages.index') }}" class="{{ request()->routeIs('teacher.student-messages.*') ? 'is-active' : '' }}">پیام دانش‌آموزان</a>
        @endif
        @if($teacherUser?->hasPermission('parent_messages.manage'))
          <a href="{{ route('teacher.communications.index') }}" class="{{ request()->routeIs('teacher.communications.*') ? 'is-active' : '' }}">اطلاعیه‌های آموزشگاه</a>
        @endif
      
        @if($teacherUser?->hasPermission('live_classes.view'))
          <a href="{{ route('teacher.counseling-requests.index') }}" class="{{ request()->routeIs('teacher.counseling-requests.*') ? 'is-active' : '' }}">درخواست‌های مشاوره</a>
        @endif
</nav>
    @endif
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
    ['route'=>'teacher.classrooms.index','icon'=>'▤','label'=>'کلاس‌ها','permission'=>'classrooms.view'],
    ['route'=>'teacher.live-classes.index','icon'=>'◉','label'=>'آنلاین','permission'=>'live_classes.view'],
    ['route'=>$teacherUser?->hasPermission('parent_messages.manage') ? 'teacher.communications.index' : ($teacherUser?->hasPermission('parent_messages.view') ? 'teacher.messages.index' : 'teacher.student-messages.index'),'icon'=>'✉','label'=>'پیام‌ها','permissions_any'=>['parent_messages.view','parent_messages.manage','student_messages.view']]
  ] as $mobileItem)
    @php
      $mobileActive = match (true) {
        $mobileItem['route'] === 'teacher.dashboard' => request()->routeIs('teacher.dashboard'),
        $mobileItem['label'] === 'پیام‌ها' => request()->routeIs('teacher.messages.*', 'teacher.direct-messages.*', 'teacher.student-messages.*', 'teacher.communications.*', 'teacher.counseling-requests.*'),
        default => request()->routeIs(str_replace('.index', '.*', $mobileItem['route'])),
      };
      $mobileVisible = isset($mobileItem['permissions_any'])
        ? collect($mobileItem['permissions_any'])->contains(fn ($permission) => $teacherUser?->hasPermission($permission))
        : $teacherUser?->hasPermission($mobileItem['permission']);
    @endphp
    @if($mobileVisible)
      <a class="{{ $mobileActive ? 'on' : '' }}" href="{{ route($mobileItem['route']) }}" @if($mobileActive) aria-current="page" @endif><i aria-hidden="true">{{ $mobileItem['icon'] }}</i>{{ $mobileItem['label'] }}</a>
    @endif
  @endforeach
  <button type="button" data-teacher-menu-open><i aria-hidden="true">☰</i>منو</button>
</nav>
@stack('scripts')
</body>
</html>