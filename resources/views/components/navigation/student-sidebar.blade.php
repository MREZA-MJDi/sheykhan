<aside class="role-sidebar student-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('student.dashboard') }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span>
                <strong>شیخان</strong>
                <small>پنل دانش‌آموز</small>
            </span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="منوی دانش‌آموز">
        <div class="role-sidebar-label">یادگیری</div>
        <a href="{{ route('student.dashboard') }}#student-courses" class="role-nav-link {{ request()->routeIs('student.dashboard') ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>دوره‌های من</span></a>
        <a href="{{ route('student.dashboard') }}#student-sessions" class="role-nav-link"><span class="role-nav-icon"></span><span>کلاس‌ها و جلسات</span></a>
        <a href="{{ route('student.dashboard') }}#student-assignments" class="role-nav-link"><span class="role-nav-icon"></span><span>تمرین‌ها</span></a>
        <a href="{{ route('student.dashboard') }}#student-results" class="role-nav-link"><span class="role-nav-icon"></span><span>آزمون‌ها و نتایج</span></a>
        <a href="{{ route('student.resources.index') }}" class="role-nav-link {{ request()->routeIs('student.resources.*') ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>جزوه‌ها و فایل‌ها</span></a>

        <div class="role-sidebar-label mt-6">حساب من</div>
        <a href="{{ route('student.dashboard') }}#student-results" class="role-nav-link"><span class="role-nav-icon"></span><span>نمرات و عملکرد</span></a>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>