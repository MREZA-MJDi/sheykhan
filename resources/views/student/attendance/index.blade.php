@extends('layouts.student')
@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')
@section('content')
<div class="student-workspace-page">
<header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">پیگیری</span><h1 class="student-workspace-title">حضور و غیاب من</h1><p class="student-workspace-description">سوابق فقط برای کلاس‌هایی که عضویت فعال شما در آن‌ها ثبت شده است.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.dashboard') }}">داشبورد</a></header>
<section class="student-workspace-card" aria-labelledby="attendance-title">
<div class="student-workspace-card-head"><div><h2 id="attendance-title">سوابق حضور</h2><p>تاریخ‌ها به تقویم جلالی نمایش داده می‌شوند.</p></div></div>
<div class="student-workspace-list">
@forelse($attendance as $item)
@php($label=['present'=>'حاضر','absent'=>'غایب','late'=>'با تأخیر','excused'=>'موجه'][$item->status] ?? $item->status)
<article class="student-workspace-row">
<div class="student-workspace-date"><strong>{{ App\Support\PersianUi::date($item->attendance_date) }}</strong><small>تاریخ</small></div>
<div class="student-workspace-row-main"><strong>{{ $item->classroom?->title }}</strong><span>{{ $item->note ?: 'بدون توضیح' }}</span></div>
<span class="student-workspace-status {{ $item->status==='present' ? 'success' : 'warning' }}">{{ $label }}</span>
</article>
@empty<div class="student-workspace-empty"><strong>سابقه‌ای ثبت نشده است.</strong><span>پس از ثبت حضور در کلاس‌های شما، اینجا نمایش داده می‌شود.</span></div>@endforelse
</div>
@if($attendance->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی حضور و غیاب">{{ $attendance->links() }}</nav>@endif
</section></div>
@endsection