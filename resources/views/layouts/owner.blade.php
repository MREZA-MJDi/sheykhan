<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'کارتابل مدیر آموزشگاه | شیخان')</title>
    @vite(['resources/css/owner-dashboard-v2.css', 'resources/js/owner-dashboard.js'])
    @stack('styles')
</head>
<body class="owner-shell owner-admin-v2">
@php
    $ownerUser = auth()->user();
    $isSeoOnlyUser = $ownerUser && $ownerUser->hasRole('seo-specialist') && ! $ownerUser->hasRole('academy-owner');
    $canManagePublicSite = $ownerUser && $ownerUser->hasRole('seo-specialist') && $ownerUser->hasPermission('seo.manage');
    // DashboardController passes the already-cached academy collection.
    // Other owner pages keep a safe fallback when they do not provide it.
    $academies = $ownerUser && $ownerUser->hasRole('academy-owner')
        ? (isset($academies)
            ? collect($academies)
            : $ownerUser->ownedAcademies()->where('status', 'active')->orderBy('name')->get(['id','name','slug','owner_id']))
        : collect();
    $websiteAcademies = $canManagePublicSite
        ? ($isSeoOnlyUser
            ? \App\Models\Academy::query()->where('status', 'active')->orderBy('name')->get(['id','name','slug'])
            : $academies)
        : collect();
    $ownerHomeRoute = $isSeoOnlyUser ? 'owner.seo.index' : 'owner.dashboard';
    $routeAcademy = request()->route('academy');
    $routeAcademyId = $routeAcademy instanceof \App\Models\Academy ? (int) $routeAcademy->getKey() : (int) ($routeAcademy ?? 0);
    $pageHeading = trim($__env->yieldContent('header-title')) ?: 'کارتابل ادمین / منشی';
    $period = in_array($period ?? request()->query('period', 'month'), ['today','week','month'], true) ? ($period ?? request()->query('period','month')) : 'month';
    $notifications = collect($notifications ?? []);
    $ownerInitial = mb_substr(trim((string) ($ownerUser?->name ?? 'ش')), 0, 1);
    $hasNavPermission = static function ($permission) use ($ownerUser): bool {
        foreach ((array) $permission as $requiredPermission) {
            if ($ownerUser?->hasPermission($requiredPermission)) {
                return true;
            }
        }

        return false;
    };
    $nextClass = collect(isset($upcomingLiveClasses) ? $upcomingLiveClasses : [])->first();
    $nextClassTarget = $nextClass?->scheduled_at?->timestamp;
    // Keep the menu task-based: related daily operations live together while
    // each destination retains its original route, permission and academy scope.
    $ownerNavGroups = [
        [
            'key' => 'control',
            'title' => 'خانه و گزارش‌ها',
            'color' => '#22d3ee',
            'links' => [
                ['route' => 'owner.dashboard', 'permission' => 'dashboard.view', 'label' => 'داشبورد اصلی', 'icon' => 'home', 'scope' => 'global'],
                ['route' => 'owner.reports.index', 'permission' => 'reports.view', 'label' => 'گزارش عملکرد', 'icon' => 'chart', 'scope' => 'global'],
            ],
        ],
        [
            'key' => 'people',
            'title' => 'افراد و ارتباطات',
            'color' => '#ff1f6b',
            'links' => [
                ['route' => 'owner.people.index', 'permission' => 'academy.view', 'label' => 'دانش‌آموزان، مدرس‌ها و اولیا', 'icon' => 'users', 'scope' => 'academy'],
                ['route' => 'owner.attendance.index', 'permission' => 'attendance.view', 'label' => 'حضور و غیاب', 'icon' => 'checksq', 'scope' => 'academy'],
                ['route' => 'owner.communication-workspace', 'permission' => ['parents.view', 'live_classes.view'], 'label' => 'ارتباطات، پیام‌ها و جلسات', 'icon' => 'chat', 'scope' => 'academy'],
            ],
        ],
        [
            'key' => 'learning',
            'title' => 'کلاس‌ها و آموزش',
            'color' => '#8b3dff',
            'links' => [
                ['route' => 'owner.courses.index', 'permission' => 'courses.view', 'label' => 'دوره‌ها', 'icon' => 'book', 'scope' => 'global'],
                ['route' => 'owner.classrooms.index', 'permission' => 'classrooms.view', 'label' => 'کلاس‌ها و گروه‌ها', 'icon' => 'calendar', 'scope' => 'academy'],
                ['route' => 'owner.assignments.index', 'permission' => 'assignments.view', 'label' => 'تکالیف و تصحیح', 'icon' => 'doc', 'scope' => 'academy'],
                ['route' => 'owner.exams.index', 'permission' => 'exams.view', 'label' => 'آزمون‌ها و نتایج', 'icon' => 'book', 'scope' => 'academy'],
                ['route' => 'owner.schedules.index', 'permission' => 'live_classes.view', 'label' => 'برنامه و لینک کلاس آنلاین', 'icon' => 'link', 'scope' => 'academy'],
                ['route' => 'owner.recordings.index', 'permission' => 'live_classes.view', 'label' => 'ضبط کلاس‌ها', 'icon' => 'video', 'scope' => 'academy'],
                ['route' => 'owner.resources.index', 'permission' => 'resources.view', 'label' => 'منابع آموزشی', 'icon' => 'book', 'scope' => 'academy'],
            ],
        ],
        [
            'key' => 'finance',
            'title' => 'فروش و امور مالی',
            'color' => '#2ee6a6',
            'links' => [
                ['route' => 'owner.finance.index', 'permission' => 'finance.view', 'label' => 'مالی و پرداخت‌ها', 'icon' => 'wallet', 'scope' => 'global'],
                ['route' => 'owner.teacher-payments.index', 'permission' => 'teacher_payments.view', 'label' => 'پرداخت مدرس‌ها', 'icon' => 'wallet', 'scope' => 'academy'],
                ['route' => 'owner.products.index', 'permission' => 'products.view', 'label' => 'محصولات و منابع فروش', 'icon' => 'tag', 'scope' => 'academy'],
                ['route' => 'owner.orders.index', 'permission' => 'orders.view', 'label' => 'سفارش‌ها و رسیدها', 'icon' => 'receipt', 'scope' => 'academy'],
                ['route' => 'owner.protected-file-views.index', 'permission' => 'orders.view', 'label' => 'گزارش مشاهده فایل‌ها', 'icon' => 'receipt', 'scope' => 'academy'],
            ],
        ],
        [
            'key' => 'more',
            'title' => 'بیشتر',
            'color' => '#9aa3d8',
            'links' => [
                ['route' => 'owner.legal-documents.index', 'permission' => 'legal.manage', 'label' => 'اسناد حقوقی و کپی‌رایت', 'icon' => 'scale', 'scope' => 'global'],
                ['route' => 'owner.content-rights.index', 'permission' => 'legal.manage', 'label' => 'حقوق نشر و مجوز محتوا', 'icon' => 'scale', 'scope' => 'global'],
                ['route' => 'owner.academy.edit', 'permission' => 'academy.view', 'label' => 'تنظیمات آموزشگاه', 'icon' => 'cog', 'scope' => 'academy'],
                ['route' => 'owner.spotplayer.edit', 'permission' => 'courses.manage', 'label' => 'SpotPlayer و لایسنس‌ها', 'icon' => 'video', 'scope' => 'global'],
            ],
        ],
    ];

    $currentOwnerNavGroupKey = null;
    foreach ($ownerNavGroups as $candidateGroup) {
        if (
            $candidateGroup['key'] === 'more'
            && request()->routeIs(
                'owner.seo.*',
                'owner.website.*',
                'owner.content.*',
                'owner.blog.*',
                'owner.academy.banners.*',
                'owner.showcase.*'
            )
        ) {
            $currentOwnerNavGroupKey = 'more';
            break;
        }

        foreach ($candidateGroup['links'] as $candidateLink) {
            $candidatePattern = str_contains($candidateLink['route'], '.index')
                ? str_replace('.index', '.*', $candidateLink['route'])
                : $candidateLink['route'];

            if (request()->routeIs($candidatePattern)) {
                $currentOwnerNavGroupKey = $candidateGroup['key'];
                break 2;
            }
        }
    }
    $currentOwnerNavGroupKey ??= 'control';
