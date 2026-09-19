@extends('layouts.teacher')
@section('title','بررسی تکلیف | شیخان')
@section('header-title','بررسی پاسخ‌ها')
@section('content')
<div class="grid gap-5">
    <div class="dashboard-panel p-5"><p class="text-xs font-black text-[var(--panel-primary)]">تکلیف</p><h2 class="mt-1 text-2xl font-black">{{ $assignment->title }}</h2><p class="mt-2 text-sm text-slate-500">{{ $assignment->classroom?->title ?? 'دوره' }} · نمره کل {{ $assignment->max_score ?? '—' }}</p></div>
    <div class="grid gap-4">
        @forelse($assignment->submissions as $submission)
            <article class="dashboard-panel p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0"><h3 class="text-sm font-black">{{ $submission->student?->name }}</h3><p class="mt-1 text-[10px] text-slate-500">تحویل: {{ $submission->submitted_at?->format('Y/m/d H:i') ?? '—' }}</p><div class="mt-4 whitespace-pre-line rounded-xl bg-slate-50 p-4 text-sm leading-7">{{ $submission->content ?: 'پاسخ متنی ثبت نشده است.' }}</div></div>
                    <form method="POST" action="{{ route('teacher.assignments.submissions.update',[$assignment,$submission]) }}" class="w-full lg:max-w-xs space-y-3">@csrf @method('PATCH')<label class="grid gap-2"><span class="text-xs font-bold">نمره</span><input type="number" step="0.01" min="0" max="{{ $assignment->max_score ?? 999999 }}" name="score" value="{{ $submission->score }}" class="rounded-xl border border-slate-200 px-3 py-3"></label><label class="grid gap-2"><span class="text-xs font-bold">بازخورد</span><textarea name="feedback" rows="5" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">{{ $submission->feedback }}</textarea></label><button class="w-full rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">ثبت تصحیح</button></form>
                </div>
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500">هنوز پاسخی برای این تکلیف ثبت نشده است.</div>
        @endforelse
    </div>
</div>
@endsection