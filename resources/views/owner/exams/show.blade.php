@extends('layouts.owner')

@section('title', $exam->title . ' | نتیجه آزمون')
@section('header-title', 'جزئیات آزمون')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div><a href="{{ route('owner.exams.index', $academy) }}" class="text-xs font-bold text-[var(--panel-primary)]">← بازگشت به آزمون‌ها</a><h1 class="mt-2 break-words text-2xl font-black">{{ $exam->title }}</h1><p class="mt-2 text-sm leading-7 text-slate-500">{{ $exam->course?->title }} · {{ $exam->classroom?->title ?: 'همه کلاس‌های دوره' }} · مدرس: {{ $exam->teacher?->name ?: '—' }}</p></div>
        <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-bold">{{ ['draft' => 'پیش‌نویس', 'published' => 'منتشرشده', 'closed' => 'بسته‌شده'][$exam->status] ?? $exam->status }}</span>
    </header>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <article class="owner-stat-card"><span>تعداد سؤال‌ها</span><strong>{{ \App\Support\PersianUi::digits($exam->questions->count()) }}</strong><small>ثبت‌شده در آزمون</small></article>
        <article class="owner-stat-card"><span>تلاش‌ها</span><strong>{{ \App\Support\PersianUi::digits($exam->attempts_count) }}</strong><small>همه تلاش‌ها</small></article>
        <article class="owner-stat-card"><span>ارسال‌شده</span><strong>{{ \App\Support\PersianUi::digits($summary['submitted']) }}</strong><small>پایان‌یافته</small></article>
        <article class="owner-stat-card"><span>میانگین نمره ثبت‌شده</span><strong>{{ $summary['average'] === null ? '—' : \App\Support\PersianUi::digits(number_format((float) $summary['average'], 2)) }}</strong><small>فقط تلاش‌های دارای نمره</small></article>
    </section>

    @if(auth()->user()->hasPermission('exams.manage'))
        <form method="POST" action="{{ route('owner.exams.status', [$academy, $exam->id]) }}" class="dashboard-panel flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
            @csrf @method('PATCH')
            <div class="flex-1"><h2 class="font-black">مدیریت وضعیت انتشار</h2><p class="mt-1 text-xs leading-6 text-slate-500">آزمونی که سؤال ندارد منتشر نمی‌شود. تغییر وضعیت، سؤال‌ها یا نتیجه‌های ثبت‌شده را حذف نمی‌کند.</p></div>
            <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-11 min-w-44 rounded-xl border border-slate-200 px-3 text-sm"><option value="draft" @selected($exam->status === 'draft')>پیش‌نویس</option><option value="published" @selected($exam->status === 'published')>منتشرشده</option><option value="closed" @selected($exam->status === 'closed')>بسته‌شده</option></select></label>
            <button class="min-h-11 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">ذخیره وضعیت</button>
        </form>
    @endif

    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-100 p-5"><h2 class="font-black">نتایج و تلاش‌های دانش‌آموزان</h2><p class="mt-1 text-sm text-slate-500">نمره‌ی خالی به معنی نمره‌ی صفر نیست؛ ممکن است تلاش هنوز تصحیح نشده باشد.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-right">
                <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">دانش‌آموز</th><th class="px-4 py-3">شماره تلاش</th><th class="px-4 py-3">شروع</th><th class="px-4 py-3">ارسال</th><th class="px-4 py-3">نمره</th><th class="px-4 py-3">وضعیت</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attempts as $attempt)
                        <tr><td class="px-4 py-4 text-sm font-bold">{{ $attempt->student?->name ?: 'حساب حذف‌شده' }}</td><td class="px-4 py-4 text-sm">{{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</td><td class="px-4 py-4 text-xs">{{ $attempt->started_at ? \App\Support\PersianUi::date($attempt->started_at) . ' ' . \App\Support\PersianUi::time($attempt->started_at) : '—' }}</td><td class="px-4 py-4 text-xs">{{ $attempt->submitted_at ? \App\Support\PersianUi::date($attempt->submitted_at) . ' ' . \App\Support\PersianUi::time($attempt->submitted_at) : 'در جریان' }}</td><td class="px-4 py-4 text-sm font-black">{{ $attempt->score === null ? 'تصحیح نشده' : \App\Support\PersianUi::digits($attempt->score) }}</td><td class="px-4 py-4 text-xs">{{ $attempt->status }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">هنوز تلاشی برای این آزمون ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $attempts->links() }}</div>
    </section>
</div>
@endsection
