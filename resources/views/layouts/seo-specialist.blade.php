<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'کارتابل مدیر آموزشگاه | شیخان')</title>
    @vite(['resources/css/owner-panel.css', 'resources/js/seo-dashboard.js'])
    @stack('styles')
</head>
<body class="owner-shell">
@php
    $ownerUser = auth()->user();
    $isSeoOnlyUser = $ownerUser && $ownerUser->hasRole('seo-specialist') && ! $ownerUser->hasRole('academy-owner');
    $canManagePublicSite = $ownerUser && $ownerUser->hasRole('seo-specialist') && $ownerUser->hasPermission('seo.manage');
    $academies = $ownerUser && $ownerUser->hasRole('academy-owner')
        ? $ownerUser->ownedAcademies()->where('status', 'active')->orderBy('name')->get(['id','name','slug','owner_id'])
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
    $nextClass = collect(isset($upcomingLiveClasses) ? $upcomingLiveClasses : [])->first();
    $nextClassTarget = $nextClass?->scheduled_at?->timestamp;
    $scopedLinks = [
        ['owner.people.index','academy.view','اعضا و ثبت‌نام','users'],
        ['owner.classrooms.index','classrooms.view','کلاس‌ها','calendar'],
        ['owner.attendance.index','attendance.view','حضور و غیاب','checksq'],
        ['owner.assignments.index','courses.view','تکالیف و تصحیح','doc'],
        ['owner.exams.index','exams.view','آزمون‌ها','book'],
        ['owner.schedules.index','live_classes.view','برنامه کلاس','video'],
        ['owner.meetings.index','live_classes.view','جلسات','calendar'],
        ['owner.recordings.index','live_classes.view','آرشیو ضبط','video'],
        ['owner.messages.index','parents.view','پیام‌های اولیا','chat'],
        ['owner.communications.index','parents.view','اطلاعیه‌ها','mega'],
        ['owner.counseling.index','parents.view','مشاوره','chat'],
        ['owner.resources.index','resources.view','منابع آموزشی','book'],
        ['owner.products.index','products.view','محصولات','tag'],
        ['owner.orders.index','orders.view','سفارش‌ها','receipt'],
        ['owner.teacher-payments.index','teacher_payments.view','حقوق مدرس‌ها','wallet'],
        ['owner.academy.edit','academy.view','تنظیمات آموزشگاه','cog'],
    ];
    $globalLinks = [
        ['owner.courses.index','courses.view','دوره‌ها','book'],
        ['owner.reports.index','reports.view','گزارش‌های آموزشی','chart'],
        ['owner.finance.index','finance.view','مالی و پرداخت‌ها','wallet'],
        ['owner.legal-documents.index','legal.manage','اسناد حقوقی','scale'],
    ];
