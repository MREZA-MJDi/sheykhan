@extends('layouts.student')
@section('title', 'تکالیف')
@section('heading', 'تکالیف')

@section('content')
<div class="page-head">
  <div><p class="muted small">تکالیف مربوط به دوره‌ها و کلاس‌های فعال تو</p></div>
  <x-tag tone="b">{{ \App\Support\PersianUi::digits($assignments->total()) }} تکلیف</x-tag>
</div>
<div class="chips" data-filter="status" role="group" aria-label="فیلتر وضعیت تکالیف">
  <button type="button" data-value="all" class="on" aria-pressed="true">همه</button>
  <button type="button" data-value="open">نیاز به ارسال</button>
  <button type="button" data-value="checking">در حال تصحیح</button>
  <button type="button" data-value="graded">نمره‌دار</button>
</div>
<div class="asg">
@forelse ($assignments as $assignment)
  @php
    $submission = $assignment->submissions->sortByDesc('created_at')->first();
    $status = $submission?->graded_at ? 'graded' : ($submission ? 'checking' : (($assignment->due_at && $assignment->due_at->isPast()) ? 'expired' : 'open'));
    $cardClass = match ($status) {
        'graded' => 'done',
        'checking' => 'checking',
        'expired' => 'urgent',
        default => '',
    };
  @endphp
  <article class="card {{ $cardClass }}" data-filter-item data-status="{{ $status === 'expired' ? 'open' : $status }}">
    <div style="display:flex;justify-content:space-between;gap:8px;align-items:center">
      @if ($status === 'graded')
        <x-tag tone="g">تصحیح‌شده · {{ \App\Support\PersianUi::number($submission->score, 1) }}</x-tag>
      @elseif ($status === 'checking')
        <x-tag tone="v">ارسال‌شده · در انتظار بررسی</x-tag>
      @elseif ($status === 'expired')
        <x-tag tone="r">مهلت گذشته</x-tag>
      @else
        <x-due :at="$assignment->due_at" />
      @endif
      <span class="muted small">{{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at) : 'بدون مهلت' }}</span>
    </div>
    <h2>{{ $assignment->title }}</h2>
    <p class="muted small">{{ $assignment->course?->title ?? 'دوره فعال' }} @if($assignment->classroom) · {{ $assignment->classroom->title }} @endif</p>
    <a class="btn s {{ $status === 'open' || $status === 'expired' ? '' : 'ghost' }}" href="{{ route('student.assignments.show', $assignment->id) }}">
      {{ $status === 'open' ? 'حل و ارسال پاسخ' : ($status === 'graded' ? 'دیدن بازخورد' : 'مشاهده پاسخ') }}
    </a>
  </article>
@empty
  <x-empty icon="i-task" text="تکلیفی برای دوره‌های فعال تو وجود ندارد." />
@endforelse
</div>
<div data-filter-empty="status" hidden><x-empty icon="i-task" text="تکلیفی در این وضعیت وجود ندارد." /></div>
@if ($assignments->hasPages())<div class="frm">{{ $assignments->links() }}</div>@endif
@endsection
