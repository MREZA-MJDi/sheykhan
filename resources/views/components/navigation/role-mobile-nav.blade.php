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
                <span class="role-mobile-bottom-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
@endif
