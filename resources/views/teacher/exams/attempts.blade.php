@extends('layouts.teacher')
@section('title','نتایج آزمون | شیخان')
@section('header-title','نتایج آزمون')
@section('content')
<div class="grid gap-5">
    <div class="dashboard-panel p-5"><p class="text-xs font-black text-[var(--panel-primary)]">آزمون</p><h2 class="mt-1 text-2xl font-black">{{ $exam->title }}</h2><p class="mt-2 text-sm text-slate-500">{{ $exam->questions->count() }} سؤال · {{ $exam->duration_minutes }} دقیقه</p></div>
    <div class="grid gap-4">
        @forelse($exam->attempts as $attempt)
            <article class="dashboard-panel p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between"><div><h3 class="text-sm font-black">{{ $attempt->student?->name }}</h3><p class="mt-1 text-[10px] text-slate-500">ارسال: {{ $attempt->submitted_at?->format('Y/m/d H:i') ?? 'در حال انجام' }}</p><p class="mt-2 text-xs">وضعیت: {{ $attempt->status }} · نمره: {{ $attempt->score ?? '—' }}</p></div>
                @if(in_array($attempt->status, ['submitted','needs_review'], true))
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('teacher.exam-attempts.grade',$attempt) }}">@csrf<button class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs font-bold">تصحیح خودکار</button></form>
                        <a href="#attempt-{{ $attempt->id }}" class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">تصحیح دستی</a>
                    </div>
                @endif</div>
                @if($attempt->answers->isNotEmpty())
                    <div class="mt-5 grid gap-2">@foreach($attempt->answers as $answer)<div class="rounded-xl bg-slate-50 p-3 text-xs"><strong>سؤال {{ $loop->iteration }}:</strong> {{ is_array($answer->answer) ? implode('، ', $answer->answer) : $answer->answer }} @if($answer->is_correct !== null)<span class="{{ $answer->is_correct ? 'text-emerald-600' : 'text-rose-600' }}"> · {{ $answer->is_correct ? 'صحیح' : 'غلط' }}</span>@endif</div>@endforeach</div>
                @endif

                @if(in_array($attempt->status, ['submitted','needs_review'], true))
                    <form id="attempt-{{ $attempt->id }}" method="POST" action="{{ route('teacher.exam-attempts.grade-manual',$attempt) }}" class="mt-5 rounded-2xl border border-slate-200 p-4">
                        @csrf @method('PATCH')
                        <div class="grid gap-3">
                            @foreach($attempt->answers as $answer)
                                <label class="grid gap-2 rounded-xl bg-slate-50 p-3">
                                    <span class="text-xs font-bold">سؤال {{ $loop->iteration }} · سقف {{ $answer->question?->score ?? '—' }}</span>
                                    <span class="text-[10px] leading-6 text-slate-600">{{ is_array($answer->answer) ? implode('، ', $answer->answer) : ($answer->answer ?: 'بدون پاسخ') }}</span>
                                    <input type="number" min="0" max="{{ $answer->question?->score ?? 999999 }}" step="0.01" name="answers[{{ $answer->id }}]" value="{{ $answer->score }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs">
                                </label>
                            @endforeach
                        </div>
                        <button class="mt-4 rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white">ثبت نمره‌های دستی</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="dashboard-panel p-10 text-center text-sm text-slate-500">هنوز تلاشی برای این آزمون ثبت نشده است.</div>
        @endforelse
    </div>
</div>
@endsection