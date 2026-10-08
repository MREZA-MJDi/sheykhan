@props(['role'])

@php
    $items = $role === 'student'
        ? [
            ['route' => 'student.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'student.courses.index', 'label' => 'دوره‌ها', 'permission' => 'courses.view', 'icon' => '▣'],
            ['route' => 'student.assignments.index', 'label' => 'تکلیف', 'permission' => 'assignments.view', 'icon' => '✓'],
            ['route' => 'student.live-classes.index', 'label' => 'کلاس', 'permission' => 'live_classes.view', 'icon' => '◷'],
            ['route' => 'student.results.index', 'label' => 'عملکرد', 'permission' => 'results.view', 'icon' => '↗'],
        ]
        : [
            ['route' => 'teacher.dashboard', 'label' => 'خانه', 'permission' => 'dashboard.view', 'icon' => '⌂'],
            ['route' => 'teacher.classrooms.index', 'label' => 'کلاس‌ها', 'permission' => 'classrooms.view', 'icon' => '▦'],
            ['route' => 'teacher.assignments.index', 'label' => 'تکلیف', 'permission' => 'assignments.view', 'icon' => '✓'],
            ['route' => 'teacher.exams.index', 'label' => 'آزمون', 'permission' => 'exams.view', 'icon' => '▤'],
            ['route' => 'teacher.attendance.index', 'label' => 'حضور', 'permission' => 'attendance.view', 'icon' => '◷'],
        ];

    $visibleItems = array_values(array_filter(
        $items,
        fn (array $item) => auth()->user()?->hasPermission($item['permission'])
    ));
@endphp

@if($visibleItems)
    <nav class="role-mobile-bottom-nav" aria-label="{{ $role === 'student' ? 'دسترسی سریع دانش‌آموز' : 'دسترسی سریع مدرس' }}">
        @foreach($visibleItems as $item)
            @php($active = request()->routeIs($item['route']) || ($item['route'] !== ($role === 'student' ? 'student.dashboard' : 'teacher.dashboard') && request()->routeIs(str_replace('.index', '.*', $item['route']))))
            <a href="{{ route($item['route']) }}" class="{{ $active ? 'is-active' : '' }}">
                <span class="role-mobile-bottom-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
@endif
