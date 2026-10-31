@extends('layouts.student')

@section('title', 'دوره‌های من')
@section('heading', 'دوره‌های من')

@section('content')
<div class="page-head">
  <p class="muted small">دوره‌های منتشرشده و قابل‌دسترسی از مسیر ثبت‌نام و گروه کلاسی تو</p>
  <x-tag tone="b">{{ \App\Support\PersianUi::digits($courses->total()) }} دوره</x-tag>
</div>
@if(request('q'))
  <p class="note">نتیجه‌های جست‌وجو برای «{{ request('q') }}»</p>
@endif

<div class="g3">
  @forelse($courses as $course)
    @php
      $lessonCount = (int) ($course->published_lessons_count ?? 0);
      $progress = max(0, min(100, (float) ($course->learning_progress ?? 0)));
    @endphp
    <article class="card cc">
      <div class="th blue"><svg class="i"><use href="#i-book"/></svg></div>
      <h3>{{ $course->title }}</h3>
      <p>{{ $course->academy?->name ?? 'آموزشگاه شیخان' }} · {{ \App\Support\PersianUi::digits($lessonCount) }} درس</p>
      <div class="bar" role="progressbar" aria-label="پیشرفت {{ $course->title }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress) }}">
        <i style="width:{{ $progress }}%"></i>
      </div>
      <p>{{ \App\Support\PersianUi::digits(round($progress)) }}٪ تکمیل</p>
      <a class="btn s" href="{{ route('student.courses.show', $course->id) }}">ادامه یادگیری</a>
    </article>
  @empty
    <x-empty icon="i-book" text="{{ request('q') ? 'دوره‌ای با این عبارت پیدا نشد.' : 'هنوز دورهٔ فعالی به حساب تو متصل نشده است.' }}" :href="route('student.courses.index')" action="دیدن همه دوره‌ها" />
  @endforelse
</div>
@if($courses->hasPages())<div class="frm">{{ $courses->links() }}</div>@endif
@endsection
