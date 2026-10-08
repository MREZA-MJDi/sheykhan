@extends('layouts.student')
@section('title','منابع آموزشی | شیخان')
@section('header-title','منابع آموزشی')
@section('content')
<div class="student-workspace-page">
<header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">فضای اختصاصی</span><h1 class="student-workspace-title">منابع آموزشی من</h1><p class="student-workspace-description">جزوه‌ها، فایل‌ها و محتوایی که برای دوره‌ها و کلاس‌های شما منتشر شده‌اند.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.dashboard') }}">داشبورد</a></header>
<section class="student-workspace-card" aria-labelledby="resources-title">
<div class="student-workspace-card-head"><div><h2 id="resources-title">کتابخانه آموزشی</h2><p>دسترسی هر منبع همچنان توسط backend کنترل می‌شود.</p></div></div>
<div class="student-workspace-list">
@forelse($resources as $resource)
<article class="student-workspace-row">
<div class="student-workspace-date"><strong>↗</strong><small>فایل</small></div>
<div class="student-workspace-row-main"><strong>{{ $resource->title }}</strong><span>{{ $resource->course?->title ?? $resource->classroom?->title ?? $resource->lesson?->title ?? 'منبع آموزشی' }} @if($resource->description) · {{ IlluminateSupportStr::limit($resource->description,90) }} @endif</span></div>
<div class="student-workspace-actions"><a class="student-workspace-btn primary" href="{{ route('student.resources.view', $resource) }}">مشاهده</a>@if($resource->downloadable)<a class="student-workspace-btn secondary" href="{{ route('student.resources.download',$resource) }}">دریافت</a>@endif</div>
</article>
@empty<div class="student-workspace-empty"><strong>هنوز منبع آموزشی منتشر نشده است.</strong><span>با انتشار جزوه یا فایل، اینجا نمایش داده می‌شود.</span></div>@endforelse
</div>
@if($resources->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی منابع">{{ $resources->links() }}</nav>@endif
</section></div>
@endsection