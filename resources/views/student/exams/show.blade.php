@extends('layouts.student')

@section('title',$exam->title.' | شیخان')
@section('header-title','آزمون')

@section('content')
<div class="student-dashboard">
<section class="student-panel dashboard-panel" aria-labelledby="exam-title">
    <div class="student-panel-head">
        <div>
            <span class="student-kicker" style="color:var(--panel-primary)">آزمون</span>
            <h2 id="exam-title">{{ $exam->title }}</h2>
            <p>{{ $exam->course?->title }} @if($exam->classroom) · {{ $exam->classroom->title }} @endif</p>
        </div>
        <span class="student-status primary">{{ \App\Support\PersianUi::digits($exam->questions->count()) }} سؤال</span>
    </div>
    @if($exam->description)<article class="student-lesson-content">{{ $exam->description }}</article>@endif
    <div class="student-stats" style="margin-top:16px">
        <article class="student-stat"><span>مدت</span><strong>{{ $exam->duration_minutes ? \App\Support\PersianUi::digits($exam->duration_minutes) : '∞' }}</strong><small>دقیقه</small></article>
        <article class="student-stat"><span>دفعات مجاز</span><strong>{{ \App\Support\PersianUi::digits($exam->attempts_allowed) }}</strong><small>تلاش</small></article>
        <article class="student-stat"><span>شروع</span><strong>{{ $exam->starts_at ? \App\Support\PersianUi::date($exam->starts_at) : 'اکنون' }}</strong><small>بازه آزمون</small></article>
    </div>

    <div class="student-list">
        @forelse($exam->attempts as $attempt)
            <div class="student-list-row">
                <div class="student-date"><strong>{{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</strong><small>تلاش</small></div>
                <div class="student-row-content"><div class="student-row-title">تلاش {{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</div><span class="student-row-meta">{{ $attempt->submitted_at ? 'ارسال‌شده' : 'در حال انجام' }}</span></div>
                <span class="student-status {{ $attempt->status==='graded' ? 'success' : 'warning' }}">{{ $attempt->status==='graded' ? 'نمره ثبت شد' : ($attempt->status==='in_progress' ? 'در حال انجام' : 'در انتظار بررسی') }}</span>
            </div>
        @empty
            <div class="student-empty"><strong>هنوز تلاشی ثبت نشده است.</strong><span>در صورت باز بودن آزمون، می‌توانید اولین تلاش را شروع کنید.</span></div>
        @endforelse
    </div>

    @php($open=($exam->status==='published') && (!$exam->starts_at || now()->gte($exam->starts_at)) && (!$exam->ends_at || now()->lte($exam->ends_at)))
    @if($open)
        <form method="POST" action="{{ route('student.exams.start',$exam) }}" class="student-form">
            @csrf
            <button type="submit" class="student-welcome-action primary">شروع / ادامه آزمون ←</button>
        </form>
    @else
        <span class="student-status warning" style="margin-top:16px">آزمون در این لحظه باز نیست.</span>
    @endif
</section>
</div>
@endsection
