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
        <a href="{{ route('parent.attendance.index') }}" class="role-nav-link {{ request()->routeIs('parent.attendance.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>حضور و غیاب</span>
        </a>
        <a href="{{ route('parent.results.index') }}" class="role-nav-link {{ request()->routeIs('parent.results.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>نتایج تکالیف و آزمون‌ها</span>
        </a>
        <a href="{{ route('parent.resources.index') }}" class="role-nav-link {{ request()->routeIs('parent.resources.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>جزوه‌ها و منابع فرزندان</span>
        </a>
        <a href="{{ route('parent.messages.index') }}" class="role-nav-link {{ request()->routeIs('parent.messages.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>پیام‌ها و درخواست‌ها</span>
        </a>
        <a href="{{ route('parent.meetings.index') }}" class="role-nav-link {{ request()->routeIs('parent.meetings.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>جلسات برنامه‌ریزی‌شده</span>
        </a>
        <a href="{{ route('parent.counseling.index') }}" class="role-nav-link {{ request()->routeIs('parent.counseling.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>درخواست مشاوره</span>
        </a>

        <div class="role-sidebar-label mt-6">دسترسی‌ها</div>
        @if(\Illuminate\Support\Facades\Route::has('library.index'))
            <a href="{{ route('library.index') }}" class="role-nav-link {{ request()->routeIs('library.index') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>کتابخانه و خریدها</span>
            </a>
        @endif
        <a href="{{ route('store.index') }}" class="role-nav-link {{ request()->routeIs('store.*') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>کتاب‌ها و محصولات منتشرشده</span>
        </a>
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