@endphp
<div class="app">
    @include('owner.partials.dashboard-icons')
    <header class="top">
        <button class="burger" id="burger" type="button" aria-label="باز و بسته کردن منوی اصلی" aria-expanded="false" aria-controls="side"><svg class="ic"><use href="#i-menu"/></svg></button>
        <a class="brand-mark" href="{{ route($ownerHomeRoute) }}" aria-label="ورود به مرکز مدیریت شیخان"><span class="lg" aria-hidden="true">ش</span><span class="brand">آکادمی شیخان</span></a>
        <span class="top-page-title">{{ $pageHeading }}</span>
        <nav class="tabs" aria-label="دسترسی سریع">
            @if(request()->routeIs('owner.dashboard'))
                @foreach(['today'=>'امروز','week'=>'این هفته','month'=>'این ماه'] as $periodKey=>$periodLabel)
                    <a class="tab {{ $period === $periodKey ? 'on' : '' }}" href="{{ route('owner.dashboard',['period'=>$periodKey]) }}" @if($period === $periodKey) aria-current="page" @endif>{{ $periodLabel }}</a>
                @endforeach
            @elseif($canManagePublicSite)
                <a class="tab {{ request()->routeIs('owner.seo.index','owner.seo.edit','owner.seo.update') ? 'on' : '' }}" href="{{ route('owner.seo.index') }}">ممیزی SEO</a>
                <a class="tab {{ request()->routeIs('owner.seo.keywords.*') ? 'on' : '' }}" href="{{ route('owner.seo.keywords.index') }}">کلمات کلیدی</a>
                <a class="tab {{ request()->routeIs('owner.website.*') ? 'on' : '' }}" href="{{ route('owner.website.index') }}">مرکز سایت</a>
            @else
                <a class="tab" href="{{ route('owner.dashboard') }}">نمای کلی</a>
                @if($ownerUser?->hasPermission('courses.view'))<a class="tab" href="{{ route('owner.courses.index') }}">دوره‌ها</a>@endif
                @if($ownerUser?->hasPermission('reports.view'))<a class="tab" href="{{ route('owner.reports.index') }}">گزارش‌ها</a>@endif
                @if($ownerUser?->hasPermission('finance.view'))<a class="tab" href="{{ route('owner.finance.index') }}">مالی</a>@endif
            @endif
        </nav>
        <button class="srch" id="srch" type="button" aria-label="جستجوی صفحه‌ها"><svg class="ic"><use href="#i-search"/></svg><span>جستجوی صفحه</span><kbd>Ctrl K</kbd></button>
        <div class="nt">
            <button class="bell" id="bell" type="button" aria-label="اعلان‌ها" aria-expanded="false" aria-controls="pop"><svg class="ic"><use href="#i-bell"/></svg><b id="nb">{{ $notifications->count() ? \App\Support\PersianUi::digits($notifications->count()) : '' }}</b></button>
            <div class="pop" id="pop" hidden>
                <div class="card-title"><div><h2>موارد نیازمند پیگیری</h2><p>بر اساس داده‌های واقعی سامانه</p></div></div>
                @forelse($notifications as $notification)
                    <a class="fd" href="{{ $notification['url'] }}" style="--c:{{ $notification['color'] ?? '#22d3ee' }}"><i aria-hidden="true"></i><span class="gx"><b>{{ $notification['title'] }}</b><small>{{ $notification['detail'] }}</small></span><svg class="ic"><use href="#i-arr"/></svg></a>
                @empty
                    <div class="em">اعلان فوری تازه‌ای برای نمایش ثبت نشده است.</div>
                @endforelse
            </div>
        </div>
        <div class="av" title="{{ $ownerUser?->name }}" aria-label="حساب {{ $ownerUser?->name }}">{{ $ownerInitial }}</div>
    </header>
    <aside class="side card" id="side" aria-label="منوی مدیریت">
        <nav id="nv" aria-label="منوی اصلی">
            @if(!$isSeoOnlyUser && $ownerUser?->hasPermission('dashboard.view'))
                <div class="nav-group"><button class="gh" type="button" data-group-toggle aria-expanded="true" style="--c:#22d3ee"><i aria-hidden="true"></i><span>مرکز کنترل</span><em>−</em></button><div class="nav-group-items"><a class="ni" href="{{ route('owner.dashboard') }}" @if(request()->routeIs('owner.dashboard')) aria-current="page" @endif data-nav-label="داشبورد"><svg class="ic"><use href="#i-home"/></svg><span>داشبورد</span></a></div></div>
            @endif
            @if($canManagePublicSite)
                <div class="nav-group">
                    <button class="gh" type="button" data-group-toggle aria-expanded="true" style="--c:#22d3ee"><i aria-hidden="true"></i><span>سایت و SEO</span><em>−</em></button>
                    <div class="nav-group-items">
                        <a class="ni" href="{{ route('owner.seo.index') }}" @if(request()->routeIs('owner.seo.index','owner.seo.edit','owner.seo.update')) aria-current="page" @endif data-nav-label="ممیزی SEO"><svg class="ic"><use href="#i-chart"/></svg><span>ممیزی SEO</span></a>
                        <a class="ni" href="{{ route('owner.seo.keywords.index') }}" @if(request()->routeIs('owner.seo.keywords.*')) aria-current="page" @endif data-nav-label="کلمات کلیدی"><svg class="ic"><use href="#i-search"/></svg><span>کلمات کلیدی</span></a>
                        <a class="ni" href="{{ route('owner.website.index') }}" @if(request()->routeIs('owner.website.index')) aria-current="page" @endif data-nav-label="مرکز سایت"><svg class="ic"><use href="#i-globe"/></svg><span>مرکز سایت و بنرها</span></a>
                        <a class="ni" href="{{ route('owner.website.hero.edit') }}" @if(request()->routeIs('owner.website.hero.*')) aria-current="page" @endif data-nav-label="اسلایدر صفحه اصلی"><svg class="ic"><use href="#i-mega"/></svg><span>اسلایدر صفحه اصلی</span></a>
                        <a class="ni" href="{{ route('owner.content.index') }}" @if(request()->routeIs('owner.content.*')) aria-current="page" @endif data-nav-label="محتوای عمومی"><svg class="ic"><use href="#i-book"/></svg><span>محتوای عمومی</span></a>
                        <a class="ni" href="{{ route('owner.blog.index') }}" @if(request()->routeIs('owner.blog.*')) aria-current="page" @endif data-nav-label="بلاگ و مقالات"><svg class="ic"><use href="#i-doc"/></svg><span>بلاگ و مقالات</span></a>
                        @foreach($websiteAcademies as $websiteAcademy)
                            <a class="ni" href="{{ route('owner.academy.banners.edit', $websiteAcademy) }}" @if(request()->routeIs('owner.academy.banners.*') && $routeAcademyId === (int) $websiteAcademy->id) aria-current="page" @endif data-nav-label="بنرهای {{ $websiteAcademy->name }}"><svg class="ic"><use href="#i-image"/></svg><span>بنرهای {{ $websiteAcademy->name }}</span></a>
                            <a class="ni" href="{{ route('owner.showcase.index', $websiteAcademy) }}" @if(request()->routeIs('owner.showcase.*') && $routeAcademyId === (int) $websiteAcademy->id) aria-current="page" @endif data-nav-label="افتخارآفرینان {{ $websiteAcademy->name }}"><svg class="ic"><use href="#i-users"/></svg><span>افتخارآفرینان و رضایتمندی · {{ $websiteAcademy->name }}</span></a>
                        @endforeach
                    </div>
                </div>
            @endif

            @foreach($academies as $academy)
                <div class="nav-group">
                    <button class="gh" type="button" data-group-toggle aria-expanded="true" style="--c:{{ $loop->even ? '#ff1f6b' : '#22d3ee' }}"><i aria-hidden="true"></i><span>{{ $academy->name }}</span><em>−</em></button>
                    <div class="nav-group-items">
                        @foreach($scopedLinks as [$routeName,$permission,$label,$icon])
                            @if($ownerUser?->hasPermission($permission))
                                <a class="ni" href="{{ route($routeName,$academy) }}" @if(request()->routeIs(str_replace('.index','.*',$routeName)) && $routeAcademyId === (int)$academy->id) aria-current="page" @endif data-nav-label="{{ $label }}"><svg class="ic"><use href="#i-{{ $icon }}"/></svg><span>{{ $label }}</span></a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
            @if(!$isSeoOnlyUser)
                <div class="nav-group">
                    <button class="gh" type="button" data-group-toggle aria-expanded="true" style="--c:#8b3dff"><i aria-hidden="true"></i><span>آموزش و گزارش</span><em>−</em></button>
                    <div class="nav-group-items">
                        @foreach($globalLinks as [$routeName,$permission,$label,$icon])
                            @if($ownerUser?->hasPermission($permission))
                                <a class="ni" href="{{ route($routeName) }}" @if(request()->routeIs(str_replace('.index','.*',$routeName))) aria-current="page" @endif data-nav-label="{{ $label }}"><svg class="ic"><use href="#i-{{ $icon }}"/></svg><span>{{ $label }}</span></a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </nav>
        @if(!$isSeoOnlyUser)
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
                <a class="ni" href="{{ route('owner.seo.index') }}"><svg class="ic"><use href="#i-chart"/></svg><span>شروع ممیزی صفحات</span></a>
            </section>
        @endif
        <div class="side-foot"><a href="{{ route('home') }}" target="_blank" rel="noopener"><svg class="ic"><use href="#i-globe"/></svg>مشاهده سایت</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">خروج از حساب</button></form></div>
    </aside>
    <main class="main main-content" id="main" tabindex="-1">
        @if(session('success') || session('error') || $errors->any())
            <section class="owner-dashboard-feedback c12" aria-live="polite">
                @if(session('success'))<div class="alert is-success" role="status">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert is-error" role="alert">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert is-error" role="alert"><strong>اطلاعات فرم نیاز به اصلاح دارد.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            </section>
        @endif
        @yield('content')
    </main>
    <dialog id="pal" class="command-palette" aria-label="جستجوی صفحه‌ها"><form method="dialog" class="command-palette-head"><svg class="ic"><use href="#i-search"/></svg><input id="pq" type="search" placeholder="نام صفحه را بنویس…" autocomplete="off" aria-label="جستجوی صفحه"><button type="submit" aria-label="بستن جستجو">×</button></form><ul id="pl" aria-label="نتایج جستجو"></ul><p class="command-palette-hint">با کلیدهای جهت‌دار جابه‌جا شو و برای رفتن به صفحه Enter را بزن.</p></dialog>
    <div id="toast" role="status" aria-live="polite"></div>
</div>
@stack('scripts')
</body>
</html>