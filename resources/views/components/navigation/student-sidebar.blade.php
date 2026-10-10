<aside class="role-sidebar student-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('student.dashboard') }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span>
                <strong>شیخان</strong>
                <small>خانه یادگیری من</small>
            </span>
        </a>
    </div>

    @php($studentUser = auth()->user())

    <nav class="role-sidebar-nav" aria-label="منوی دانش‌آموز">
        <div class="role-sidebar-label">مرکز یادگیری</div>

        <a href="{{ route('student.dashboard') }}" class="role-nav-link {{ request()->routeIs('student.dashboard') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>خانه یادگیری</span>
        </a>

        <div class="role-sidebar-label mt-6">یادگیری</div>

        @if($studentUser->hasPermission('courses.view'))
            <a href="{{ route('student.courses.index') }}" class="role-nav-link {{ request()->routeIs('student.courses.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>دوره‌های من</span>
            </a>
        @endif

        @if($studentUser->hasPermission('live_classes.view'))
            <a href="{{ route('student.live-classes.index') }}" class="role-nav-link {{ request()->routeIs('student.live-classes.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>کلاس‌های من</span>
            </a>
        @endif

        @if($studentUser->hasPermission('assignments.view'))
            <a href="{{ route('student.assignments.index') }}" class="role-nav-link {{ request()->routeIs('student.assignments.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>تکالیف</span>
            </a>
        @endif

        @if($studentUser->hasPermission('exams.view'))
            <a href="{{ route('student.exams.index') }}" class="role-nav-link {{ request()->routeIs('student.exams.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>آزمون‌ها</span>
            </a>
        @endif

        @if($studentUser->hasPermission('resources.view'))
            <a href="{{ route('student.resources.index') }}" class="role-nav-link {{ request()->routeIs('student.resources.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>جزوه‌ها و منابع</span>
            </a>
        @endif

        @if(\Illuminate\Support\Facades\Route::has('library.index'))
            <a href="{{ route('library.index') }}" class="role-nav-link {{ request()->routeIs('library.index') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>کتابخانه و خریدهای من</span>
            </a>
        @endif

        <div class="role-sidebar-label mt-6">عملکرد و حساب</div>

        @if($studentUser->hasPermission('results.view'))
            <a href="{{ route('student.results.index') }}" class="role-nav-link {{ request()->routeIs('student.results.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>نمرات و عملکرد</span>
            </a>
        @endif

        @if($studentUser->hasPermission('attendance.view'))
            <a href="{{ route('student.attendance.index') }}" class="role-nav-link {{ request()->routeIs('student.attendance.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>حضور و غیاب</span>
            </a>
        @endif

        @if($studentUser->hasPermission('achievements.view'))
            <a href="{{ route('student.achievements.index') }}" class="role-nav-link {{ request()->routeIs('student.achievements.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>دستاوردها</span>
            </a>
        @endif

        @if($studentUser->hasPermission('notes.view'))
            <a href="{{ route('student.notes.index') }}" class="role-nav-link {{ request()->routeIs('student.notes.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>یادداشت‌ها</span>
            </a>
        @endif

        @if($studentUser->hasPermission('profile.view'))
            <a href="{{ route('student.profile.edit') }}" class="role-nav-link {{ request()->routeIs('student.profile.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>حساب کاربری</span>
            </a>
        @endif
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>