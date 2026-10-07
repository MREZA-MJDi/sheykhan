@extends('layouts.student')

@section('title','نتیجه آزمون | شیخان')
@section('header-title','نتیجه آزمون')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="exam-result-title">
    <div class="student-panel-head">
        <div><span class="student-kicker" style="color:var(--panel-primary)">نتیجه</span><h2 id="exam-result-title">{{ $exam->title }}</h2><p>{{ $exam->course?->title }}</p></div>
    </div>
    <div class="student-list">
        @forelse($attempts as $attempt)
            <article class="student-list-row">
                <div class="student-date"><strong>{{ $attempt->score !== null ? \App\Support\PersianUi::digits($attempt->score) : '—' }}</strong><small>نمره</small></div>
                <div class="student-row-content"><div class="student-row-title">تلاش {{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</div><span class="student-row-meta">{{ $attempt->submitted_at ? \App\Support\PersianUi::date($attempt->submitted_at) : 'ارسال نشده' }}</span></div>
                <span class="student-status {{ $attempt->status==='graded' ? 'success' : 'warning' }}">{{ $attempt->status==='graded' ? 'تصحیح‌شده' : 'در انتظار بررسی' }}</span>
            </article>
        @empty
            <div class="student-empty"><strong>نتیجه‌ای ثبت نشده است.</strong></div>
        @endforelse
    </div>
</section>
@endsection
