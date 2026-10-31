@extends('layouts.student')

@section('title', 'جزوه‌ها و منابع')
@section('heading', 'جزوه‌ها و منابع')

@section('content')
<div class="page-head">
  <p class="muted small">منابع منتشرشده برای دوره‌ها، کلاس‌ها و درس‌های مجاز تو</p>
  <x-tag tone="b">{{ \App\Support\PersianUi::digits($resources->total()) }} منبع</x-tag>
</div>
<div class="g3">
  @forelse($resources as $resource)
    @php
      $resourceCourse = $resource->course ?? $resource->classroom?->course ?? $resource->lesson?->section?->course;
      $isProtectedVideo = str_starts_with(strtolower((string) $resource->media?->mime_type), 'video/');
    @endphp
    <article class="card cc">
      <div class="th {{ $isProtectedVideo ? 'violet' : 'blue' }}">
        <svg class="i"><use href="#{{ $isProtectedVideo ? 'i-video' : 'i-file' }}"/></svg>
      </div>
      <h3>{{ $resource->title }}</h3>
      <p>{{ $resourceCourse?->title ?? $resource->academy?->name ?? 'منبع آموزشی' }}</p>
      @if($resource->description)<p>{{ $resource->description }}</p>@endif
      <p>{{ strtoupper(pathinfo((string) $resource->media?->original_name, PATHINFO_EXTENSION) ?: ($resource->resource_type ?: 'FILE')) }} @if($resource->media) · {{ \App\Support\PersianUi::number(((int) $resource->media->size) / 1024) }} کیلوبایت @endif</p>
      @if($resource->release_at)<p>انتشار: {{ \App\Support\PersianUi::date($resource->release_at) }}</p>@endif
      @if($isProtectedVideo)
        @if($resourceCourse && filled($resourceCourse->spotplayer_course_id))
          <a class="btn s" href="{{ route('student.courses.show', $resourceCourse->id) }}#spotplayer-access">پخش امن ویدئو</a>
        @else
          <x-tag tone="a">اتصال پلیر محافظت‌شده در انتظار تنظیم مدیر است</x-tag>
        @endif
      @else
        <a class="btn s" href="{{ route('student.resources.view', $resource->id) }}">مشاهده امن منبع</a>
      @endif
    </article>
  @empty
    <x-empty icon="i-file" text="هنوز منبع منتشرشده‌ای برای دوره‌ها و کلاس‌های تو در دسترس نیست." />
  @endforelse
</div>
@if($resources->hasPages())<div class="frm">{{ $resources->links() }}</div>@endif
@endsection
