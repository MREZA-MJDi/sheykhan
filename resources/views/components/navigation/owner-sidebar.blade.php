@php
    $user = auth()->user();
    $seoOnly = $user->hasRole('seo-specialist') && ! $user->hasRole('academy-owner');
    $academyScopedPermissions = [
        'academy.view', 'classrooms.view', 'attendance.view', 'assignments.view', 'courses.view',
        'live_classes.view', 'parents.view', 'exams.view', 'teacher_payments.view',
        'resources.view', 'products.view', 'orders.view',
    ];
    $canUseAcademyScope = ! $seoOnly && collect($academyScopedPermissions)
        ->contains(fn (string $permission) => $user->hasPermission($permission));
    $academies = $canUseAcademyScope
        ? $user->ownedAcademies()->where('status', 'active')->orderBy('name')->get()
        : collect();
    $firstAcademy = $academies->first();
    $websiteAcademies = $seoOnly
        ? \App\Models\Academy::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'slug'])
        : collect();

    $ownerPanelHomeUrl = route('home');
    if ($seoOnly) {
        if ($user->hasPermission('seo.manage')) {
            $ownerPanelHomeUrl = route('owner.seo.index');
        }
    } elseif ($user->hasPermission('dashboard.view')) {
        $ownerPanelHomeUrl = route('owner.dashboard');
    } elseif ($user->hasPermission('academy.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.people.index', $firstAcademy);
    } elseif ($user->hasPermission('classrooms.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.classrooms.index', $firstAcademy);
    } elseif ($user->hasPermission('attendance.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.attendance.index', $firstAcademy);
    } elseif ($user->hasPermission('courses.view')) {
        $ownerPanelHomeUrl = route('owner.courses.index');
    } elseif ($user->hasPermission('live_classes.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.meetings.index', $firstAcademy);
    } elseif ($user->hasPermission('parents.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.messages.index', $firstAcademy);
    } elseif ($user->hasPermission('exams.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.exams.index', $firstAcademy);
    } elseif ($user->hasPermission('teacher_payments.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.teacher-payments.index', $firstAcademy);
    } elseif ($user->hasPermission('resources.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.resources.index', $firstAcademy);
    } elseif ($user->hasPermission('products.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.products.index', $firstAcademy);
    } elseif ($user->hasPermission('orders.view') && $firstAcademy) {
        $ownerPanelHomeUrl = route('owner.orders.index', $firstAcademy);
    } elseif ($user->hasPermission('finance.view')) {
        $ownerPanelHomeUrl = route('owner.finance.index');
    } elseif ($user->hasPermission('legal.manage')) {
        $ownerPanelHomeUrl = route('owner.legal-documents.index');
    } elseif ($user->hasPermission('reports.view')) {
        $ownerPanelHomeUrl = route('owner.reports.index');
    }

    $routeAcademy = request()->route('academy');
    $routeAcademyId = $routeAcademy instanceof \App\Models\Academy ? $routeAcademy->getKey() : $routeAcademy;
@endphp

