@extends('layouts.student')

@section('title','نتیجه آزمون | شیخان')
@section('header-title','نتیجه آزمون')

@section('content')
@php
    $maximumScore = (float) $exam->questions->sum('score');
@endphp

<div class="student-exam-results-page">
    <header class="student-workspace-head">
        <div class="student-workspace-head-copy">
            <span class="student-workspace-kicker">کارنامهٔ آزمون</span>
            <h1 class="student-workspace-title">{{ $exam->title }}</h1>
            <p class="student-workspace-description">{{ $exam->course?->title }} · نتیجهٔ هر تلاش و وضعیت تصحیح را اینجا می‌بینی.</p>
        </div>
        <a class="student-workspace-btn secondary" href="{{ $exam->status === 'published' ? route('student.exams.show', $exam) : route('student.results.index') }}">{{ $exam->status === 'published' ? 'بازگشت به آزمون' : 'بازگشت به کارنامه' }}</a>
    </header>

    <section class="student-exam-results-card" aria-labelledby="exam-result-title">
        <header class="student-exam-results-card-head">
            <div>
                <span class="student-exam-results-kicker">گزارش عملکرد</span>
                <h2 id="exam-result-title">تلاش‌های شما</h2>
                <p>نمرهٔ نهایی فقط بعد از تکمیل تصحیح نمایش داده می‌شود. اگر آزمون تشریحی باشد، تا بررسی مدرس در انتظار می‌ماند.</p>
            </div>
            <span class="student-exam-results-count">{{ \App\Support\PersianUi::digits($attempts->count()) }} تلاش</span>
        </header>

        <div class="student-exam-results-list">
            @forelse($attempts as $attempt)
                @php
                    $isFinal = $attempt->status === 'graded' && $attempt->score !== null;
                    $isManualReview = in_array($attempt->status, ['pending_review', 'needs_review', 'submitted'], true);
                    $status = match($attempt->status) {
                        'graded' => ['label' => 'تصحیح کامل شد', 'tone' => 'success'],
                        'needs_review' => ['label' => 'نیازمند بررسی تشریحی', 'tone' => 'warning'],
                        'pending_review' => ['label' => 'در صف تصحیح مدرس', 'tone' => 'warning'],
                        'submitted' => ['label' => 'ارسال شده', 'tone' => 'info'],
                        default => ['label' => 'در حال انجام', 'tone' => 'muted'],
                    };
                    $scorePercent = $isFinal && $maximumScore > 0
                        ? max(0, min(100, ((float) $attempt->score / $maximumScore) * 100))
                        : null;
                @endphp

                <article class="student-exam-result-attempt">
                    <div class="student-exam-result-attempt-number">
                        <span>تلاش</span>
                        <strong>{{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</strong>
                    </div>

                    <div class="student-exam-result-main">
                        <div class="student-exam-result-topline">
                            <strong>{{ $isFinal ? 'نمرهٔ نهایی' : ($attempt->status === 'needs_review' && $attempt->score !== null ? 'نمرهٔ موقت تستی' : 'نمره') }}</strong>
                            <span class="student-exam-result-status {{ $status['tone'] }}">{{ $status['label'] }}</span>
                        </div>

                        @if($isFinal)
                            <div class="student-exam-result-scoreline">
                                <strong>{{ \App\Support\PersianUi::digits($attempt->score) }}</strong>
                                @if($maximumScore > 0)
                                    <span>از {{ \App\Support\PersianUi::digits($maximumScore) }} نمره</span>
                                    <span class="student-exam-result-percent">{{ \App\Support\PersianUi::digits((int) round($scorePercent)) }}٪</span>
                                @endif
                            </div>
                            @if($scorePercent !== null)
                                <div class="student-exam-result-progress" role="progressbar" aria-label="درصد نمرهٔ نهایی" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) round($scorePercent) }}">
                                    <span style="width: {{ $scorePercent }}%"></span>
                                </div>
                            @endif
                            <p class="student-exam-result-help">این نتیجه نهایی است و تمام سؤال‌ها در محاسبهٔ نمره در نظر گرفته شده‌اند.</p>
                        @elseif($attempt->status === 'needs_review' && $attempt->score !== null)
                            <div class="student-exam-result-scoreline is-provisional">
                                <strong>{{ \App\Support\PersianUi::digits($attempt->score) }}</strong>
                                <span>نمرهٔ موقت از سؤال‌های قابل تصحیح خودکار</span>
                            </div>
                            <p class="student-exam-result-help">این نمره نهایی نیست؛ پس از تصحیح پاسخ‌های تشریحی، نتیجه به‌روزرسانی می‌شود.</p>
                        @else
                            <p class="student-exam-result-help">{{ $attempt->status === 'in_progress' ? 'این برگه هنوز تحویل نشده است.' : 'پاسخ‌ها ثبت شده‌اند؛ بعد از تکمیل تصحیح، نمرهٔ نهایی همین‌جا نمایش داده می‌شود.' }}</p>
                        @endif

                        <span class="student-exam-result-date">
                            {{ $attempt->submitted_at ? 'تحویل در ' . \App\Support\PersianUi::date($attempt->submitted_at) : ($attempt->started_at ? 'شروع در ' . \App\Support\PersianUi::date($attempt->started_at) : 'تاریخ ثبت نشده') }}
                        </span>
                    </div>
                </article>
            @empty
                <div class="student-workspace-empty">
                    <strong>هنوز تلاشی برای این آزمون ثبت نشده است.</strong>
                    <span>بعد از شروع و تحویل برگه، وضعیت نتیجه را در همین بخش می‌بینی.</span>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
