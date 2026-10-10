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
        <div class="role-sidebar-label">خانواده</div>
        <a href="{{ route('parent.dashboard') }}" class="role-nav-link {{ request()->routeIs('parent.dashboard') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>داشبورد خانواده</span>
        </a>
        <a href="{{ route('parent.dashboard') }}#parent-children-title" class="role-nav-link">
            <span class="role-nav-icon"></span><span>وضعیت فرزندان</span>
        </a>
        <a href="{{ route('parent.dashboard') }}#parent-live-title" class="role-nav-link">
            <span class="role-nav-icon"></span><span>جلسات پیش‌رو</span>
        </a>
        <a href="{{ route('parent.dashboard') }}#parent-results-title" class="role-nav-link">
            <span class="role-nav-icon"></span><span>نتیجه‌های اخیر</span>
        </a>

        <div class="role-sidebar-label mt-6">دسترسی‌ها</div>
        @if(\Illuminate\Support\Facades\Route::has('library.index'))
            <a href="{{ route('library.index') }}" class="role-nav-link {{ request()->routeIs('library.index') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>کتابخانه و خریدها</span>
            </a>
        @endif
        <a href="{{ route('courses.index') }}" class="role-nav-link">
            <span class="role-nav-icon"></span><span>کشف دوره‌ها</span>
        </a>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>
