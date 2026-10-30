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