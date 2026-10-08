@extends('layouts.student')
@section('title','کلاس‌های من | شیخان')
@section('header-title','کلاس‌های من')
@section('content')
<div class="student-workspace-page">
<header class="student-workspace-head"><div class="student-workspace-head-copy"><span class="student-workspace-kicker">برنامه</span><h1 class="student-workspace-title">کلاس‌های آنلاین</h1><p class="student-workspace-description">جلسه‌های مرتبط با دوره‌ها و کلاس‌های فعال شما؛ فقط در بازه مجاز امکان ورود وجود دارد.</p></div><a class="student-workspace-btn secondary" href="{{ route('student.dashboard') }}">داشبورد</a></header>
<section class="student-workspace-card" aria-labelledby="classes-title">
<div class="student-workspace-card-head"><div><h2 id="classes-title">جلسه‌های پیش‌رو</h2><p>وضعیت ورود هر جلسه از backend محاسبه می‌شود.</p></div></div>
<div class="student-workspace-list">
@forelse($classes as $class)
<article class="student-workspace-row">
<div class="student-workspace-date"><strong>{{ App\Support\PersianUi::time($class->scheduled_at) }}</strong><small>{{ App\Support\PersianUi::date($class->scheduled_at) }}</small></div>
<div class="student-workspace-row-main"><strong>{{ $class->title }}</strong><span>{{ $class->course?->title }} @if($class->teacher) · {{ $class->teacher->name }} @endif</span></div>
@if($class->can_join)<a class="student-workspace-status success" href="{{ route('student.live-classes.join',$class) }}">ورود به جلسه ←</a>@else<span class="student-workspace-status warning">در زمان مجاز فعال می‌شود</span>@endif
</article>
@empty<div class="student-workspace-empty"><strong>جلسه‌ای در برنامه نیست.</strong><span>جلسه‌های بعدی دوره‌های فعال در این بخش ظاهر می‌شوند.</span></div>@endforelse
</div>
@if($classes->hasPages())<nav class="student-pagination" aria-label="صفحه‌بندی کلاس‌ها">{{ $classes->links() }}</nav>@endif
</section></div>
@endsection