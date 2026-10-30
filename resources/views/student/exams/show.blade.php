@extends('layouts.student')

@section('title', $exam->title)
@section('header-title', 'جزئیات آزمون')

@section('content')
@php
    $latestAttempt = $exam->attempts->first();
    $open = $exam->status === 'published' && (!$exam->starts_at || $exam->starts_at->lte(now())) && (!$exam->ends_at || $exam->ends_at->gte(now()));
@endphp
<section class="page">
    <div class="card">
        <div class="chead"><h2>{{ $exam->title }}</h2><a class="pill" href="{{ route('student.exams.index') }}">بازگشت به آزمون‌ها</a></div>
        <div class="sum">
            <span class="tag">{{ $exam->course?->title ?? 'دوره' }}</span>
            @if($exam->classroom)<span class="tag">{{ $exam->classroom->title }}</span>@endif
            <span class="tag">{{ $exam->duration_minutes ? \App\Support\PersianUi::digits($exam->duration_minutes).' دقیقه' : 'بدون محدودیت زمانی' }}</span>
            <span class="tag">{{ \App\Support\PersianUi::digits($exam->attempts_allowed) }} بار مجاز</span>
        </div>
        <p class="note">{{ $exam->description ?: 'برای این آزمون توضیح اضافه‌ای ثبت نشده است.' }}</p>
        @php($examHandouts = $exam->media->filter(fn ($file) => $file->pivot->collection === 'exam-handout'))
        @if($examHandouts->isNotEmpty())
            <div class="exam-handout-list">
                <strong>فایل‌های راهنمای آزمون</strong>
                @foreach($examHandouts as $handout)
                    <a href="{{ route('media.view', $handout) }}" target="_blank" rel="noopener noreferrer">
                        <span>{{ strtoupper($handout->extension ?: 'FILE') }}</span>
                        <span><b>{{ $handout->original_name }}</b><small>{{ App\Support\PersianUi::number((int) ceil(((int) $handout->size) / 1024)) }} کیلوبایت</small></span>
                    </a>
                @endforeach
            </div>
        @endif
        <div class="frm">
            <span>شروع: {{ $exam->starts_at ? \App\Support\PersianUi::dateTime($exam->starts_at) : 'بدون زمان شروع مشخص' }}</span>
            <span>پایان: {{ $exam->ends_at ? \App\Support\PersianUi::dateTime($exam->ends_at) : 'بدون زمان پایان مشخص' }}</span>
        </div>
    </div>

    @if($latestAttempt)
        <div class="card">
            <div class="chead"><h2>آخرین تلاش تو</h2><span class="tag {{ $latestAttempt->status === 'graded' ? 'ok' : 'd' }}">
                {{ match($latestAttempt->status) { 'in_progress' => 'در حال پاسخ‌گویی', 'graded' => 'تصحیح‌شده', 'needs_review' => 'نمرهٔ موقت', 'pending_review' => 'در صف تصحیح', 'submitted' => 'ارسال‌شده', default => $latestAttempt->status } }}
            </span></div>
            @if($latestAttempt->score !== null)<p class="note">نمره: {{ \App\Support\PersianUi::number($latestAttempt->score, 1) }}</p>@endif
            @if($latestAttempt->status === 'in_progress')
                <a class="btn s" href="{{ route('student.exams.attempt', ['exam' => $exam->id, 'attempt' => $latestAttempt->id]) }}">ادامه آزمون</a>
            @elseif($latestAttempt->submitted_at)
                <p class="note">زمان ارسال: {{ \App\Support\PersianUi::dateTime($latestAttempt->submitted_at) }}</p>
            @endif
        </div>
    @endif

    @if($latestAttempt && $latestAttempt->status !== 'in_progress' && in_array($latestAttempt->status, ['graded','needs_review','pending_review','submitted'], true))
        <div class="frm"><a class="btn s" href="{{ route('student.exams.result', $exam->id) }}">مشاهده نتیجه‌ها</a></div>
    @endif

    @if($open && (!$latestAttempt || $latestAttempt->status !== 'in_progress'))
        <form method="POST" action="{{ route('student.exams.start', $exam->id) }}" class="card">
            @csrf
            <p class="note">با شروع آزمون، زمان مجاز و تعداد تلاش‌های مجاز توسط سرور کنترل می‌شود.</p>
            <div class="frm"><button type="submit" class="btn">شروع آزمون</button></div>
        </form>
    @elseif(!$open)
        <div class="card"><p class="note">این آزمون در حال حاضر باز نیست.</p></div>
    @endif

    <div class="card">
        <div class="chead"><h2>سؤال‌های آزمون</h2><span class="pill">{{ \App\Support\PersianUi::digits($exam->questions->count()) }} سؤال</span></div>
        @forelse($exam->questions as $question)
            <div class="trk">
                <div class="th"><svg class="i" aria-hidden="true"><use href="#i-exam"/></svg></div>
                <div><b>{{ $question->question }}</b><span>{{ $question->type === 'text' ? 'پاسخ تشریحی' : 'سؤال چندگزینه‌ای' }} · {{ \App\Support\PersianUi::number($question->score, 1) }} نمره</span></div>
            </div>
        @empty
            <div class="empty">سؤال‌های آزمون هنوز منتشر نشده‌اند.</div>
        @endforelse
    </div>
</section>
@endsection
