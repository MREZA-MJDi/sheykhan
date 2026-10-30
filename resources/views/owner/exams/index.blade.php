@extends('layouts.owner')

@section('title', 'مدیریت آزمون‌ها | شیخان')
@section('header-title', 'مدیریت آزمون‌ها')

@section('content')
<div class="space-y-6 panel-page-enter owner-workspace-page owner-exams-page">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-black text-[var(--panel-primary)]">ارزیابی آموزشی</p><h1 class="mt-1 text-2xl font-black">آزمون‌ها و نتایج</h1><p class="mt-2 max-w-3xl text-sm leading-7 text-slate-500">آزمون‌های مربوط به همین آموزشگاه، وضعیت انتشار و تعداد تلاش‌های دانش‌آموزان را بررسی کنید. برای جزئیات و نمره‌ها، آزمون را باز کنید.</p></div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('exams.manage'))
                <a href="{{ route('owner.exams.create', $academy) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">+ ساخت آزمون</a>
            @endif
            <a href="{{ route('owner.dashboard') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold">بازگشت به داشبورد</a>
        </div>
    </header>

    <section class="grid gap-3 sm:grid-cols-3">
        <article class="owner-stat-card"><span>کل آزمون‌ها</span><strong>{{ \App\Support\PersianUi::digits($stats['all']) }}</strong><small>متعلق به این آموزشگاه</small></article>
        <article class="owner-stat-card"><span>منتشرشده</span><strong>{{ \App\Support\PersianUi::digits($stats['published']) }}</strong><small>قابل مشاهده طبق زمان‌بندی آزمون</small></article>
        <article class="owner-stat-card"><span>تلاش‌های ثبت‌شده</span><strong>{{ \App\Support\PersianUi::digits($stats['attempts']) }}</strong><small>شامل تلاش‌های ارسال‌شده و در جریان</small></article>
    </section>

    <form method="GET" class="dashboard-panel grid gap-3 p-4 sm:grid-cols-[1fr_200px_auto] sm:items-end">
        <label class="grid gap-1 text-xs font-bold">جست‌وجو<input name="q" value="{{ $filters['q'] ?? '' }}" class="min-h-10 rounded-xl border border-slate-200 px-3 text-sm" placeholder="عنوان آزمون یا دوره"></label>
        <label class="grid gap-1 text-xs font-bold">وضعیت<select name="status" class="min-h-10 rounded-xl border border-slate-200 px-3 text-sm"><option value="">همه وضعیت‌ها</option><option value="draft" @selected(($filters['status'] ?? '') === 'draft')>پیش‌نویس</option><option value="published" @selected(($filters['status'] ?? '') === 'published')>منتشرشده</option><option value="closed" @selected(($filters['status'] ?? '') === 'closed')>بسته‌شده</option></select></label>
        <button class="min-h-10 rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">اعمال فیلتر</button>
    </form>

    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-100 p-5"><h2 class="font-black">فهرست آزمون‌ها</h2><p class="mt-1 text-sm text-slate-500">برای تغییر وضعیت، وارد جزئیات آزمون شوید.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-right">
                <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">آزمون</th><th class="px-4 py-3">دوره / کلاس</th><th class="px-4 py-3">مدرس</th><th class="px-4 py-3">زمان آزمون</th><th class="px-4 py-3">تلاش‌ها</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">جزئیات</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($exams as $exam)
                        <tr>
                            <td class="px-4 py-4"><strong class="block text-sm">{{ $exam->title }}</strong><span class="mt-1 block text-xs text-slate-500">{{ $exam->duration_minutes ? \App\Support\PersianUi::digits($exam->duration_minutes) . ' دقیقه' : 'بدون محدودیت زمانی' }}</span></td>
                            <td class="px-4 py-4 text-sm">{{ $exam->course?->title }}<span class="mt-1 block text-xs text-slate-500">{{ $exam->classroom?->title ?: 'همه کلاس‌های دوره' }}</span></td>
                            <td class="px-4 py-4 text-sm">{{ $exam->teacher?->name ?: '—' }}</td>
                            <td class="px-4 py-4 text-xs leading-6">{{ $exam->starts_at ? \App\Support\PersianUi::date($exam->starts_at) . ' ' . \App\Support\PersianUi::time($exam->starts_at) : 'بدون زمان شروع' }}</td>
                            <td class="px-4 py-4 text-sm font-bold">{{ \App\Support\PersianUi::digits($exam->attempts_count) }}</td>
                            <td class="px-4 py-4 text-xs"><span class="rounded-full px-2.5 py-1.5 {{ $exam->status === 'published' ? 'bg-emerald-50 text-emerald-700' : ($exam->status === 'closed' ? 'bg-slate-100 text-slate-600' : 'bg-amber-50 text-amber-700') }}">{{ ['draft' => 'پیش‌نویس', 'published' => 'منتشرشده', 'closed' => 'بسته‌شده'][$exam->status] ?? $exam->status }}</span></td>
                            <td class="px-4 py-4"><a href="{{ route('owner.exams.show', [$academy, $exam->id]) }}" class="inline-flex min-h-9 items-center rounded-lg border border-slate-200 px-3 text-xs font-bold hover:bg-slate-50">مشاهده نتایج</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center"><p class="font-bold">آزمونی برای این آموزشگاه پیدا نشد.</p><p class="mt-2 text-sm text-slate-500">ممکن است هنوز آزمونی ساخته نشده یا فیلترها محدودکننده باشند.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $exams->links() }}</div>
    </section>
</div>
@endsection
