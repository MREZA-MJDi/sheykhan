<aside class="role-sidebar teacher-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('teacher.dashboard') }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span><strong>شیخان</strong><small>پنل استاد</small></span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="منوی استاد">
        <div class="role-sidebar-label">آموزش</div>

        <a href="{{ route('teacher.dashboard') }}" class="role-nav-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>داشبورد</span>
        </a>
        <a href="{{ route('teacher.courses.index') }}" class="role-nav-link {{ request()->routeIs('teacher.courses.*') || request()->routeIs('teacher.lessons.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>دوره‌های من</span>
        </a>
        <a href="{{ route('teacher.classrooms.index') }}" class="role-nav-link {{ request()->routeIs('teacher.classrooms.*') && !request()->routeIs('teacher.classrooms.attendance.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>کلاس‌های من</span>
        </a>
        <a href="{{ route('teacher.schedule.index') }}" class="role-nav-link {{ request()->routeIs('teacher.schedule.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>برنامه هفتگی</span>
        </a>
        <a href="{{ route('teacher.live-classes.index') }}" class="role-nav-link {{ request()->routeIs('teacher.live-classes.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>کلاس‌های آنلاین</span>
        </a>

        <div class="role-sidebar-label mt-6">ارزیابی</div>

        <a href="{{ route('teacher.assignments.index') }}" class="role-nav-link {{ request()->routeIs('teacher.assignments.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>تکالیف</span>
        </a>
        <a href="{{ route('teacher.exams.index') }}" class="role-nav-link {{ request()->routeIs('teacher.exams.*') || request()->routeIs('teacher.exam-attempts.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>آزمون‌ها</span>
        </a>

        <div class="role-sidebar-label mt-6">پیگیری</div>

        <a href="{{ route('teacher.attendance.index') }}" class="role-nav-link {{ request()->routeIs('teacher.attendance.*') || request()->routeIs('teacher.classrooms.attendance.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>حضور و غیاب</span>
        </a>
        <a href="{{ route('teacher.students.index') }}" class="role-nav-link {{ request()->routeIs('teacher.students.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>دانش‌آموزان</span>
        </a>
        <a href="{{ route('teacher.dashboard') }}#teacher-progress-title" class="role-nav-link">
            <span class="role-nav-icon"></span><span>گزارش عملکرد</span>
        </a>

        <div class="role-sidebar-label mt-6">حساب</div>

        <a href="{{ route('teacher.profile.edit') }}" class="role-nav-link {{ request()->routeIs('teacher.profile.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>پروفایل من</span>
        </a>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="role-footer-link role-logout">خروج</button></form>
    </div>
</aside>
