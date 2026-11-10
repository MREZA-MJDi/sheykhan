@php
    $publicSiteSheets = [
        ['key' => 'blog', 'label' => 'مقاله‌ها', 'route' => 'owner.blog.index', 'pattern' => 'owner.blog.*'],
        ['key' => 'content', 'label' => 'محتوای آموزشی', 'route' => 'owner.content.index', 'pattern' => 'owner.content.*'],
        ['key' => 'website', 'label' => 'صفحات و بنرها', 'route' => 'owner.website.index', 'pattern' => 'owner.website.*'],
        ['key' => 'seo', 'label' => 'نمایش در گوگل', 'route' => 'owner.seo.index', 'pattern' => 'owner.seo.*'],
    ];
@endphp
@if(auth()->user()?->hasRole('seo-specialist') && auth()->user()?->hasPermission('seo.manage'))
    <section class="owner-workspace-tabs mb-5 overflow-hidden rounded-2xl border border-indigo-100 bg-gradient-to-l from-indigo-50 via-white to-slate-50 p-4 sm:p-5" aria-label="بخش محتوا و سایت">
        <div><p class="text-[11px] font-black text-indigo-700">بخش محتوا و سایت</p><h2 class="mt-1 text-lg font-black text-slate-900">مدیریت محتوای سایت آموزشگاه</h2><p class="mt-1 text-xs leading-6 text-slate-600">مقاله‌ها، محتوای آموزشی، صفحه‌های سایت و تنظیمات نمایش در جست‌وجو را از اینجا مدیریت کن.</p></div>
        <nav class="mt-4 flex flex-wrap gap-2" aria-label="بخش‌های محتوا و سایت">
            @foreach($publicSiteSheets as $sheet)
                <a href="{{ route($sheet['route']) }}" @if(request()->routeIs($sheet['pattern'])) aria-current="page" @endif
                   class="inline-flex min-h-10 items-center rounded-xl px-4 text-xs font-black transition {{ request()->routeIs($sheet['pattern']) ? 'bg-indigo-700 text-white shadow-sm' : 'border border-indigo-100 bg-white text-slate-700 hover:border-indigo-300 hover:text-indigo-800' }}">{{ $sheet['label'] }}</a>
            @endforeach
            <a href="{{ route('owner.seo.keywords.index') }}" @if(request()->routeIs('owner.seo.keywords.*')) aria-current="page" @endif class="inline-flex min-h-10 items-center rounded-xl border border-indigo-100 bg-white px-4 text-xs font-black text-slate-700 hover:border-indigo-300 hover:text-indigo-800">برنامه کلمات کلیدی</a>
        </nav>
    </section>
@endif
