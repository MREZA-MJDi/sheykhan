<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'مدیریت آموزشگاه | شیخان')</title>
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
    $pageHeading = trim($__env->yieldContent('header-title')) ?: 'مدیریت آموزشگاه';
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
            'title' => 'خانه',
            'color' => '#22d3ee',
            'links' => [
                ['route' => 'owner.dashboard', 'permission' => 'dashboard.view', 'label' => 'داشبورد اصلی', 'icon' => 'home', 'scope' => 'global'],
            ],
        ],
        [
            'key' => 'people',
            'title' => 'اعضا و ارتباطات',
            'color' => '#ff1f6b',
            'links' => [
                ['route' => 'owner.people.index', 'permission' => 'academy.view', 'label' => 'اعضا و پرونده‌ها', 'icon' => 'users', 'scope' => 'academy'],
                ['route' => 'owner.communication-workspace', 'permission' => ['parents.view', 'live_classes.view'], 'label' => 'پیام‌ها و ارتباطات', 'icon' => 'chat', 'scope' => 'academy'],
            ],
        ],
        [
            'key' => 'attendance',
            'title' => 'حضور و غیاب',
            'color' => '#0d9488',
            'links' => [
                ['route' => 'owner.attendance.index', 'permission' => 'attendance.view', 'label' => 'ثبت، سوابق و گزارش حضور', 'icon' => 'checksq', 'scope' => 'academy'],
            ],
        ],
        [
            'key' => 'learning',
            'title' => 'آموزش',
            'color' => '#8b3dff',
            'links' => [
                ['route' => 'owner.courses.index', 'permission' => 'courses.view', 'label' => 'مرکز آموزش', 'icon' => 'book', 'scope' => 'global'],
            ],
        ],
        [
            'key' => 'finance',
            'title' => 'مالی',
            'color' => '#2ee6a6',
            'links' => [
                ['route' => 'owner.finance.index', 'permission' => 'finance.view', 'label' => 'مرکز مالی و پرداخت‌ها', 'icon' => 'wallet', 'scope' => 'global'],
            ],
        ],
        [
            'key' => 'reports',
            'title' => 'گزارش‌ها',
            'color' => '#64748b',
            'links' => [
                ['route' => 'owner.reports.index', 'permission' => 'reports.view', 'label' => 'گزارش‌های مدیریتی', 'icon' => 'chart', 'scope' => 'global'],
            ],
        ],
        [
            'key' => 'more',
            'title' => 'محتوا و سایت',
            'color' => '#9aa3d8',
            'links' => [
                ['route' => 'owner.academy.edit', 'permission' => 'academy.view', 'label' => 'تنظیمات آموزشگاه', 'icon' => 'cog', 'scope' => 'academy'],
                ['route' => 'owner.spotplayer.edit', 'permission' => 'courses.manage', 'label' => 'SpotPlayer و لایسنس‌ها', 'icon' => 'video', 'scope' => 'global'],
                ['route' => 'owner.legal-documents.index', 'permission' => 'legal.manage', 'label' => 'اسناد حقوقی', 'icon' => 'scale', 'scope' => 'global'],
                ['route' => 'owner.content-rights.index', 'permission' => 'legal.manage', 'label' => 'حقوق نشر و مجوز محتوا', 'icon' => 'scale', 'scope' => 'global'],
            ],
        ],
    ];

    $currentOwnerNavGroupKey = match (true) {
        request()->routeIs('owner.classrooms.*', 'owner.assignments.*', 'owner.exams.*', 'owner.question-bank.*', 'owner.schedules.*', 'owner.meetings.*', 'owner.recordings.*', 'owner.resources.*', 'owner.courses.*') => 'learning',
        request()->routeIs('owner.finance.*', 'owner.teacher-payments.*', 'owner.orders.*') => 'finance',
        request()->routeIs('owner.attendance.*') => 'attendance',
        request()->routeIs('owner.reports.*') => 'reports',
        request()->routeIs('owner.people.*', 'owner.communication-workspace', 'owner.communications.*', 'owner.messages.*', 'owner.counseling.*') => 'people',
        default => null,
    };
    foreach ($ownerNavGroups as $candidateGroup) {
        if ($currentOwnerNavGroupKey !== null) {
            break;
        }
        if (
            $candidateGroup['key'] === 'learning'
            && request()->routeIs(
                'owner.courses.*',
                'owner.classrooms.*',
                'owner.schedules.*',
                'owner.assignments.*',
                'owner.exams.*',
                'owner.question-bank.*',
                'owner.recordings.*',
                'owner.resources.*'
            )
        ) {
            $currentOwnerNavGroupKey = 'learning';
            break;
        }

        if (
            $candidateGroup['key'] === 'finance'
            && request()->routeIs('owner.finance.*', 'owner.orders.*', 'owner.teacher-payments.*')
        ) {
            $currentOwnerNavGroupKey = 'finance';
            break;
        }

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
                        ->filter(fn ($link) => $link['scope'] === 'global'
                            && $hasNavPermission($link['permission'])
                            && (! in_array($link['route'], ['owner.blog.index', 'owner.content.index', 'owner.website.index', 'owner.seo.index'], true) || $canManagePublicSite));
                    $visibleAcademyLinks = collect($navGroup['links'])
                        ->filter(fn ($link) => $link['scope'] === 'academy' && $hasNavPermission($link['permission']));
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
                                    <?php if ($navLink['scope'] === 'global' && $hasNavPermission($navLink['permission']) && (! in_array($navLink['route'], ['owner.blog.index', 'owner.content.index', 'owner.website.index', 'owner.seo.index'], true) || $canManagePublicSite)) { ?>
                                        <?php
                                            $navRoutePattern = str_contains($navLink['route'], '.index')
                                                ? str_replace('.index', '.*', $navLink['route'])
                                                : $navLink['route'];
                                        ?>
                                        <a class="ni" style="--c:{{ $navGroup['color'] }}"
                                           href="{{ route($navLink['route']) }}"
                                           <?php if (request()->routeIs($navRoutePattern) || ($navGroup['key'] === $currentOwnerNavGroupKey && in_array($navGroup['key'], ['learning', 'finance', 'attendance', 'reports'], true))) echo 'aria-current="page"'; ?>
                                           data-nav-label="{{ $navLink['label'] }}">
                                            <svg class="ic" aria-hidden="true"><use href="#i-{{ $navLink['icon'] }}"/></svg>
                                            <span>{{ $navLink['label'] }}</span>
                                        </a>
                                    <?php } elseif ($navLink['scope'] === 'academy' && $hasNavPermission($navLink['permission'])) { ?>
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
                                <a class="ni" style="--c:{{ $navGroup['color'] }}" href="{{ route('owner.blog.index') }}" <?php if (request()->routeIs('owner.blog.*')) echo 'aria-current="page"'; ?> data-nav-label="مقاله‌ها">
                                    <svg class="ic" aria-hidden="true"><use href="#i-news"/></svg><span>مقاله‌ها</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}" href="{{ route('owner.content.index') }}" <?php if (request()->routeIs('owner.content.*')) echo 'aria-current="page"'; ?> data-nav-label="محتوای آموزشی">
                                    <svg class="ic" aria-hidden="true"><use href="#i-news"/></svg><span>محتوای آموزشی</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}" href="{{ route('owner.website.index') }}" <?php if (request()->routeIs('owner.website.*')) echo 'aria-current="page"'; ?> data-nav-label="صفحات و بنرها">
                                    <svg class="ic" aria-hidden="true"><use href="#i-globe"/></svg><span>صفحات و بنرها</span>
                                </a>
                                <a class="ni" style="--c:{{ $navGroup['color'] }}" href="{{ route('owner.seo.index') }}" <?php if (request()->routeIs('owner.seo.*')) echo 'aria-current="page"'; ?> data-nav-label="SEO و کلمات کلیدی">
                                    <svg class="ic" aria-hidden="true"><use href="#i-search"/></svg><span>SEO و کلمات کلیدی</span>
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
                <small>اطلاعات سایت، عبارت‌های جست‌وجو، بنرها و مقاله‌ها از این بخش مدیریت می‌شوند.</small>
                <a class="ni" style="--c:{{ $navGroup['color'] }}" href="{{ route('owner.seo.index') }}"><svg class="ic"><use href="#i-chart"/></svg><span>شروع ممیزی صفحات</span></a>
            </section>
        @endif
        <div class="side-foot"><a href="{{ route('account.guide') }}"><svg class="ic"><use href="#i-book"/></svg>راهنمای استفاده</a><a href="{{ route('home') }}" target="_blank" rel="noopener"><svg class="ic"><use href="#i-globe"/></svg>مشاهده سایت</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">خروج از حساب</button></form></div>
    </aside>
    @include('owner.partials.admin-main')
    @include('owner.partials.admin-command-palette')
</div>
@stack('scripts')
</body>
</html>