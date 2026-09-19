<aside class="role-sidebar parent-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('parent.dashboard') }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span>
                <strong>شیخان</strong>
                <small>پنل والد</small>
            </span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="منوی والد">
        <div class="role-sidebar-label">پیگیری فرزند</div>
        <a href="{{ route('parent.dashboard') }}" class="role-nav-link is-active"><span class="role-nav-icon"></span><span>داشبورد</span></a>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>فرزندان من</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>دوره‌ها و کلاس‌ها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>تمرین و آزمون</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>حضور و غیاب</span><small>به‌زودی</small></span>

        <div class="role-sidebar-label mt-6">گزارش</div>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>عملکرد تحصیلی</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>تقویم آموزشی</span><small>به‌زودی</small></span>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>
