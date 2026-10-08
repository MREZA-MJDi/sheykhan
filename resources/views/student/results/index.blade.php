@extends('layouts.student')
@section('title','نمرات و عملکرد | شیخان')
@section('header-title','نمرات و عملکرد')
@section('content')
<div class="student-workspace-page">
<header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">کارنامه</span><h1 class="student-workspace-title">نمرات و عملکرد</h1><p class="student-workspace-description">نتیجه‌های منتشرشده و ارزیابی‌های متعلق به همین حساب.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.exams.index') }}">آزمون‌ها</a></header>
<section class="student-workspace-card" aria-labelledby="results-title">
<div class="student-workspace-card-head"><div><h2 id="results-title">نتایج اخیر</h2><p>نمره‌ها و وضعیت ارزیابی‌های شما</p></div></div>
<div class="student-workspace-list">
@forelse($results as $result)
<article class="student-workspace-row">
<div class="student-workspace-date"><strong>{{ $result->score !== null ? AppSupportPersianUi::digits($result->score) : '—' }}</strong><small>نمره</small></div>
<div class="student-workspace-row-main"><strong>{{ $result->title }}</strong><span>{{ $result->occurred_at ? AppSupportPersianUi::date($result->occurred_at) : '—' }}</span></div>
<span class="student-workspace-status {{ $result->score !== null ? 'success' : 'warning' }}">{{ $result->status_label }}</span>
</article>
@empty<div class="student-workspace-empty"><strong>هنوز نتیجه‌ای ثبت نشده است.</strong><span>بعد از ارزیابی، نتایج در اینجا دیده می‌شوند.</span></div>@endforelse
</div>
@if($results->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی نتایج">{{ $results->links() }}</nav>@endif
</section></div>
@endsection