@extends('layouts.teacher')
@section('title','تکالیف | شیخان')
@section('header-title','تکالیف')
@section('content')
<div class="grid gap-5">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-black text-[var(--panel-primary)]">ارزیابی</p><h2 class="mt-1 text-2xl font-black">تکالیف</h2></div><a href="{{ route('teacher.assignments.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-[var(--panel-primary)] px-4 text-sm font-black text-white">+ تکلیف جدید</a></div>
    <div class="grid gap-4">
        @forelse($assignments as $assignment)
            <article class="dashboard-panel p-5"><div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"><div><h3 class="text-sm font-black">{{ $assignment->title }}</h3><p class="mt-1 text-xs text-slate-500">{{ $assignment->classroom?->title ?? 'عمومی دوره' }} · موعد {{ $assignment->due_at?->format('Y/m/d H:i') ?? 'بدون موعد' }}</p></div><div class="flex flex-wrap items-center gap-2"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px]">{{ $assignment->submitted_count }} تحویل</span><span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] text-amber-700">{{ $assignment->pending_review_count }} منتظر تصحیح</span><a href="{{ route('teacher.assignments.submissions',$assignment) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold">بررسی پاسخ‌ها</a></div></div></article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500">تکلیفی ثبت نشده است.</div>
        @endforelse
    </div>
</div>
@endsection