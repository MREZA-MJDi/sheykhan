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
<div class="student-workspace-row-main"><a href="{{ route('student.exams.show',$exam) }}">{{ $exam->title }}</a><span>{{ $exam->course?->title }} · {{ $exam->duration_minutes ? App\Support\PersianUi::digits($exam->duration_minutes).' دقیقه' : 'زمان متغیر' }}</span></div>
<span class="student-workspace-status {{ $exam->starts_at && now()->lt($exam->starts_at) ? 'warning' : 'primary' }}">{{ $exam->starts_at && now()->lt($exam->starts_at) ? 'هنوز باز نشده' : 'مشاهده' }}</span>
</article>
@empty<div class="student-workspace-empty"><strong>آزمون فعالی ندارید.</strong><span>با انتشار آزمون جدید، اینجا قرار می‌گیرد.</span></div>@endforelse
</div>
@if($exams->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی آزمون‌ها">{{ $exams->links() }}</nav>@endif
</section></div>
@endsection