@extends('layouts.teacher')
@section('title','آزمون‌ها | شیخان')
@section('header-title','آزمون‌ها')
@section('content')
<div class="grid gap-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-black text-[var(--panel-primary)]">ارزیابی</p><h2 class="mt-1 text-2xl font-black">آزمون‌ها</h2><p class="mt-2 text-sm text-slate-500">آزمون بساز و تلاش‌های ارسال‌شده را بررسی کن.</p></div>
        <a href="{{ route('teacher.exams.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">+ آزمون جدید</a>
    </div>
    <div class="grid gap-4">
        @forelse($exams as $exam)
            <article class="dashboard-panel p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div><h3 class="text-sm font-black">{{ $exam->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $exam->classroom?->title ?? 'عمومی دوره' }} · {{ $exam->duration_minutes }} دقیقه</p></div>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px]">{{ $exam->submitted_attempts_count }} ارسال</span>
                        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] text-emerald-700">{{ $exam->graded_attempts_count }} تصحیح‌شده</span>
                        <a href="{{ route('teacher.exams.attempts',$exam) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">مشاهده نتایج</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500">آزمونی ثبت نشده است.</div>
        @endforelse
    </div>
</div>
@endsection