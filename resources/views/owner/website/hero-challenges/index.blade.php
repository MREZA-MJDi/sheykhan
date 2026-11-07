@extends('layouts.seo-specialist')

@section('title', 'بانک چالش‌های هوش | شیخان')
@section('header-title', 'بانک چالش‌های هوش')

@section('content')
<div class="page-enter space-y-6">
    <section class="website-center-hero">
        <div>
            <span class="website-kicker">مدیریت محتوای Hero</span>
            <h1>بانک معماهای صفحه اصلی</h1>
            <p>معماها به ترتیب انتشار در صفحه اصلی نمایش داده می‌شوند. برای تغییر ترتیب، شماره نمایش را ویرایش کن؛ برای برداشتن یک سؤال از چرخه، آن را غیرفعال کن.</p>
        </div>
        <a href="{{ route('home') }}#hero-challenge" target="_blank" rel="noopener" class="website-open-btn">پیش‌نمایش سایت ↗</a>
    </section>

    <section class="dashboard-panel overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-5">
            <div><h2 class="font-black">معماهای ثبت‌شده</h2><p class="mt-1 text-xs text-slate-500">پاسخ صحیح و توضیح فقط از سرور بررسی می‌شوند.</p></div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold">{{ \App\Support\PersianUi::digits($challenges->total()) }} معما</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($challenges as $challenge)
                <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-black">{{ $challenge->title }}</h3>
                            @if($challenge->is_active)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black text-emerald-700">در چرخه نمایش</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600">غیرفعال</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-600">{{ $challenge->prompt }}</p>
                        <p class="mt-2 text-[11px] text-slate-400">ترتیب نمایش: {{ \App\Support\PersianUi::digits($challenge->sort_order) }} · {{ \App\Support\PersianUi::digits($challenge->options_count) }} گزینه</p>
                    </div>
                    <a class="course-primary-btn shrink-0" href="{{ route('owner.website.hero-challenge.edit', $challenge) }}">ویرایش معما</a>
                </article>
            @empty
                <div class="p-8 text-center text-sm text-slate-500">هنوز معمایی ثبت نشده است.</div>
            @endforelse
        </div>

        @if($challenges->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $challenges->links() }}</div>
        @endif
    </section>
</div>
@endsection
