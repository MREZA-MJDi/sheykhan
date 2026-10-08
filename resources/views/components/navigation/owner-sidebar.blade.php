@php
    $user = auth()->user();
    $academies = $user->ownedAcademies()->where('status', 'active')->orderBy('name')->get();
    $routeAcademy = request()->route('academy');
    $routeAcademyId = $routeAcademy instanceof \App\Models\Academy ? $routeAcademy->getKey() : $routeAcademy;
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
        <div class="role-sidebar-label">مرکز کنترل</div>

        <a href="{{ route('owner.dashboard') }}"
           class="role-nav-link {{ request()->routeIs('owner.dashboard') ? 'is-active' : '' }}">
            <span class="role-nav-icon"></span><span>داشبورد</span>
        </a>

        @if($user->hasPermission('reports.view'))
            <a href="{{ route('owner.reports.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.reports.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>عملکرد و گزارش</span>
            </a>
        @endif

        <div class="role-sidebar-label mt-6">عملیات آموزشگاه</div>

        @forelse($academies as $academy)
            <div class="owner-sidebar-academy">
                <div class="owner-sidebar-academy-name" title="{{ $academy->name }}">
                    {{ $academy->name }}
                </div>

                <div class="owner-sidebar-academy-links">
                    @if($user->hasPermission('students.view') || $user->hasPermission('teachers.view'))
                        <a href="{{ route('owner.people.index', $academy) }}"
                           class="role-nav-link {{ request()->routeIs('owner.people.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                            <span class="role-nav-icon"></span><span>اعضا</span>
                        </a>
                    @endif

                    @if($user->hasPermission('classrooms.view'))
                        <a href="{{ route('owner.classrooms.index', $academy) }}"
                           class="role-nav-link {{ request()->routeIs('owner.classrooms.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                            <span class="role-nav-icon"></span><span>کلاس‌ها</span>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="owner-sidebar-empty">هنوز آموزشگاه فعالی ندارید.</div>
        @endforelse

        @if($user->hasPermission('courses.view'))
            <div class="role-sidebar-label mt-6">آموزش و دوره</div>

            <a href="{{ route('owner.courses.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.courses.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>دوره‌ها</span>
            </a>
        @endif

        @if($user->hasPermission('content.manage') || $user->hasPermission('blog.manage') || $user->hasPermission('seo.manage'))
            <div class="role-sidebar-label mt-6">سایت و محتوا</div>

            @if(Route::has('owner.website.index'))
                <a href="{{ route('owner.website.index') }}"
                   class="role-nav-link {{ request()->routeIs('owner.website.*') ? 'is-active' : '' }}">
                    <span class="role-nav-icon"></span><span>مرکز سایت</span>
                </a>
            @endif

            @if($user->hasPermission('content.manage'))
                <a href="{{ route('owner.content.index') }}"
                   class="role-nav-link {{ request()->routeIs('owner.content.*') ? 'is-active' : '' }}">
                    <span class="role-nav-icon"></span><span>محتوای آموزشگاه</span>
                </a>
            @endif

            @if($user->hasPermission('blog.manage'))
                <a href="{{ route('owner.blog.index') }}"
                   class="role-nav-link {{ request()->routeIs('owner.blog.*') ? 'is-active' : '' }}">
                    <span class="role-nav-icon"></span><span>مقالات</span>
                </a>
            @endif

            @if($user->hasPermission('seo.manage'))
                <a href="{{ route('owner.seo.index') }}"
                   class="role-nav-link {{ request()->routeIs('owner.seo.*') ? 'is-active' : '' }}">
                    <span class="role-nav-icon"></span><span>SEO و دیده‌شدن</span>
                </a>
            @endif

            @if($user->hasPermission('academy.view'))
                @if($academies->count() === 1)
                    @php($singleAcademy = $academies->first())
                    <a href="{{ route('owner.academy.banners.edit', $singleAcademy) }}"
                       class="role-nav-link {{ request()->routeIs('owner.academy.banners.*') ? 'is-active' : '' }}">
                        <span class="role-nav-icon"></span><span>بنرهای سایت</span>
                    </a>
                @endif
            @endif
        @endif

        @if($user->hasPermission('finance.view') && Route::has('owner.finance.index'))
            <div class="role-sidebar-label mt-6">مالی</div>

            <a href="{{ route('owner.finance.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.finance.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>مالی و پرداخت‌ها</span>
            </a>
        @endif

        @if($user->hasPermission('academy.view'))
            <div class="role-sidebar-label mt-6">تنظیمات</div>

            @forelse($academies as $academy)
                <a href="{{ route('owner.academy.edit', $academy) }}"
                   class="role-nav-link {{ request()->routeIs('owner.academy.edit') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                    <span class="role-nav-icon"></span><span>تنظیمات {{ $academy->name }}</span>
                </a>
            @empty
            @endforelse
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