@extends('layouts.student')
@section('title','تکالیف | شیخان')
@section('header-title','تکالیف')
@section('content')
<div class="student-workspace-page">
<header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">پیگیری</span><h1 class="student-workspace-title">تکالیف من</h1><p class="student-workspace-description">موعدها و وضعیت ارسال تکالیف دوره‌ها و کلاس‌های مجاز شما.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.dashboard') }}">داشبورد</a></header>
<section class="student-workspace-card" aria-labelledby="assignments-title">
<div class="student-workspace-card-head"><div><h2 id="assignments-title">فهرست تکالیف</h2><p>اول مواردی را انجام بده که نیازمند اقدام هستند.</p></div></div>
<div class="student-workspace-list">
@forelse($assignments as $assignment)
@php($submission=$assignment->submissions->first())
<article class="student-workspace-row">
<div class="student-workspace-date"><strong>{{ $assignment->due_at ? App\Support\PersianUi::date($assignment->due_at) : '—' }}</strong><small>موعد</small></div>
<div class="student-workspace-row-main"><a href="{{ route('student.assignments.show',$assignment) }}">{{ $assignment->title }}</a><span>{{ $assignment->course?->title }} @if($assignment->classroom) · {{ $assignment->classroom->title }} @endif</span></div>
@if($submission?->graded_at)<span class="student-workspace-status success">ارزیابی‌شده</span>@elseif($submission?->submitted_at)<span class="student-workspace-status primary">ارسال‌شده</span>@else<span class="student-workspace-status warning">نیازمند اقدام</span>@endif
</article>
@empty<div class="student-workspace-empty"><strong>تکلیف فعالی ندارید.</strong><span>با انتشار تکلیف مرتبط، اینجا نمایش داده می‌شود.</span></div>@endforelse
</div>
@if($assignments->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی تکالیف">{{ $assignments->links() }}</nav>@endif
</section></div>
@endsection