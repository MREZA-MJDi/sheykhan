@php
    $items = [
        ['route' => 'owner.dashboard', 'label' => 'داشبورد', 'icon' => 'grid'],
        ['route' => 'owner.courses.index', 'label' => 'دوره‌ها', 'icon' => 'book'],
    ];
@endphp

<aside class="role-sidebar owner-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ route('owner.dashboard') }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span>
                <strong>شیخان</strong>
                <small>مدیریت آموزشگاه</small>
            </span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="منوی مدیریت آموزشگاه">
        <div class="role-sidebar-label">مدیریت</div>
        @foreach($items as $item)
            <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
               class="role-nav-link {{ request()->routeIs($item['route']) ? 'is-active' : '' }} {{ Route::has($item['route']) ? '' : 'is-disabled' }}">
                <span class="role-nav-icon" aria-hidden="true"></span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        <div class="role-sidebar-label mt-6">نظارت</div>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>استادها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>دانش‌آموزان</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>کلاس‌ها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>گزارش‌ها</span><small>به‌زودی</small></span>
        <span class="role-nav-link is-disabled"><span class="role-nav-icon"></span><span>تنظیمات</span><small>به‌زودی</small></span>
    </nav>

    <div class="role-sidebar-footer">
        <a href="{{ route('home') }}" class="role-footer-link">مشاهده سایت</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="role-footer-link role-logout">خروج</button>
        </form>
    </div>
</aside>
