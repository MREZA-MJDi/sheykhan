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
        ],
        'teacher' => [
            ['route' => 'teacher.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'teacher.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦'],
            ['route' => 'teacher.assignments.index', 'label' => 'تکلیف', 'permission' => 'assignments.view', 'icon' => '✓'],
            ['route' => 'teacher.exams.index', 'label' => 'آزمون', 'permission' => 'exams.view', 'icon' => '▤'],
            ['route' => 'teacher.attendance.index', 'label' => 'حضور', 'permission' => 'attendance.view', 'icon' => '◷'],
        ],
        'owner' => [
            ['route' => 'owner.dashboard', 'label' => 'مرکز', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'owner.people.index', 'label' => 'اعضا', 'permission' => 'students.view', 'icon' => '◎', 'requiresAcademy' => true],
            ['route' => 'owner.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦', 'requiresAcademy' => true],
            ['route' => 'owner.courses.index', 'label' => 'دوره‌ها', 'permission' => 'courses.view', 'icon' => '▣'],
            ['route' => 'owner.reports.index', 'label' => 'گزارش', 'permission' => 'reports.view', 'icon' => '↗'],
        ],
        default => [],
    };

    $academy = $role === 'owner'
        ? $user?->ownedAcademies()->where('status', 'active')->orderBy('name')->first()
        : null;

    $visibleItems = collect($items)
        ->filter(fn (array $item) => !$user || $user->hasPermission($item['permission']))
        ->filter(fn (array $item) => empty($item['requiresAcademy']) || $academy)
        ->map(function (array $item) use ($academy) {
            if ($academy && in_array($item['route'], ['owner.people.index', 'owner.classrooms.index'], true)) {
                $item['urlArgs'] = [$academy];
            }

            return $item;
        })
        ->values();
@endphp

@if($visibleItems->isNotEmpty())
    <nav
        class="role-mobile-bottom-nav"
        aria-label="{{ match ($role) {
            'student' => 'دسترسی سریع دانش‌آموز',
            'teacher' => 'دسترسی سریع مدرس',
            'owner' => 'دسترسی سریع مدیر آموزشگاه',
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
                $active = request()->routeIs($item['route'])
                    || (
                        $item['route'] !== $dashboardRoute
                        && str_ends_with($item['route'], '.index')
                        && request()->routeIs(str_replace('.index', '.*', $item['route']))
                    );
            @endphp
            <a
                href="{{ route($item['route'], $item['urlArgs'] ?? []) }}"
                class="{{ $active ? 'is-active' : '' }}"
            >
                <span class="role-mobile-bottom-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
@endif
