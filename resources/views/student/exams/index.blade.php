@extends('layouts.student')

@section('title','آزمون‌ها | شیخان')
@section('header-title','آزمون‌ها')

@section('content')
<section class="student-panel dashboard-panel" aria-labelledby="exams-title">
    <div class="student-panel-head">
        <div>
            <span class="student-kicker" style="color:var(--panel-primary)">ارزیابی</span>
            <h2 id="exams-title">آزمون‌های من</h2>
            <p>آزمون‌های منتشرشده در دوره‌ها و کلاس‌های مجاز شما.</p>
        </div>
    </div>
    <div class="student-list">
        @forelse($exams as $exam)
            <article class="student-list-row">
                <div class="student-date"><strong>{{ \App\Support\PersianUi::digits($exam->attempts_allowed) }}</strong><small>دفعات</small></div>
                <div class="student-row-content">
                    <a class="student-row-title" href="{{ route('student.exams.show',$exam) }}">{{ $exam->title }}</a>
                    <span class="student-row-meta">{{ $exam->course?->title }} · {{ $exam->duration_minutes ? \App\Support\PersianUi::digits($exam->duration_minutes).' دقیقه' : 'زمان متغیر' }}</span>
                </div>
                <span class="student-status {{ $exam->starts_at && now()->lt($exam->starts_at) ? 'warning' : 'primary' }}">
                    {{ $exam->starts_at && now()->lt($exam->starts_at) ? 'هنوز باز نشده' : 'بررسی' }}
                </span>
            </article>
        @empty
            <div class="student-empty"><strong>آزمون فعالی ندارید.</strong><span>با انتشار آزمون جدید، اینجا قرار می‌گیرد.</span></div>
        @endforelse
    </div>
    @if($exams->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی آزمون‌ها">{{ $exams->links() }}</nav>@endif
</section>
@endsection
