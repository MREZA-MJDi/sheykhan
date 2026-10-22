@extends('layouts.owner')

@section('title', 'تکالیف و تصحیح | شیخان')
@section('header-title', 'تکالیف و تصحیح')

@section('content')
@php
    $statusLabels = ['draft' => 'پیش‌نویس', 'published' => 'منتشرشده', 'closed' => 'بسته'];
@endphp

<div class="space-y-6 panel-page-enter">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-black text-[var(--panel-primary)]">کارتابل ادمین / منشی</p>
            <h1 class="mt-1 text-2xl font-black">{{ $academy->name }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">تکلیف را به کلاس فعال دارای مدرس متصل کن، فایل راهنما را بارگذاری کن و پاسخ‌های ثبت‌شده را تصحیح کن.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('owner.assignments.create', $academy) }}" class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">+ تکلیف جدید</a>
            <a href="{{ route('owner.dashboard') }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-black text-slate-600">بازگشت به کارتابل</a>
        </div>
    </header>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        @foreach([['کل تکالیف', $stats['total'] ?? 0], ['منتشرشده', $stats['published'] ?? 0], ['منتظر تصحیح', $stats['pending_reviews'] ?? 0]] as [$label, $value])
            <article class="owner-stat-card"><span>{{ $label }}</span><strong>{{ \App\Support\PersianUi::digits($value) }}</strong><small>داده واقعی آموزشگاه</small></article>
        @endforeach
    </section>

    <section class="dashboard-panel p-5 sm:p-6">
        <form method="GET" action="{{ route('owner.assignments.index', $academy) }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <label class="grid gap-1 sm:col-span-2">
                <span class="text-xs font-bold">جست‌وجوی عنوان، دوره یا کلاس</span>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="120" class="rounded-xl border border-slate-200 px-3 py-3 text-sm" placeholder="مثلاً فصل سوم یا کلاس فیزیک">
            </label>
            <label class="grid gap-1">
                <span class="text-xs font-bold">وضعیت</span>
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">اعمال فیلتر</button>
                <a href="{{ route('owner.assignments.index', $academy) }}" class="rounded-xl border border-slate-200 px-4 py-3 text-xs font-black text-slate-600">پاک‌کردن</a>
            </div>
        </form>
        <p class="mt-3 text-xs text-slate-500">{{ \App\Support\PersianUi::digits($assignments->total()) }} تکلیف با فیلتر فعلی · نمایش ۱۵ مورد در هر صفحه</p>
    </section>

    <section class="dashboard-panel p-5 sm:p-6">
        <div class="grid gap-3">
            @forelse($assignments as $assignment)
                <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <a href="{{ route('owner.assignments.show', [$academy, $assignment]) }}" class="text-sm font-black text-slate-900 underline-offset-4 hover:underline">{{ $assignment->title }}</a>
                            <p class="mt-1 text-xs text-slate-500">{{ $assignment->course?->title ?? 'دوره حذف شده' }} · {{ $assignment->classroom?->title ?? 'کلاس نامشخص' }}</p>
                            <p class="mt-1 text-xs text-slate-500">مدرس مسئول: {{ $assignment->teacher?->name ?? 'حساب مدرس در دسترس نیست' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $assignment->due_at ? 'مهلت '.\App\Support\PersianUi::date($assignment->due_at).' · '.\App\Support\PersianUi::time($assignment->due_at) : 'بدون مهلت مشخص' }} · نمره کل {{ \App\Support\PersianUi::digits($assignment->max_score ?? 20) }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-600">{{ $statusLabels[$assignment->status] ?? $assignment->status }}</span>
                            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-bold text-slate-600">{{ \App\Support\PersianUi::digits($assignment->submitted_count) }} ارسال</span>
                            <span class="rounded-full {{ $assignment->pending_review_count ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1.5 text-xs font-bold">{{ \App\Support\PersianUi::digits($assignment->pending_review_count) }} در انتظار تصحیح</span>
                            <a href="{{ route('owner.assignments.show', [$academy, $assignment]) }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-[var(--panel-primary)]">بررسی پاسخ‌ها ←</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center">
                    <strong class="block text-sm font-black">تکلیفی با این شرایط پیدا نشد.</strong>
                    <p class="mt-2 text-xs leading-6 text-slate-500">فیلترها را پاک کن یا برای یکی از کلاس‌های دارای مدرس، تکلیف جدید بساز.</p>
                    <a href="{{ route('owner.assignments.create', $academy) }}" class="mt-4 inline-block rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">ساخت تکلیف</a>
                </div>
            @endforelse
        </div>

        @if($assignments->hasPages())
            <div class="mt-5 border-t border-slate-100 pt-4 text-sm">{{ $assignments->links('components.navigation.pagination') }}</div>
        @endif
    </section>
</div>
@endsection
