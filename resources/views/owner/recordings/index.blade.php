@extends('layouts.owner')

@section('title', 'آرشیو ضبط کلاس‌ها | شیخان')
@section('header-title', 'آرشیو ضبط کلاس‌ها')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-black text-[var(--panel-primary)]">مدیریت محتوای ضبط‌شده</p><h1 class="mt-1 text-2xl font-black">تقویم و انتشار ضبط کلاس‌ها</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">این بخش فقط ضبط‌های مرتبط با دوره‌های همین آموزشگاه را نشان می‌دهد. زمان انتشار دسترسی دانش‌آموزان مجاز را کنترل می‌کند؛ فایل ضبط در فضای خصوصی می‌ماند.</p></div>
        <a href="{{ route('owner.dashboard') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">بازگشت به داشبورد</a>
    </header>
    <section class="grid gap-3 sm:grid-cols-3">
        <article class="owner-stat-card"><span>ضبط‌های ثبت‌شده</span><strong>{{ \App\Support\PersianUi::digits($stats['all']) }}</strong><small>دارای فایل</small></article>
        <article class="owner-stat-card"><span>قابل دسترسی</span><strong>{{ \App\Support\PersianUi::digits($stats['released']) }}</strong><small>انتشار فعال</small></article>
        <article class="owner-stat-card"><span>انتشار زمان‌بندی‌شده</span><strong>{{ \App\Support\PersianUi::digits($stats['scheduled']) }}</strong><small>منتظر زمان انتشار</small></article>
    </section>
    <section class="space-y-4">
        @forelse($recordings as $item)
            @php
                $released = $item->isRecordingAvailable();
                $scheduled = $item->recording_released_at && $item->recording_released_at->isFuture();
            @endphp
            <article class="dashboard-panel p-5">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2"><h2 class="break-words text-base font-black">{{ $item->title }}</h2><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $released ? 'bg-emerald-50 text-emerald-700' : ($scheduled ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-800') }}">{{ $released ? 'قابل دسترسی' : ($scheduled ? 'در انتظار انتشار' : 'مخفی / منتشرنشده') }}</span></div>
                        <p class="mt-2 text-sm text-slate-600">{{ $item->course?->title }} · {{ $item->classroom?->title ?: 'بدون کلاس مشخص' }} · مدرس: {{ $item->teacher?->name ?: '—' }}</p>
                        <p class="mt-1 break-all text-xs text-slate-500">{{ $item->recording?->original_name ?: 'نام فایل در دسترس نیست' }} · {{ $item->ended_at ? \App\Support\PersianUi::date($item->ended_at) : 'زمان پایان ثبت نشده' }}</p>
                        @if($item->recording_released_at)<p class="mt-2 text-xs text-slate-500">زمان انتشار: {{ \App\Support\PersianUi::date($item->recording_released_at) }} · {{ \App\Support\PersianUi::time($item->recording_released_at) }}</p>@endif
                    </div>
                    <form method="POST" action="{{ route('owner.recordings.update', [$academy, $item->id]) }}" class="grid w-full gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4 xl:max-w-[420px]">
                        @csrf @method('PATCH')
                        <label class="grid gap-1 text-xs font-bold">زمان انتشار (اختیاری)<input type="datetime-local" name="release_at" value="{{ $scheduled ? $item->recording_released_at?->format('Y-m-d\TH:i') : '' }}" min="{{ now()->format('Y-m-d\TH:i') }}" class="min-h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm"><span class="font-normal text-slate-500">خالی بماند، از همین حالا منتشر می‌شود.</span></label>
                        <div class="flex flex-wrap gap-2"><button name="action" value="publish" class="min-h-10 flex-1 rounded-xl bg-[var(--panel-primary)] px-3 text-sm font-black text-white">انتشار / زمان‌بندی</button><button name="action" value="unpublish" class="min-h-10 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-700">توقف دسترسی</button></div>
                    </form>
                </div>
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center"><h2 class="font-black">ضبطی برای نمایش وجود ندارد.</h2><p class="mt-2 text-sm leading-7 text-slate-500">وقتی مدرس فایل ضبط یک جلسه را در پنل خود ثبت کند، آن ضبط در اینجا برای کنترل انتشار ظاهر می‌شود.</p></div>
        @endforelse
        <div>{{ $recordings->links() }}</div>
    </section>
</div>
@endsection
