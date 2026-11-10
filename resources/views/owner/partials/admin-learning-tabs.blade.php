@php
    $learningRoutePatterns = [
        'owner.courses.*',
        'owner.classrooms.*',
        'owner.attendance.*',
        'owner.assignments.*',
        'owner.exams.*',
        'owner.schedules.*',
        'owner.meetings.*',
        'owner.recordings.*',
        'owner.resources.*',
    ];
    $isLearningWorkspaceRoute = request()->routeIs(...$learningRoutePatterns);
    $currentLearningAcademy = $academies->firstWhere('id', $routeAcademyId) ?: $academies->first();
    $learningTabs = [
        ['route' => 'owner.courses.index', 'pattern' => 'owner.courses.*', 'permission' => 'courses.view', 'label' => 'دوره‌ها', 'academy_scoped' => false],
        ['route' => 'owner.classrooms.index', 'pattern' => 'owner.classrooms.*', 'permission' => 'classrooms.view', 'label' => 'کلاس‌ها و گروه‌ها', 'academy_scoped' => true],
        ['route' => 'owner.attendance.index', 'pattern' => 'owner.attendance.*', 'permission' => 'attendance.view', 'label' => 'حضور و غیاب', 'academy_scoped' => true],
        ['route' => 'owner.assignments.index', 'pattern' => 'owner.assignments.*', 'permission' => 'assignments.view', 'label' => 'تکالیف و تصحیح', 'academy_scoped' => true],
        ['route' => 'owner.exams.index', 'pattern' => 'owner.exams.*', 'permission' => 'exams.view', 'label' => 'آزمون‌ها و نتایج', 'academy_scoped' => true],
        ['route' => 'owner.schedules.index', 'pattern' => 'owner.schedules.*', 'permission' => 'live_classes.view', 'label' => 'برنامه کلاس آنلاین', 'academy_scoped' => true],
        ['route' => 'owner.meetings.index', 'pattern' => 'owner.meetings.*', 'permission' => 'live_classes.view', 'label' => 'هماهنگی جلسات', 'academy_scoped' => true],
        ['route' => 'owner.recordings.index', 'pattern' => 'owner.recordings.*', 'permission' => 'live_classes.view', 'label' => 'ضبط کلاس‌ها', 'academy_scoped' => true],
        ['route' => 'owner.resources.index', 'pattern' => 'owner.resources.*', 'permission' => 'resources.view', 'label' => 'منابع آموزشی', 'academy_scoped' => true],
    ];
    $learningTabs = collect($learningTabs)->filter(function (array $tab) use ($ownerUser, $currentLearningAcademy): bool {
        return $ownerUser?->hasPermission($tab['permission'])
            && (! $tab['academy_scoped'] || $currentLearningAcademy !== null);
    })->values();
@endphp

@if($isLearningWorkspaceRoute && $ownerUser?->hasRole('academy-owner') && $learningTabs->isNotEmpty())
    <nav class="admin-context-tabs" aria-label="بخش‌های کلاس‌ها و آموزش">
        <span class="admin-context-tabs__label">کلاس‌ها و آموزش</span>
        <div class="admin-context-tabs__list">
            @foreach($learningTabs as $tab)
                @php
                    $tabUrl = $tab['academy_scoped']
                        ? route($tab['route'], $currentLearningAcademy)
                        : route($tab['route']);
                    $isCurrentLearningTab = request()->routeIs($tab['pattern']);
                @endphp
                <a class="admin-context-tab"
                   href="{{ $tabUrl }}"
                   @if($isCurrentLearningTab) aria-current="page" @endif>
                    {{ $tab['label'] }}
                </a>
            @endforeach
        </div>
        @if($currentLearningAcademy)
            <span class="admin-context-tabs__scope">آموزشگاه فعال در این نما: {{ $currentLearningAcademy->name }}</span>
        @endif
    </nav>
@endif
