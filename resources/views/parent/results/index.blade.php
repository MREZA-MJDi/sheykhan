@extends('layouts.parent')

@section('title', 'نتایج آموزشی فرزندان | شیخان')
@section('header-title', 'نتایج آموزشی')

@section('content')
<div class="parent-dashboard space-y-6">
    <header class="parent-panel dashboard-panel p-5 sm:p-7">
        <p class="parent-kicker" style="color:var(--panel-primary)">نتیجه و بازخورد</p>
        <h1 class="mt-1 text-2xl font-black">نتیجهٔ تکالیف و آزمون‌ها</h1>
        <p class="mt-2 text-sm leading-7 text-slate-500">فقط تکالیف تصحیح‌شده و تلاش‌های ثبت‌شدهٔ آزمون برای دانش‌آموزان متصل به این حساب نمایش داده می‌شود.</p>
    </header>
    <section class="space-y-3">
        @forelse($results as $result)
            <article class="parent-panel dashboard-panel flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full {{ $result->result_type === 'exam' ? 'bg-violet-50 text-violet-700' : 'bg-sky-50 text-sky-700' }} px-2.5 py-1 text-[11px] font-black">{{ $result->result_type === 'exam' ? 'آزمون' : 'تکلیف' }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold">{{ $result->status_label }}</span>
                    </div>
                    <h2 class="mt-2 break-words text-base font-black">{{ $result->title }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $result->student_name }} · {{ \App\Support\PersianUi::date($result->occurred_at) }}</p>
                    @if(filled($result->feedback ?? null))
                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600">بازخورد مدرس: {{ $result->feedback }}</p>
                    @endif
                </div>
                <div class="shrink-0 text-right">
                    <small class="block text-xs text-slate-500">نمره</small>
                    <strong class="text-xl font-black">{{ $result->score === null ? '—' : \App\Support\PersianUi::digits($result->score) }}</strong>
                </div>
            </article>
        @empty
            <div class="parent-panel dashboard-panel p-8 text-center"><h2 class="font-black">نتیجه‌ای برای نمایش ثبت نشده است.</h2><p class="mt-2 text-sm text-slate-500">پس از تصحیح تکلیف یا ثبت نتیجهٔ آزمون، موارد مجاز در این صفحه قرار می‌گیرند.</p></div>
        @endforelse
        <div>{{ $results->links() }}</div>
    </section>
</div>
@endsection