<aside class="role-sidebar owner-sidebar">
    <div class="role-sidebar-brand">
        <a href="{{ $ownerPanelHomeUrl }}" class="role-brand">
            <span class="role-brand-mark">ش</span>
            <span>
                <strong>شیخان</strong>
                <small>{{ $seoOnly ? 'پنل متخصص SEO' : 'کارتابل ادمین / منشی' }}</small>
            </span>
        </a>
    </div>

    <nav class="role-sidebar-nav" aria-label="{{ $seoOnly ? 'منوی متخصص SEO' : 'منوی کارتابل ادمین / منشی' }}">
        @if(! $seoOnly && $user->hasPermission('dashboard.view'))
            <div class="role-sidebar-label">مرکز کنترل</div>

            <a href="{{ route('owner.dashboard') }}"
               class="role-nav-link {{ request()->routeIs('owner.dashboard') ? 'is-active' : '' }}"
               @if(request()->routeIs('owner.dashboard')) aria-current="page" @endif>
                <span class="role-nav-icon"></span><span>داشبورد</span>
            </a>
        @endif

        @if($seoOnly && $user->hasPermission('seo.manage'))
            <div class="role-sidebar-label mt-6">بهینه‌سازی سایت</div>
            <a href="{{ route('owner.seo.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.seo.index', 'owner.seo.edit', 'owner.seo.update') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>ممیزی SEO صفحات</span>
            </a>
            <a href="{{ route('owner.seo.keywords.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.seo.keywords.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>کلمات کلیدی و رتبه</span>
            </a>
            <div class="role-sidebar-label mt-6">کنترل سایت عمومی</div>
            <a href="{{ route('owner.website.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.website.index', 'owner.website.hero.*', 'owner.academy.banners.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>مرکز سایت و بنرها</span>
            </a>
            <a href="{{ route('owner.website.hero.edit') }}"
               class="role-nav-link {{ request()->routeIs('owner.website.hero.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>مدیریت Hero صفحه اصلی</span>
            </a>
            <a href="{{ route('owner.content.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.content.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>محتوای عمومی صفحات</span>
            </a>
            <a href="{{ route('owner.blog.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.blog.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>بلاگ و مقالات</span>
            </a>
            @foreach($websiteAcademies as $websiteAcademy)
                <a href="{{ route('owner.showcase.index', $websiteAcademy) }}"
                   class="role-nav-link {{ request()->routeIs('owner.showcase.*') && (int) $routeAcademyId === (int) $websiteAcademy->id ? 'is-active' : '' }}">
                    <span class="role-nav-icon"></span><span>افتخارآفرینان و رضایتمندی · {{ $websiteAcademy->name }}</span>
                </a>
            @endforeach
        @endif

        @if(! $seoOnly && $user->hasPermission('reports.view'))
            <a href="{{ route('owner.reports.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.reports.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>عملکرد و گزارش</span>
            </a>
        @endif

        @if($canUseAcademyScope)
        <div class="role-sidebar-label mt-6">عملیات آموزشگاه</div>

        @forelse($academies as $academy)
            <div class="owner-sidebar-academy">
                <div class="owner-sidebar-academy-name" title="{{ $academy->name }}">
                    {{ $academy->name }}
                </div>

                <div class="owner-sidebar-academy-links">
                    @if($user->hasPermission('academy.view'))
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

                    @if($user->hasPermission('attendance.view'))
                        <a href="{{ route('owner.attendance.index', $academy) }}"
                           class="role-nav-link {{ request()->routeIs('owner.attendance.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                            <span class="role-nav-icon"></span><span>حضور و غیاب</span>
                        </a>
                    @endif

                    @if($user->hasPermission('assignments.view'))
                        <a href="{{ route('owner.assignments.index', $academy) }}"
                           class="role-nav-link {{ request()->routeIs('owner.assignments.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                            <span class="role-nav-icon"></span><span>تکالیف و تصحیح</span>
                        </a>
                    @endif

                    @if($user->hasPermission('live_classes.view'))
                        <a href="{{ route('owner.schedules.index', $academy) }}"
                           class="role-nav-link {{ request()->routeIs('owner.schedules.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                            <span class="role-nav-icon"></span><span>لینک کلاس آنلاین</span>
                        </a>
                    @endif

                    @if($user->hasPermission('live_classes.view'))
                        <a href="{{ route('owner.meetings.index', $academy) }}"
                           class="role-nav-link {{ request()->routeIs('owner.meetings.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}">
                            <span class="role-nav-icon"></span><span>هماهنگی جلسات</span>
                        </a>
                    @endif
                    @if($user->hasPermission('parents.view'))
                        <a href="{{ route('owner.messages.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.messages.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>پیام‌های اولیا</span></a>
                        <a href="{{ route('owner.communications.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.communications.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>ارسال اطلاعیه و پیام</span></a>
                        <a href="{{ route('owner.counseling.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.counseling.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>جلسات مشاوره</span></a>
                    @endif
                    @if($user->hasPermission('exams.view'))
                        <a href="{{ route('owner.exams.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.exams.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>آزمون‌ها و نتایج</span></a>
                    @endif
                    @if($user->hasPermission('live_classes.view'))
                        <a href="{{ route('owner.recordings.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.recordings.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>آرشیو ضبط کلاس‌ها</span></a>
                    @endif
                    @if($user->hasPermission('teacher_payments.view'))
                        <a href="{{ route('owner.teacher-payments.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.teacher-payments.*') && (int)$routeAcademyId === (int)$academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>حقوق مدرس‌ها</span></a>
                    @endif
                    @if($user->hasPermission('resources.view'))
                        <a href="{{ route('owner.resources.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.resources.*') && (int) $routeAcademyId === (int) $academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>ویدئو و منابع آموزشی</span></a>
                    @endif
                    @if($user->hasPermission('products.view'))
                        <a href="{{ route('owner.products.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.products.*') && (int) $routeAcademyId === (int) $academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>محصولات و منابع فروش</span></a>
                    @endif
                    @if($user->hasPermission('orders.view'))
                        <a href="{{ route('owner.orders.index', $academy) }}" class="role-nav-link {{ request()->routeIs('owner.orders.*') && (int) $routeAcademyId === (int) $academy->id ? 'is-active' : '' }}"><span class="role-nav-icon"></span><span>سفارش‌ها و رسیدها</span></a>
                    @endif
                </div>
            </div>
        @empty
            <div class="owner-sidebar-empty">هنوز آموزشگاه فعالی ندارید.</div>
        @endforelse
        @endif

        @if($user->hasPermission('courses.view'))
            <div class="role-sidebar-label mt-6">آموزش و دوره</div>

            <a href="{{ route('owner.courses.index') }}"
               class="role-nav-link {{ request()->routeIs('owner.courses.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>دوره‌ها</span>
            </a>
        @endif

        @if($user->hasPermission('legal.manage') && Route::has('owner.legal-documents.index'))
            <div class="role-sidebar-label mt-6">اسناد و حقوقی</div>
            <a href="{{ route('owner.legal-documents.index') }}" class="role-nav-link {{ request()->routeIs('owner.legal-documents.*') ? 'is-active' : '' }}">
                <span class="role-nav-icon"></span><span>اسناد حقوقی و کپی‌رایت</span>
            </a>
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