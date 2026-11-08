@extends('layouts.seo-specialist')

@section('title', 'محتوای سایت و ایندکس | شیخان')
@section('header-title', 'محتوای سایت و ایندکس')

@section('content')
<div class="space-y-6">
    <section class="dashboard-panel space-y-4 p-5 sm:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <span class="text-xs font-black text-indigo-600">مدیریت ساده محتوای عمومی</span>
                <h1 class="mt-2 text-2xl font-black text-slate-900">بنرها، مقاله‌ها و وضعیت ایندکس</h1>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">از این بخش محتوای منتشرشده را پیدا کن و مشخص کن موتورهای جستجو اجازه ایندکس آن را داشته باشند یا نه.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener" class="course-action-btn">مشاهده سایت ↗</a>
        </div>
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4"><span class="text-xs text-slate-500">مقاله و محتوای عمومی</span><strong class="mt-1 block text-2xl font-black">{{ \App\Support\PersianUi::digits($stats['total'] ?? 0) }}</strong></div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4"><span class="text-xs text-emerald-700">قابل ایندکس</span><strong class="mt-1 block text-2xl font-black text-emerald-800">{{ \App\Support\PersianUi::digits($stats['indexable'] ?? 0) }}</strong></div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4"><span class="text-xs text-amber-700">روی noindex</span><strong class="mt-1 block text-2xl font-black text-amber-800">{{ \App\Support\PersianUi::digits($stats['noindex'] ?? 0) }}</strong></div>
        </div>
    </section>

    <section class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('owner.website.hero.edit') }}" class="dashboard-panel flex items-center justify-between gap-3 p-5 transition hover:-translate-y-0.5">
            <span><strong class="block text-sm font-black text-slate-900">اسلایدهای اصلی صفحه خانه</strong><small class="mt-1 block text-xs text-slate-500">تصویر، متن و پیوند بنرها</small></span><b class="text-xl text-indigo-600">←</b>
        </a>
        <a href="{{ route('owner.website.hero-challenges.index') }}" class="dashboard-panel flex items-center justify-between gap-3 p-5 transition hover:-translate-y-0.5">
            <span><strong class="block text-sm font-black text-slate-900">بانک معماهای صفحه خانه</strong><small class="mt-1 block text-xs text-slate-500">ویرایش سؤال‌ها و ترتیب نمایش</small></span><b class="text-xl text-indigo-600">←</b>
        </a>
        @foreach($academies as $academy)
            <a href="{{ route('owner.academy.banners.edit', $academy) }}" class="dashboard-panel flex items-center justify-between gap-3 p-5 transition hover:-translate-y-0.5">
                <span><strong class="block text-sm font-black text-slate-900">بنرهای {{ $academy->name }}</strong><small class="mt-1 block text-xs text-slate-500">مدیریت بنرهای آموزشگاه</small></span><b class="text-xl text-indigo-600">←</b>
            </a>
        @endforeach
    </section>

    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-200 p-5">
            <h2 class="font-black text-slate-900">مقاله‌ها و بلاگ</h2>
            <p class="mt-1 text-xs leading-6 text-slate-500">فقط مقاله‌ها و نوشته‌های منتشرشده نمایش داده می‌شوند. برای هر صفحه می‌توانی عنوان، توضیح کوتاه و وضعیت ایندکس را تنظیم کنی.</p>
            <form method="GET" action="{{ route('owner.seo.index') }}" class="mt-4 grid gap-2 sm:grid-cols-[minmax(0,1fr)_180px_160px_auto]">
                <input class="ui-control px-3" type="search" name="q" value="{{ $filters['search'] ?? '' }}" placeholder="جست‌وجوی عنوان یا آدرس">
                <select class="ui-control px-3" name="type"><option value="">همه محتواها</option><option value="blog" @selected(($filters['type'] ?? '')==='blog')>بلاگ</option><option value="content" @selected(($filters['type'] ?? '')==='content')>مقاله آموزشگاه</option></select>
                <select class="ui-control px-3" name="status"><option value="">همه وضعیت‌ها</option><option value="index" @selected(($filters['status'] ?? '')==='index')>قابل ایندکس</option><option value="noindex" @selected(($filters['status'] ?? '')==='noindex')>بدون ایندکس</option></select>
                <button class="course-action-btn primary" type="submit">اعمال فیلتر</button>
            </form>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($items as $item)
                <article class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600">{{ $item['type']==='blog' ? 'بلاگ' : 'مقاله آموزشگاه' }}</span>
                            <strong class="text-sm font-black text-slate-900">{{ $item['label'] }}</strong>
                            @if(str_starts_with($item['robots'], 'noindex'))
                                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-black text-amber-800">noindex</span>
                            @else
                                <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[10px] font-black text-emerald-800">index</span>
                            @endif
                        </div>
                        @if($item['academy'])<small class="mt-1 block text-xs text-slate-500">{{ $item['academy'] }}</small>@endif
                        @if($item['url'])<a class="mt-1 inline-block max-w-full truncate text-xs text-sky-700 underline" href="{{ $item['url'] }}" target="_blank" rel="noopener">{{ $item['url'] }}</a>@endif
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <a class="course-action-btn" href="{{ route('owner.seo.edit', [$item['type'], $item['id']]) }}">ویرایش SEO / ایندکس</a>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">مقاله یا نوشته منتشرشده‌ای با این فیلتر پیدا نشد.</div>
            @endforelse
        </div>
        @if($items->hasPages())<div class="border-t border-slate-200 p-4">{{ $items->links() }}</div>@endif
        <div class="border-t border-slate-100 px-5 py-3 text-xs text-slate-500">نمایش {{ \App\Support\PersianUi::digits($filteredTotal ?? 0) }} مورد در فهرست فعلی · آخرین بررسی {{ isset($scannedAt) ? \App\Support\PersianUi::dateTime($scannedAt) : '—' }}</div>
    </section>
</div>
@endsection
