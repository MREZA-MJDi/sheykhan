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
        <a href="{{ route('student.dashboard') }}" class="role-nav-link is-active"><span class="role-nav-icon"></span><span>داشبورد</span></a>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>دوره‌های من</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>کلاس‌ها و جلسات</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>تمرین‌ها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>آزمون‌ها</span><small>به‌زودی</small></span>

        <div class="role-sidebar-label mt-6">حساب من</div>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>نمرات و عملکرد</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>پروفایل</span><small>به‌زودی</small></span>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>