@endphp
<div class="app">
    @include('owner.partials.dashboard-icons')
    @include('owner.partials.admin-topbar')
    <aside class="side card" id="side" aria-label="منوی مدیریت">
        <nav id="nv" aria-label="منوی اصلی">
            <?php foreach ($ownerNavGroups as $navGroup) { ?>
                <?php
                    $visibleGlobalLinks = collect($navGroup['links'])
                        ->filter(fn ($link) => $link['scope'] === 'global' && $hasNavPermission($link['permission']));
                    $visibleAcademyLinks = collect($navGroup['links'])
                        ->filter(fn ($link) => $link['scope'] === 'academy' && $ownerUser?->hasPermission($link['permission']));
                    $showSiteGroup = $navGroup['key'] === 'more' && $canManagePublicSite;
                    $showNavGroup = (
                        ! $isSeoOnlyUser
                        && (
                            $visibleGlobalLinks->isNotEmpty()
                            || ($academies->isNotEmpty() && $visibleAcademyLinks->isNotEmpty())
                        )
                    ) || $showSiteGroup;
                    $isNavGroupExpanded = $currentOwnerNavGroupKey === $navGroup['key'];
                ?>

                <?php if ($showNavGroup) { ?>
                    <div class="nav-group">
                        <button class="gh" type="button" data-group-toggle
                            aria-expanded="{{ $isNavGroupExpanded ? 'true' : 'false' }}"
                            style="--c:{{ $navGroup['color'] }}">
                            <i aria-hidden="true"></i>
                            <span>{{ $navGroup['title'] }}</span>
                            <em>{{ $isNavGroupExpanded ? '−' : '+' }}</em>
                        </button>

                        <div class="nav-group-items" <?php if (! $isNavGroupExpanded) echo 'hidden'; ?>>
                            <?php if (! $isSeoOnlyUser) { ?>
                                <?php foreach ($navGroup['links'] as $navLink) { ?>
                                    <?php if ($navLink['scope'] === 'global' && $hasNavPermission($navLink['permission'])) { ?>
                                        <?php
                                            $navRoutePattern = str_contains($navLink['route'], '.index')
                                                ? str_replace('.index', '.*', $navLink['route'])
                                                : $navLink['route'];
                                        ?>
                                        <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                           href="{{ route($navLink['route']) }}"
                                           <?php if (request()->routeIs($navRoutePattern)) echo 'aria-current="page"'; ?>
                                           data-nav-label="{{ $navLink['label'] }}">
                                            <svg class="ic" aria-hidden="true"><use href="#i-{{ $navLink['icon'] }}"/></svg>
                                            <span>{{ $navLink['label'] }}</span>
                                        </a>
                                    <?php } elseif ($navLink['scope'] === 'academy' && $ownerUser?->hasPermission($navLink['permission'])) { ?>
                                        <?php foreach ($academies as $academy) { ?>
                                            <?php
                                                $navRoutePattern = str_contains($navLink['route'], '.index')
                                                    ? str_replace('.index', '.*', $navLink['route'])
                                                    : $navLink['route'];
                                                $academyNavLabel = $academies->count() > 1
                                                    ? $navLink['label'] . ' · ' . $academy->name
                                                    : $navLink['label'];
                                            ?>
                                            <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                               href="{{ route($navLink['route'], $academy) }}"
                                               <?php if (request()->routeIs($navRoutePattern) && $routeAcademyId === (int) $academy->id) echo 'aria-current="page"'; ?>
                                               data-nav-label="{{ $academyNavLabel }}">
                                                <svg class="ic" aria-hidden="true"><use href="#i-{{ $navLink['icon'] }}"/></svg>
                                                <span>{{ $academyNavLabel }}</span>
                                            </a>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>

                            <?php if ($showSiteGroup) { ?>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                   href="{{ route('owner.seo.index') }}"
                                   <?php if (request()->routeIs('owner.seo.index','owner.seo.edit','owner.seo.update')) echo 'aria-current="page"'; ?>
                                   data-nav-label="ممیزی SEO">
                                    <svg class="ic" aria-hidden="true"><use href="#i-chart"/></svg><span>ممیزی SEO</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                   href="{{ route('owner.seo.keywords.index') }}"
                                   <?php if (request()->routeIs('owner.seo.keywords.*')) echo 'aria-current="page"'; ?>
                                   data-nav-label="کلمات کلیدی">
                                    <svg class="ic" aria-hidden="true"><use href="#i-search"/></svg><span>کلمات کلیدی</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                   href="{{ route('owner.website.index') }}"
                                   <?php if (request()->routeIs('owner.website.index')) echo 'aria-current="page"'; ?>
                                   data-nav-label="مرکز سایت">
                                    <svg class="ic" aria-hidden="true"><use href="#i-globe"/></svg><span>مرکز سایت و بنرها</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                   href="{{ route('owner.website.hero.edit') }}"
                                   <?php if (request()->routeIs('owner.website.hero.*')) echo 'aria-current="page"'; ?>
                                   data-nav-label="اسلایدر صفحه اصلی">
                                    <svg class="ic" aria-hidden="true"><use href="#i-mega"/></svg><span>اسلایدر صفحه اصلی</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                   href="{{ route('owner.content.index') }}"
                                   <?php if (request()->routeIs('owner.content.*')) echo 'aria-current="page"'; ?>
                                   data-nav-label="محتوای عمومی">
                                    <svg class="ic" aria-hidden="true"><use href="#i-news"/></svg><span>محتوای عمومی</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                   href="{{ route('owner.blog.index') }}"
                                   <?php if (request()->routeIs('owner.blog.*')) echo 'aria-current="page"'; ?>
                                   data-nav-label="بلاگ و مقالات">
                                    <svg class="ic" aria-hidden="true"><use href="#i-news"/></svg><span>بلاگ و مقالات</span>
                                </a>
                                <?php foreach ($websiteAcademies as $websiteAcademy) { ?>
                                    <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                       href="{{ route('owner.academy.banners.edit', $websiteAcademy) }}"
                                       <?php if (request()->routeIs('owner.academy.banners.*') && $routeAcademyId === (int) $websiteAcademy->id) echo 'aria-current="page"'; ?>
                                       data-nav-label="بنرهای {{ $websiteAcademy->name }}">
                                        <svg class="ic" aria-hidden="true"><use href="#i-image"/></svg>
                                        <span>بنرهای {{ $websiteAcademy->name }}</span>
                                    </a>
                                    <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                       href="{{ route('owner.showcase.index', $websiteAcademy) }}"
                                       <?php if (request()->routeIs('owner.showcase.*') && $routeAcademyId === (int) $websiteAcademy->id) echo 'aria-current="page"'; ?>
                                       data-nav-label="افتخارآفرینان {{ $websiteAcademy->name }}">
                                        <svg class="ic" aria-hidden="true"><use href="#i-heart"/></svg>
                                        <span>افتخارآفرینان و رضایتمندی · {{ $websiteAcademy->name }}</span>
                                    </a>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </nav>
        @if(!$isSeoOnlyUser && !request()->routeIs('owner.people.*'))
        <section class="tm" aria-label="کلاس آنلاین بعدی">
            <small>{{ $nextClass ? 'جلسه آنلاین بعدی' : 'وضعیت کلاس آنلاین' }}</small>
            <svg viewBox="0 0 130 130" aria-hidden="true"><circle cx="65" cy="65" r="58" fill="none" stroke="#2b3270" stroke-width="2" stroke-dasharray="2 6"/><circle cx="65" cy="65" r="48" fill="none" stroke="#0a0e27" stroke-width="9"/><circle id="rg" cx="65" cy="65" r="48" fill="none" stroke-width="9" stroke-dasharray="302" stroke-dashoffset="0" stroke-linecap="round" transform="rotate(-90 65 65)"/><text id="rt" x="65" y="71" text-anchor="middle" fill="#fff" font-size="17" font-weight="800">{{ $nextClassTarget ? '۰۰:۰۰:۰۰' : '—:—:—' }}</text></svg>
            @if($nextClass)<b>{{ $nextClass->title }}</b><small>{{ $nextClass->course?->title }} · {{ \App\Support\PersianUi::dateTime($nextClass->scheduled_at) }}</small><span class="sr-only" id="timer-data" data-target="{{ $nextClassTarget }}"></span>@else<b>جلسه آینده ثبت نشده</b><small class="timer-empty">برای نمایش زمان، جلسه آینده را در برنامه کلاس ثبت کنید.</small>@endif
        </section>
        @else
            <section class="tm owner-seo-sidebar-note" aria-label="مرکز مدیریت SEO">
                <small>مرکز مدیریت سایت</small>
                <b>SEO و محتوای عمومی</b>
                <small>متادیتا، کلمات کلیدی، بنرها و مقاله‌ها از همین پنل مدیریت می‌شوند.</small>
                <a class="ni" style="--c:{{ $navGroup['color'] }}" href="{{ route('owner.seo.index') }}"><svg class="ic"><use href="#i-chart"/></svg><span>شروع ممیزی صفحات</span></a>
            </section>
        @endif
        <div class="side-foot"><a href="{{ route('account.guide') }}"><svg class="ic"><use href="#i-book"/></svg>راهنمای پنل و بروشور</a><a href="{{ route('home') }}" target="_blank" rel="noopener"><svg class="ic"><use href="#i-globe"/></svg>مشاهده سایت</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">خروج از حساب</button></form></div>
    </aside>
    @include('owner.partials.admin-main')
    @include('owner.partials.admin-command-palette')
</div>
@stack('scripts')
</body>
</html>