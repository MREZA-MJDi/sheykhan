@extends('layouts.owner')

@section('title', $exam->title . ' | نتیجه آزمون')
@section('header-title', 'جزئیات آزمون')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('owner.exams.index', $academy) }}" class="text-xs font-bold text-[var(--panel-primary)]">← بازگشت به آزمون‌ها</a>
            <h1 class="mt-2 break-words text-2xl font-black">{{ $exam->title }}</h1>
            <p class="mt-2 text-sm leading-7 text-slate-500">{{ $exam->course?->title }} · {{ $exam->classroom?->title ?: 'همه کلاس‌های دوره' }} · مدرس: {{ $exam->teacher?->name ?: '—' }}</p>
        </div>
        @if(auth()->user()->hasPermission('exams.manage') && (int) $exam->attempts_count === 0)
            <a href="{{ route('owner.exams.edit', [$academy, $exam->id]) }}" class="inline-flex min-h-10 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">ویرایش مشخصات و سؤال‌ها</a>
        @endif
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
        <div class="border-b border-slate-100 p-5"><h2 class="font-black">سؤال‌های آزمون</h2><p class="mt-1 text-sm text-slate-500">برای حفظ سابقه نمره‌دهی، پس از اولین تلاش ویرایش سؤال‌ها قفل می‌شود.</p></div>
        <div class="divide-y divide-slate-100">
            @forelse($exam->questions as $question)
                <article class="flex flex-col gap-2 p-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-[var(--panel-primary)]">سؤال {{ \App\Support\PersianUi::digits($loop->iteration) }} · {{ ['text' => 'تشریحی', 'single' => 'تک‌گزینه‌ای', 'multiple' => 'چندپاسخی', 'checkbox' => 'چندپاسخی'][$question->type] ?? $question->type }}</p>
                        <h3 class="mt-1 whitespace-pre-line break-words text-sm font-bold leading-7">{{ $question->question }}</h3>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ \App\Support\PersianUi::digits($question->score) }} امتیاز</span>
                </article>
            @empty
                <div class="p-6 text-center text-sm text-slate-500">هنوز سؤالی برای این آزمون تعریف نشده است.</div>
            @endforelse
        </div>
    </section>

    <section class="dashboard-panel overflow-hidden">
        <div class="border-b border-slate-100 p-5"><h2 class="font-black">نتایج و تلاش‌های دانش‌آموزان</h2><p class="mt-1 text-sm text-slate-500">نمره‌ی خالی به معنی نمره‌ی صفر نیست؛ ممکن است تلاش هنوز تصحیح نشده باشد.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-right">
                <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">دانش‌آموز</th><th class="px-4 py-3">شماره تلاش</th><th class="px-4 py-3">شروع</th><th class="px-4 py-3">ارسال</th><th class="px-4 py-3">نمره</th><th class="px-4 py-3">وضعیت</th><th class="px-4 py-3">تصحیح دستی</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attempts as $attempt)
                        <tr><td class="px-4 py-4 text-sm font-bold">{{ $attempt->student?->name ?: 'حساب حذف‌شده' }}</td><td class="px-4 py-4 text-sm">{{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</td><td class="px-4 py-4 text-xs">{{ $attempt->started_at ? \App\Support\PersianUi::date($attempt->started_at) . ' ' . \App\Support\PersianUi::time($attempt->started_at) : '—' }}</td><td class="px-4 py-4 text-xs">{{ $attempt->submitted_at ? \App\Support\PersianUi::date($attempt->submitted_at) . ' ' . \App\Support\PersianUi::time($attempt->submitted_at) : 'در جریان' }}</td><td class="px-4 py-4 text-sm font-black">{{ $attempt->score === null ? 'تصحیح نشده' : \App\Support\PersianUi::digits($attempt->score) }}</td><td class="px-4 py-4 text-xs">{{ $attempt->status }}</td>
<td class="px-4 py-4 align-top text-xs">
    @if(auth()->user()->hasPermission('exams.manage') && $attempt->submitted_at && $attempt->answers->isNotEmpty())
        <details class="min-w-64">
            <summary class="cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2 font-black text-[var(--panel-primary)]">ثبت / اصلاح نمره</summary>
            <form method="POST" action="{{ route('owner.exams.attempts.grade', [$academy, $exam->id, $attempt->id]) }}" class="mt-3 grid gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                @csrf @method('PATCH')
                @foreach($attempt->answers as $answer)
                    <label class="grid gap-1">
                        <span class="font-bold leading-6">{{ $answer->question?->question ?? 'سؤال حذف‌شده' }}</span>
                        <span class="text-slate-500">پاسخ: {{ is_array($answer->answer) ? implode('، ', $answer->answer) : ($answer->answer ?: 'بدون پاسخ') }}</span>
                        <span class="text-slate-500">حداکثر {{ \App\Support\PersianUi::digits($answer->question?->score ?? 0) }} امتیاز</span>
                        <input type="number" name="scores[{{ $answer->id }}]" min="0" max="{{ $answer->question?->score ?? 0 }}" step="0.01" value="{{ old('scores.'.$answer->id, $answer->score) }}" required class="min-h-9 w-full rounded-lg border border-slate-200 bg-white px-2 text-sm">
                    </label>
                @endforeach
                <button type="submit" class="min-h-10 rounded-lg bg-[var(--panel-primary)] px-3 font-black text-white">ذخیره نمره‌ها</button>
            </form>
        </details>
    @else
        <span class="text-slate-400">{{ $attempt->submitted_at ? 'پاسخی برای تصحیح وجود ندارد' : 'پس از ارسال فعال می‌شود' }}</span>
    @endif
</td></tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">هنوز تلاشی برای این آزمون ثبت نشده است.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $attempts->links() }}</div>
    </section>
</div>
@endsection
