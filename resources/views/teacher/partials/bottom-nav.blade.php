<nav class="tp-bottomnav" aria-label="میانبرهای مدرس">
    @foreach ([['teacher.dashboard','⌂','داشبورد'],['teacher.assignments.index','✎','تکالیف'],['teacher.exams.index','✓','آزمون‌ها'],['teacher.messages.index','✉','پیام‌ها']] as [$routeName, $icon, $label])
        @if (\Illuminate\Support\Facades\Route::has($routeName) && $tpUser?->hasPermission(match($routeName) { 'teacher.assignments.index' => 'assignments.view', 'teacher.exams.index' => 'exams.view', 'teacher.messages.index' => 'parent_messages.view', default => 'dashboard.view' }))
            <a href="{{ route($routeName) }}" class="{{ request()->routeIs(str_replace('.index', '.*', $routeName)) || request()->routeIs($routeName) ? 'is-on' : '' }}"><i>{{ $icon }}</i>{{ $label }}</a>
        @endif
    @endforeach
    <button type="button" data-tp-drawer-open><i>☰</i>منو</button>
</nav>