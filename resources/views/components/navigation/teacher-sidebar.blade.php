<aside class="role-sidebar teacher-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('teacher.dashboard') }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span>
                <strong>شیخان</strong>
                <small>پنل استاد</small>
            </span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="منوی استاد">
        <div class="role-sidebar-label">آموزش</div>
        <a href="{{ route('teacher.dashboard') }}" class="role-nav-link {{ request()->routeIs('teacher.dashboard') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>داشبورد</span>
        </a>
        <a href="{{ route('teacher.courses.index') }}" class="role-nav-link {{ request()->routeIs('teacher.courses.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>دوره‌های من</span>
        </a>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>کلاس‌ها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>برنامه هفتگی</span><small>به‌زودی</small></span>

        <div class="role-sidebar-label mt-6">ارزیابی</div>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>تکالیف</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>آزمون‌ها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>حضور و غیاب</span><small>به‌زودی</small></span>

        <div class="role-sidebar-label mt-6">پیگیری</div>
        <a href="{{ route('teacher.courses.index') }}" class="role-nav-link">
            <span class="role-nav-icon"></span><span>پیشرفت دانش‌آموزان</span>
        </a>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>کلاس آنلاین</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>گزارش عملکرد</span><small>به‌زودی</small></span>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>
