@props(['role'])

@php
    $user = auth()->user();

    $items = match ($role) {
        'student' => [
            ['route' => 'student.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'student.courses.index', 'label' => 'دوره‌ها', 'permission' => 'courses.view', 'icon' => '▣'],
            ['route' => 'student.assignments.index', 'label' => 'تکلیف', 'permission' => 'assignments.view', 'icon' => '✓'],
            ['route' => 'student.live-classes.index', 'label' => 'کلاس', 'permission' => 'live_classes.view', 'icon' => '◷'],
            ['route' => 'student.results.index', 'label' => 'عملکرد', 'permission' => 'results.view', 'icon' => '↗'],
            ['route' => 'student.messages.index', 'label' => 'پیام', 'permission' => 'student_messages.view', 'icon' => '✉'],
        ],
        'teacher' => [
            ['route' => 'teacher.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'teacher.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦'],
            ['route' => 'teacher.assignments.index', 'label' => 'تکلیف', 'permission' => 'assignments.view', 'icon' => '✓'],
            ['route' => 'teacher.live-classes.index', 'label' => 'کلاس آنلاین', 'permission' => 'live_classes.view', 'icon' => '◉'],
            ['route' => 'teacher.messages.index', 'label' => 'پیام‌های اولیا', 'permission' => 'parent_messages.view', 'icon' => '✉'],
            ['route' => 'teacher.student-messages.index', 'label' => 'پیام دانش‌آموز', 'permission' => 'student_messages.view', 'icon' => '✉'],
        ],
        'owner' => [
            ['route' => 'owner.dashboard', 'label' => 'مرکز', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'owner.people.index', 'label' => 'اعضا', 'permissions' => ['students.view', 'teachers.view'], 'icon' => '◎', 'requiresAcademy' => true],
            ['route' => 'owner.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦', 'requiresAcademy' => true],
            ['route' => 'owner.messages.index', 'label' => 'پیام‌ها', 'permission' => 'parents.view', 'icon' => '✉', 'requiresAcademy' => true],
            ['route' => 'owner.exams.index', 'label' => 'آزمون‌ها', 'permission' => 'exams.view', 'icon' => '▤', 'requiresAcademy' => true],
            ['route' => 'owner.reports.index', 'label' => 'گزارش', 'permission' => 'reports.view', 'icon' => '↗'],
        ],
        'parent' => [
            ['route' => 'parent.dashboard', 'url' => route('parent.dashboard'), 'activeRoute' => 'parent.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'parent.attendance.index', 'activeRoute' => 'parent.attendance.*', 'label' => 'حضور', 'permission' => 'dashboard.view', 'icon' => '✓'],
            ['route' => 'parent.results.index', 'activeRoute' => 'parent.results.*', 'label' => 'نتایج', 'permission' => 'dashboard.view', 'icon' => '↗'],
            ['route' => 'parent.resources.index', 'activeRoute' => 'parent.resources.*', 'label' => 'منابع', 'permission' => 'dashboard.view', 'icon' => '▤'],
            ['route' => 'parent.messages.index', 'activeRoute' => 'parent.messages.*', 'label' => 'پیام‌ها', 'permission' => 'dashboard.view', 'icon' => '✉'],
            ['route' => 'parent.counseling.index', 'activeRoute' => 'parent.counseling.*', 'label' => 'مشاوره', 'permission' => 'dashboard.view', 'icon' => '◷'],
        ],
        default => [],
    };

    if ($role === 'owner' && $user?->hasRole('seo-specialist') && ! $user?->hasRole('academy-owner')) {
        // Keep the specialist inside the shared owner shell, but point the home
        // shortcut only to the page they are authorized to use.
        $items[0] = ['route' => 'owner.seo.index', 'label' => 'SEO', 'permission' => 'seo.manage', 'icon' => '⌕'];
    }

    $academy = $role === 'owner'
        ? $user?->ownedAcademies()->where('status', 'active')->orderBy('name')->first()
        : null;

    $visibleItems = collect($items)
        ->filter(fn (array $item) => !$user || collect($item['permissions'] ?? [$item['permission'] ?? null])->filter()->contains(fn (string $permission) => $user->hasPermission($permission)))
        ->filter(fn (array $item) => empty($item['requiresAcademy']) || $academy)
        ->map(function (array $item) use ($academy) {
            if ($academy && in_array($item['route'], [
                'owner.people.index', 'owner.classrooms.index', 'owner.attendance.index',
                'owner.assignments.index', 'owner.schedules.index', 'owner.meetings.index',
                'owner.messages.index', 'owner.counseling.index', 'owner.exams.index',
                'owner.recordings.index', 'owner.teacher-payments.index',
            ], true)) {
                $item['urlArgs'] = [$academy];
            }

            return $item;
        })
        ->values();
@endphp

@if($visibleItems->isNotEmpty())
    <nav
        class="role-mobile-bottom-nav"
        style="--role-mobile-nav-items: {{ $visibleItems->count() }};"
        aria-label="{{ match ($role) {
            'student' => 'دسترسی سریع دانش‌آموز',
            'teacher' => 'دسترسی سریع مدرس',
            'owner' => ($user?->hasRole('seo-specialist') && ! $user?->hasRole('academy-owner'))
                ? 'دسترسی سریع متخصص SEO'
                : 'دسترسی سریع ادمین / منشی',
            default => 'دسترسی سریع',
        } }}"
    >
        @foreach($visibleItems as $item)
            @php
                $dashboardRoute = match ($role) {
                    'student' => 'student.dashboard',
                    'teacher' => 'teacher.dashboard',
                    'owner' => 'owner.dashboard',
                    default => '',
                };
                $active = array_key_exists('activeRoute', $item)
                    ? ($item['activeRoute'] !== null && request()->routeIs($item['activeRoute']))
                    : (
                        request()->routeIs($item['route'])
                        || (
                            $item['route'] !== $dashboardRoute
                            && str_ends_with($item['route'], '.index')
                            && request()->routeIs(str_replace('.index', '.*', $item['route']))
                        )
                    );
            @endphp
            <a
                href="{{ $item['url'] ?? route($item['route'], $item['urlArgs'] ?? []) }}"
                class="{{ $active ? 'is-active' : '' }}"
            >
                <span class="role-mobile-bottom-icon" aria-hidden="true">
                    @switch($item['icon'])
                        @case('⌂')
                            <svg viewBox="0 0 24 24"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
                            @break
                        @case('▣')
                        @case('▤')
                            <svg viewBox="0 0 24 24"><path d="M5 3h10l4 4v14H5zM15 3v5h4M8 12h8M8 16h8"/></svg>
                            @break
                        @case('✓')
                            <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>
                            @break
                        @case('◷')
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            @break
                        @case('↗')
                            <svg viewBox="0 0 24 24"><path d="M4 19V5M4 19h16M8 15v-4M13 15V7M18 15v-7M14 4h6v6"/></svg>
                            @break
                        @case('✉')
                            <svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                            @break
                        @case('▦')
                        @case('◎')
                            <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                            @break
                        @case('◉')
                            <svg viewBox="0 0 24 24"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="m16 10 5-3v10l-5-3z"/></svg>
                            @break
                        @case('⌕')
                            <svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg>
                            @break
                        @default
                            <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                    @endswitch
                </span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
@endif
