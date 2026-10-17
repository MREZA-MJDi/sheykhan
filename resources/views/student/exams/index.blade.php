@extends('layouts.student')
@section('title','آزمون‌ها | شیخان')
@section('header-title','آزمون‌ها')
@section('content')
<div class="student-workspace-page">
<header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">ارزیابی</span><h1 class="student-workspace-title">آزمون‌های من</h1><p class="student-workspace-description">آزمون‌های منتشرشده در دوره‌ها و کلاس‌های مجاز شما؛ زمان و دسترسی نهایی در سرور کنترل می‌شود.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.results.index') }}">نتایج من</a></header>
<section class="student-workspace-card" aria-labelledby="exams-title">
<div class="student-workspace-card-head"><div><h2 id="exams-title">آزمون‌های فعال</h2><p>آزمون را انتخاب کن تا قبل از شروع جزئیات آن را ببینی.</p></div></div>
<div class="student-workspace-list">
@forelse($exams as $exam)
<article class="student-workspace-row">
<div class="student-workspace-date"><strong>{{ App\Support\PersianUi::digits($exam->attempts_allowed) }}</strong><small>دفعات</small></div>
<div class="student-workspace-row-main">
<a href="{{ route('student.exams.show',$exam) }}">{{ $exam->title }}</a>
<span>{{ $exam->course?->title }} · {{ $exam->duration_minutes ? App\Support\PersianUi::digits($exam->duration_minutes).' دقیقه' : 'بدون محدودیت زمانی' }}</span>
@if($exam->classroom)<span>کلاس: {{ $exam->classroom->title }}</span>@endif
@if($exam->starts_at)<span>شروع: {{ App\Support\PersianUi::date($exam->starts_at) }} · {{ App\Support\PersianUi::time($exam->starts_at) }}</span>@endif
</div>
<div class="student-workspace-actions">
@if($exam->starts_at && now()->lt($exam->starts_at))
<span class="student-workspace-status warning">هنوز باز نشده</span>
@elseif($exam->ends_at && now()->gt($exam->ends_at))
<span class="student-workspace-status warning">مهلت تمام شده</span>
@else
<span class="student-workspace-status primary">آماده بررسی</span>
@endif
<a class="student-workspace-btn primary" href="{{ route('student.exams.show',$exam) }}">جزئیات و شروع آزمون</a>
</div>
</article>
@empty<div class="student-workspace-empty"><strong>آزمون فعالی ندارید.</strong><span>با انتشار آزمون جدید، اینجا قرار می‌گیرد.</span></div>@endforelse
</div>
@if($exams->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی آزمون‌ها">{{ $exams->links() }}</nav>@endif
</section></div>
@endsection