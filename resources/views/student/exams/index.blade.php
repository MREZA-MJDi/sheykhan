@extends('layouts.student')
@section('title', 'آزمون‌ها')
@section('heading', 'آزمون‌ها')

@section('content')
<div class="page-head"><p class="muted small">آزمون‌های منتشرشده برای دوره‌ها و گروه‌های کلاسی تو</p><x-tag tone="b">{{ \App\Support\PersianUi::digits($exams->total()) }} آزمون</x-tag></div>
<div class="chips" data-filter="state" role="group" aria-label="فیلتر آزمون‌ها">
  <button type="button" data-value="all" class="on" aria-pressed="true">همه</button>
  <button type="button" data-value="ready">قابل شروع</button>
  <button type="button" data-value="upcoming">پیش‌رو</button>
  <button type="button" data-value="closed">خارج از بازه</button>
</div>
<div class="g3">
@forelse ($exams as $exam)
  @php
    $isOpen = $exam->status === 'published'
      && (!$exam->starts_at || $exam->starts_at->lte(now()))
      && (!$exam->ends_at || $exam->ends_at->gte(now()));
    $state = $isOpen ? 'ready' : (($exam->starts_at && $exam->starts_at->isFuture()) ? 'upcoming' : 'closed');
  @endphp
  <article class="card cc" data-filter-item data-state="{{ $state }}">
    <div class="th {{ $isOpen ? 'mint' : 'blue' }}"><svg class="i"><use href="#i-exam"/></svg></div>
    <h3>{{ $exam->title }}</h3>
    <p>{{ $exam->course?->title ?? 'دوره فعال' }} @if($exam->classroom) · {{ $exam->classroom->title }} @endif</p>
    <p>{{ \App\Support\PersianUi::digits($exam->duration_minutes ?: 0) }} دقیقه · {{ \App\Support\PersianUi::digits($exam->questions->count()) }} سؤال</p>
    @if ($isOpen)
      <x-tag tone="g">همین حالا قابل شروع</x-tag>
    @elseif ($state === 'upcoming')
      <x-tag tone="a">شروع {{ \App\Support\PersianUi::dateTime($exam->starts_at) }}</x-tag>
    @else
      <x-tag tone="r">خارج از بازه برگزاری</x-tag>
    @endif
    <a class="btn s {{ $isOpen ? '' : 'ghost' }}" href="{{ route('student.exams.show', $exam->id) }}">مشاهده جزئیات و تلاش‌ها</a>
  </article>
@empty
  <x-empty icon="i-exam" text="آزمون منتشرشده‌ای برای دوره‌های فعال تو وجود ندارد." />
@endforelse
</div>
<div data-filter-empty="state" hidden><x-empty icon="i-exam" text="آزمونی در این دسته وجود ندارد." /></div>
@if ($exams->hasPages())<div class="frm">{{ $exams->links() }}</div>@endif
@endsection
