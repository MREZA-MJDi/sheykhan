@extends('layouts.student')

@section('title', $liveClass->title)
@section('heading', 'فیلم جلسه')

@section('content')
<div class="page-head">
  <div>
    <a class="back" href="{{ route('student.live-classes.index') }}">
      <svg class="i" aria-hidden="true"><use href="#i-back"/></svg>
      کلاس‌های من
    </a>
    <h2 style="font-size:22px">{{ $liveClass->title }}</h2>
    <p class="muted small">
      {{ $liveClass->course?->title ?? 'دوره' }}
      @if($liveClass->teacher) · {{ $liveClass->teacher->name }} @endif
      @if($liveClass->scheduled_at) · {{ \App\Support\PersianUi::dateTime($liveClass->scheduled_at) }} @endif
      @if($liveClass->duration_minutes) · {{ \App\Support\PersianUi::digits($liveClass->duration_minutes) }} دقیقه @endif
    </p>
  </div>
</div>

<section class="card sec">
  <div class="player">
    <div style="text-align:center;padding:24px">
      <svg class="i" style="width:52px;height:52px;margin:0 auto 12px"><use href="#i-play"/></svg>
      <h2>پخش محافظت‌شده</h2>
      <p class="small" style="margin-top:8px">این ضبط فقط از طریق SpotPlayer و با مجوز دوره پخش می‌شود؛ ارائهٔ فایل خام مجاز نیست.</p>
    </div>
  </div>
  @if ($spotPlayerCourse)
    <div class="alert a">شناسهٔ این دوره در SpotPlayer هنوز تنظیم نشده است. برای فعال‌سازی پخش، با آموزشگاه تماس بگیر.</div>
    <a class="btn" href="{{ route('student.courses.show', $spotPlayerCourse->id) }}">رفتن به صفحهٔ دوره</a>
  @else
    <div class="alert a">ضبط پیدا شد، اما به دورهٔ قابل‌پخش در SpotPlayer متصل نیست. برای بررسی این مورد با آموزشگاه تماس بگیر.</div>
    <a class="btn ghost" href="{{ route('student.courses.index') }}">مشاهدهٔ دوره‌های من</a>
  @endif
</section>

@if($liveClass->description)
<section class="card">
  <div class="chead"><h2>توضیحات جلسه</h2></div>
  <p style="white-space:pre-line;line-height:2">{{ $liveClass->description }}</p>
</section>
@endif
@endsection
