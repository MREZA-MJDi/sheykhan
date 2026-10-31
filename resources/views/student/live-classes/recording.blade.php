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
    <iframe
      src="{{ route('media.view', $liveClass->recording) }}"
      title="پخش امن ضبط {{ $liveClass->title }}"
      loading="lazy"
      referrerpolicy="no-referrer"
      allow="fullscreen"
      allowfullscreen></iframe>
  </div>
  <p class="muted small">این ضبط فقط پس از بررسی دسترسی کلاس و از viewer امن داخل سایت نمایش داده می‌شود.</p>
</section>

@if($liveClass->description)
<section class="card">
  <div class="chead"><h2>توضیحات جلسه</h2></div>
  <p style="white-space:pre-line;line-height:2">{{ $liveClass->description }}</p>
</section>
@endif
@endsection
