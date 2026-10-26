@extends('layouts.student')

@section('title', 'نتیجهٔ آزمون')
@section('header-title', 'نتیجهٔ آزمون')

@section('content')
<section class="page">
    <div class="card">
        <div class="chead"><h2>{{ $exam->title }}</h2><div class="flex flex-wrap items-center gap-2"><a class="pill" href="{{ route('student.exams.result.export', ['exam' => $exam, 'format' => 'word']) }}">کارنامه Word</a><a class="pill" href="{{ route('student.exams.result.export', ['exam' => $exam, 'format' => 'csv']) }}">خروجی Excel / CSV</a><a class="pill" href="{{ route('student.exams.index') }}">بازگشت به آزمون‌ها</a></div></div>
        <div class="sum">
            <span class="tag">{{ $exam->course?->title ?? 'دوره' }}</span>
            <span class="tag">{{ \App\Support\PersianUi::digits($attempts->count()) }} تلاش ثبت‌شده</span>
        </div>
        @forelse($attempts as $attempt)
            <div class="trk">
                <div class="th"><svg class="i" aria-hidden="true"><use href="#i-exam"/></svg></div>
                <div>
                    <b>تلاش {{ \App\Support\PersianUi::digits($attempt->attempt_number) }}</b>
                    <span>
                        {{ match($attempt->status) { 'graded' => 'تصحیح‌شده', 'needs_review' => 'نمرهٔ موقت؛ در انتظار بررسی تشریحی', 'pending_review' => 'در صف تصحیح مدرس', 'submitted' => 'ارسال‌شده', 'in_progress' => 'در حال پاسخ‌گویی', default => $attempt->status } }}
                        @if($attempt->submitted_at) · {{ \App\Support\PersianUi::dateTime($attempt->submitted_at) }} @endif
                    </span>
                </div>
                @if($attempt->score !== null)
                    <span class="tag {{ $attempt->status === 'graded' ? 'ok' : 'w' }}">{{ \App\Support\PersianUi::number($attempt->score, 1) }}</span>
                @else
                    <span class="tag w">نمره ثبت نشده</span>
                @endif
            </div>
        @empty
            <div class="empty">هنوز تلاشی برای این آزمون ثبت نشده است.</div>
        @endforelse
    </div>
    <div class="card"><p class="note">پاسخ صحیح و کلید آزمون فقط در صورت ارائهٔ آن از طرف مدرس نمایش داده می‌شود؛ این صفحه فقط نتیجه‌های قابل‌مشاهدهٔ حساب خودت را نشان می‌دهد.</p></div>
</section>
@endsection
