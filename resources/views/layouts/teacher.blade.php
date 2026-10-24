<!doctype html>
<html lang="fa" dir="rtl" data-theme="{{ request()->routeIs('teacher.dashboard') ? 'light' : 'dark' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'پنل مدرس | شیخان')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        try {
            var savedTeacherTheme = localStorage.getItem('theme');
            if (savedTeacherTheme === 'dark' || savedTeacherTheme === 'light') {
                document.documentElement.dataset.theme = savedTeacherTheme;
            }
        } catch (_) {}
    </script>
    @vite(['resources/css/app.css', 'resources/css/teacher.css', 'resources/js/app.js', 'resources/js/teacher.js'])
    @stack('styles')
</head>
<body class="teacher-shell">
    @php
        $teacherPanelUser = auth()->user();
        $teacherNavGroups = [
            ['label' => '', 'items' => [
                ['label' => 'داشبورد', 'icon' => 'home', 'url' => route('teacher.dashboard'), 'active' => 'teacher.dashboard', 'permission' => 'dashboard.view'],
                ['label' => 'دوره‌های من', 'icon' => 'book', 'url' => route('teacher.courses.index'), 'active' => 'teacher.courses.*', 'permission' => 'courses.view'],
                ['label' => 'کلاس‌های من', 'icon' => 'users', 'url' => route('teacher.classrooms.index'), 'active' => 'teacher.classrooms.*', 'permission' => 'classrooms.view'],
                ['label' => 'برنامه هفتگی', 'icon' => 'cal', 'url' => route('teacher.schedule.index'), 'active' => 'teacher.schedule.*', 'permission' => 'live_classes.view'],
                ['label' => 'کلاس‌های آنلاین', 'icon' => 'vid', 'url' => route('teacher.live-classes.index'), 'active' => 'teacher.live-classes.*', 'permission' => 'live_classes.view'],
            ]],
            ['label' => 'ارزیابی', 'items' => [
                ['label' => 'تکالیف', 'icon' => 'file', 'url' => route('teacher.assignments.index'), 'active' => 'teacher.assignments.*', 'permission' => 'assignments.view'],
                ['label' => 'آزمون‌ها', 'icon' => 'clip', 'url' => route('teacher.exams.index'), 'active' => 'teacher.exams.*', 'permission' => 'exams.view'],
                ['label' => 'حضور و غیاب', 'icon' => 'chk', 'url' => route('teacher.attendance.index'), 'active' => 'teacher.attendance.*', 'permission' => 'attendance.view'],
            ]],
            ['label' => 'پیگیری', 'items' => [
                ['label' => 'پیام‌های اولیا', 'icon' => 'mail', 'url' => route('teacher.messages.index'), 'active' => 'teacher.messages.*', 'permission' => 'parent_messages.view'],
                ['label' => 'پیام‌های دانش‌آموزان', 'icon' => 'mail', 'url' => route('teacher.student-messages.index'), 'active' => 'teacher.student-messages.*', 'permission' => 'student_messages.view'],
                ['label' => 'پیام مستقیم', 'icon' => 'mail', 'url' => route('teacher.direct-messages.index'), 'active' => 'teacher.direct-messages.*', 'permission' => 'parent_messages.manage'],
                ['label' => 'اطلاعیه‌های کلاس', 'icon' => 'bell', 'url' => route('teacher.communications.index'), 'active' => 'teacher.communications.*', 'permission' => 'parent_messages.manage'],
                ['label' => 'جلسات با اولیا', 'icon' => 'hs', 'url' => route('teacher.meetings.index'), 'active' => 'teacher.meetings.*', 'permission' => 'live_classes.view'],
                ['label' => 'دانش‌آموزان', 'icon' => 'users', 'url' => route('teacher.students.index'), 'active' => 'teacher.students.*', 'permission' => 'students.view'],
                ['label' => 'گزارش عملکرد', 'icon' => 'chart', 'url' => route('teacher.dashboard') . '#teacher-progress', 'active' => 'teacher.dashboard', 'permissions' => ['reports.view', 'dashboard.view']],
            ]],
            ['label' => 'حساب', 'items' => [
                ['label' => 'حقوق و پرداخت‌ها', 'icon' => 'wal', 'url' => route('teacher.payments.index'), 'active' => 'teacher.payments.*', 'permission' => 'teacher_payments.view'],
                ['label' => 'پروفایل من', 'icon' => 'usr', 'url' => route('teacher.profile.edit'), 'active' => 'teacher.profile.*', 'permission' => 'profile.view'],
            ]],
        ];
        foreach ($teacherNavGroups as &$teacherNavGroup) {
            $teacherNavGroup['items'] = array_values(array_filter(
                $teacherNavGroup['items'],
                fn ($item) => collect($item['permissions'] ?? [$item['permission']])
                    ->every(fn (string $permission) => $teacherPanelUser?->hasPermission($permission))
            ));
        }
        unset($teacherNavGroup);
        $teacherNavGroups = array_values(array_filter($teacherNavGroups, fn ($group) => $group['items'] !== []));
        $teacherPanelInitial = \Illuminate\Support\Str::substr(trim((string) ($teacherPanelUser?->name ?? 'م')), 0, 1) ?: 'م';
    @endphp

    @include('teacher.partials.icons')

    <div class="app" id="teacher-panel-app">
        <nav class="sb" id="nav" aria-label="منوی پنل مدرس">
            <a class="lg" href="{{ route('teacher.dashboard') }}" title="داشبورد شیخان" aria-label="داشبورد شیخان">
                <b>ش</b><span class="nav-label">شیخان</span>
            </a>
            @foreach($teacherNavGroups as $group)
                @if(filled($group['label']))
                    <div class="gl">{{ $group['label'] }}</div>
                @endif
                @foreach($group['items'] as $item)
                    <a class="nv {{ request()->routeIs($item['active']) && $item['label'] !== 'گزارش عملکرد' ? 'on' : '' }}"
                       href="{{ $item['url'] }}" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}"
                       @if(request()->routeIs($item['active']) && $item['label'] !== 'گزارش عملکرد') aria-current="page" @endif>
                        <svg class="i" aria-hidden="true"><use href="#{{ $item['icon'] }}"/></svg>
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            @endforeach
            <div class="nav-footer">
                <a class="nv" href="{{ route('home') }}" title="مشاهده سایت" aria-label="مشاهده سایت">
                    <svg class="i" aria-hidden="true"><use href="#out"/></svg><span class="nav-label">مشاهده سایت</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="nv" type="submit" title="خروج از حساب" aria-label="خروج از حساب">
                        <svg class="i" aria-hidden="true"><use href="#out"/></svg><span class="nav-label">خروج</span>
                    </button>
                </form>
            </div>
            <div class="us" title="{{ $teacherPanelUser?->name }}">
                <span class="av">{{ $teacherPanelInitial }}</span>
                <div><b>{{ $teacherPanelUser?->name }}</b><small>پنل مدرس</small></div>
            </div>
        </nav>

        <div class="mn">
            @include('teacher.partials.topbar')
            <main class="workspace-content role-main">
                <div class="flash-wrap"><x-ui.flash-messages /></div>
                @yield('content')
            </main>
        </div>
    </div>

    <x-navigation.role-mobile-nav role="teacher" />

    @include('teacher.partials.overlays')
    @stack('scripts')
</body>
</html>
