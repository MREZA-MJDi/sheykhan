<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'مدیریت آموزشگاه | شیخان')</title>
@vite(['resources/views/owner/admin/assets/admin.css','resources/views/owner/admin/assets/admin.js'])
@stack('styles')
</head>
<body class="owner-shell owner-admin-v2 owner-admin-unified">
@php
$ownerUser=auth()->user();
$isSeoOnlyUser=$ownerUser && $ownerUser->hasRole('seo-specialist') && ! $ownerUser->hasRole('academy-owner');
$canManagePublicSite=$ownerUser && $ownerUser->hasRole('seo-specialist') && $ownerUser->hasPermission('seo.manage');
$academies=$ownerUser && $ownerUser->hasRole('academy-owner') ? (isset($academies) ? collect($academies) : $ownerUser->ownedAcademies()->where('status','active')->orderBy('name')->get(['id','name','slug','owner_id'])) : collect();
$websiteAcademies=$canManagePublicSite ? ($isSeoOnlyUser ? \App\\Models\\Academy::query()->where('status','active')->orderBy('name')->get(['id','name','slug']) : $academies) : collect();
$ownerHomeRoute=$isSeoOnlyUser ? 'owner.seo.index' : 'owner.dashboard';
$pageHeading=trim($__env->yieldContent('header-title')) ?: 'مدیریت آموزشگاه';
$period=in_array($period ?? request()->query('period','month'),['today','week','month'],true) ? ($period ?? request()->query('period','month')) : 'month';
$notifications=collect($notifications ?? []);
$ownerInitial=mb_substr(trim((string)($ownerUser?->name ?? 'ش')),0,1);
$hasPermission=static function($permission) use($ownerUser):bool { foreach((array)$permission as $item){if($ownerUser?->hasPermission($item)) return true;} return false; };
$routeAcademy=request()->route('academy');
$routeAcademyId=$routeAcademy instanceof \App\\Models\\Academy ? (int)$routeAcademy->getKey() : (int)($routeAcademy ?? 0);
$groups=[
 ['title'=>'مرکز کنترل','links'=>[['route'=>'owner.dashboard','label'=>'داشبورد اصلی','icon'=>'⌂','permission'=>'dashboard.view','scope'=>'global']]],
 ['title'=>'آموزشگاه','links'=>[
 ['route'=>'owner.people.index','label'=>'دانش‌آموزان و اولیا','icon'=>'♙','permission'=>'academy.view','scope'=>'academy'],
 ['route'=>'owner.classrooms.index','label'=>'کلاس‌ها','icon'=>'▦','permission'=>'classrooms.view','scope'=>'academy'],
 ['route'=>'owner.attendance.index','label'=>'حضور و غیاب','icon'=>'✓','permission'=>'attendance.view','scope'=>'academy'],
 ['route'=>'owner.assignments.index','label'=>'تکالیف','icon'=>'✎','permission'=>'assignments.view','scope'=>'academy'],
 ['route'=>'owner.communications.index','label'=>'پیام‌ها و اطلاعیه‌ها','icon'=>'✉','permission'=>'parents.view','scope'=>'academy'],
 ['route'=>'owner.schedules.index','label'=>'برنامه کلاس‌ها','icon'=>'◷','permission'=>'live_classes.view','scope'=>'academy'],
 ['route'=>'owner.exams.index','label'=>'آزمون‌ها','icon'=>'★','permission'=>'exams.view','scope'=>'academy'],
 ['route'=>'owner.recordings.index','label'=>'فیلم کلاس‌ها','icon'=>'▷','permission'=>'live_classes.view','scope'=>'academy'],
 ['route'=>'owner.teacher-payments.index','label'=>'حقوق مدرسان','icon'=>'₮','permission'=>'teacher_payments.view','scope'=>'academy'],
 ['route'=>'owner.resources.index','label'=>'منابع آموزشی','icon'=>'▤','permission'=>'resources.view','scope'=>'academy'],
 ['route'=>'owner.products.index','label'=>'محصولات','icon'=>'▣','permission'=>'products.view','scope'=>'academy'],
 ['route'=>'owner.orders.index','label'=>'سفارش‌ها','icon'=>'▧','permission'=>'orders.view','scope'=>'academy'],
 ]],
 ['title'=>'مدیریت کل','links'=>[
 ['route'=>'owner.courses.index','label'=>'دوره‌ها','icon'=>'▤','permission'=>'courses.view','scope'=>'global'],
 ['route'=>'owner.finance.index','label'=>'مالی و پرداخت‌ها','icon'=>'₮','permission'=>'finance.view','scope'=>'global'],
 ['route'=>'owner.reports.index','label'=>'گزارش‌ها','icon'=>'▥','permission'=>'reports.view','scope'=>'global'],
 ['route'=>'owner.legal-documents.index','label'=>'اسناد حقوقی','icon'=>'§','permission'=>'legal.manage','scope'=>'global'],
 ]],
 ['title'=>'محتوا و سایت','links'=>[
 ['route'=>'owner.seo.index','label'=>'مدیریت SEO','icon'=>'⌕','permission'=>'seo.manage','scope'=>'global'],
 ['route'=>'owner.website.index','label'=>'مدیریت صفحات سایت','icon'=>'▧','permission'=>'seo.manage','scope'=>'global'],
 ['route'=>'owner.blog.index','label'=>'مقاله‌ها','icon'=>'✉','permission'=>'seo.manage','scope'=>'global'],
 ['route'=>'owner.content.index','label'=>'محتوای آموزشی','icon'=>'▤','permission'=>'seo.manage','scope'=>'global'],
 ]],
];
@endphp
<div class="app admin-app">
<header class="top admin-topbar">
<button class="burger" id="burger" type="button" aria-label="باز و بسته کردن منو" aria-expanded="false">☰</button>
<a class="brand-mark" href="{{ route($ownerHomeRoute) }}"><span class="lg">ش</span><span class="brand">آکادمی شیخان</span></a>
<div class="top-page-title">@yield('header-title','داشبورد')</div>
@if(request()->routeIs('owner.dashboard'))
<nav class="tabs" aria-label="بازه گزارش">
@foreach(['today'=>'امروز','week'=>'این هفته','month'=>'این ماه'] as $key=>$label)
<a class="tab {{ $period===$key?'on':'' }}" href="{{ route('owner.dashboard',['period'=>$key]) }}" @if($period===$key) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav>
@endif
<label class="admin-search"><span>⌕</span><input id="adminSearch" type="search" placeholder="جستجو در منو…" autocomplete="off"></label>
<div class="nt">
<button class="bell" id="bell" type="button" aria-label="اعلان‌ها" aria-expanded="false" aria-controls="pop">♧<b>{{ $notifications->count() ? \App\\Support\\PersianUi::digits($notifications->count()) : '' }}</b></button>
<div class="pop" id="pop" hidden>
<strong>موارد نیازمند پیگیری</strong>
@forelse($notifications as $notification)
<a class="fd" href="{{ $notification['url'] }}"><span class="gx"><b>{{ $notification['title'] }}</b><small>{{ $notification['detail'] }}</small></span></a>
@empty <p class="em">مورد فوری تازه‌ای برای نمایش ثبت نشده است.</p> @endforelse
</div>
</div>
<div class="av" title="{{ $ownerUser?->name }}">{{ $ownerInitial }}</div>
</header>
<aside class="side admin-sidebar" id="side" aria-label="منوی مدیریت">
<div class="admin-sidebar-brand"><span class="admin-brand-mark">ش</span><div><strong>آکادمی شیخان</strong><small>{{ $isSeoOnlyUser ? 'پنل متخصص SEO' : 'پنل ادمین / منشی' }}</small></div></div>
<nav id="nv" aria-label="منوی اصلی">
@foreach($groups as $group)
@php
$visibleGlobal=collect($group['links'])->filter(fn($link)=>$link['scope']==='global' && $hasPermission($link['permission']) && \Illuminate\\Support\\Facades\\Route::has($link['route']));
$visibleAcademy=collect($group['links'])->filter(fn($link)=>$link['scope']==='academy' && $hasPermission($link['permission']) && \Illuminate\\Support\\Facades\\Route::has($link['route']));
$showGroup=(!$isSeoOnlyUser && ($visibleGlobal->isNotEmpty() || ($academies->isNotEmpty() && $visibleAcademy->isNotEmpty()))) || ($group['title']==='محتوا و سایت' && $canManagePublicSite && $isSeoOnlyUser);
@endphp
@if($showGroup)
<details class="nav-group" open>
<summary class="gh"><i></i><span>{{ $group['title'] }}</span><em>⌄</em></summary>
<div class="nav-group-items">
@if(!$isSeoOnlyUser)
@foreach($visibleGlobal as $link)
<a class="ni" href="{{ route($link['route']) }}" data-nav-label="{{ $link['label'] }}" @if(request()->routeIs(str_replace('.index','.*',$link['route']))) aria-current="page" @endif><span class="nav-icon">{{ $link['icon'] }}</span><span>{{ $link['label'] }}</span></a>
@endforeach
@foreach($academies as $academy)
@foreach($visibleAcademy as $link)
<a class="ni" href="{{ route($link['route'],$academy) }}" data-nav-label="{{ $link['label'] }} {{ $academy->name }}" @if(request()->routeIs(str_replace('.index','.*',$link['route'])) && $routeAcademyId===(int)$academy->id) aria-current="page" @endif><span class="nav-icon">{{ $link['icon'] }}</span><span>{{ $link['label'] }}{{ $academies->count()>1 ? ' · '.$academy->name : '' }}</span></a>
@endforeach
@endforeach
@endif
@if($group['title']==='محتوا و سایت' && $canManagePublicSite)
@foreach([['owner.blog.index','مقاله‌ها'],['owner.content.index','محتوای آموزشی'],['owner.website.index','صفحات و بنرها'],['owner.seo.index','نمایش سایت در گوگل']] as [$routeName,$label])
@if(\Illuminate\\Support\\Facades\\Route::has($routeName))<a class="ni" href="{{ route($routeName) }}" data-nav-label="{{ $label }}"><span class="nav-icon">↗</span><span>{{ $label }}</span></a>@endif
@endforeach
@endif
</div></details>
@endif
@endforeach
</nav>
<div class="side-foot">
<a href="{{ route('account.guide') }}">راهنمای استفاده</a><a href="{{ route('home') }}" target="_blank" rel="noopener">مشاهده سایت</a>
<form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">خروج</button></form>
</div>
</aside>
<main class="main main-content" id="main" tabindex="-1">
@if(session('success') || session('error') || $errors->any())
<section class="owner-dashboard-feedback c12" aria-live="polite">
@if(session('success'))<div class="alert is-success" role="status">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert is-error" role="alert">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert is-error" role="alert"><strong>لطفاً موارد زیر را بررسی و اصلاح کن:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
</section>
@endif
@yield('content')
</main>
</div>
@stack('scripts')
</body></html>