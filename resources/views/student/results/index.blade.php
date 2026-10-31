@extends('layouts.student')
@section('title', 'نمرات و عملکرد')
@section('heading', 'نمرات و عملکرد')

@section('content')
@php
    $pageRows = collect($results->items());
    $scoredRows = $pageRows->filter(fn ($row) => $row->score !== null);
    $average = $scoredRows->isNotEmpty() ? $scoredRows->avg('score') : null;
@endphp
<div class="stats">
  <x-stat color="blue" icon="i-chart" label="نتیجه‌های این صفحه" :value="\App\Support\PersianUi::digits($pageRows->count())" hint="از {{ \App\Support\PersianUi::digits($results->total()) }} نتیجه" />
  <x-stat color="mint" icon="i-trophy" label="میانگین نمره‌های قابل‌نمایش" :value="$average === null ? '—' : \App\Support\PersianUi::number($average, 1)" hint="فقط نمره‌های همین صفحه" />
  <x-stat color="amber" icon="i-exam" label="نتیجه آزمون" :value="\App\Support\PersianUi::digits($pageRows->where('result_type', 'exam')->count())" hint="در این صفحه" />
  <x-stat color="violet" icon="i-task" label="نتیجه تکلیف" :value="\App\Support\PersianUi::digits($pageRows->where('result_type', 'assignment')->count())" hint="در این صفحه" />
</div>

<section class="card">
  <div class="chead"><h2>کارنامه</h2><span class="pill">{{ \App\Support\PersianUi::digits($results->total()) }} نتیجه</span></div>
  @forelse ($results as $result)
    <div class="trk">
      <div class="th {{ $result->result_type === 'exam' ? 'violet' : 'blue' }}"><svg class="i"><use href="#{{ $result->result_type === 'exam' ? 'i-exam' : 'i-task' }}"/></svg></div>
      <div>
        <b>{{ $result->title }}</b>
        <span>{{ $result->result_type === 'exam' ? 'آزمون' : 'تکلیف' }} · {{ $result->status_label }} · {{ $result->occurred_at ? \App\Support\PersianUi::dateTime($result->occurred_at) : '—' }}</span>
      </div>
      @if ($result->score !== null)
        <x-tag :tone="$result->status_label === 'تصحیح‌شده' ? 'g' : 'a'">{{ \App\Support\PersianUi::number($result->score, 1) }}</x-tag>
      @else
        <x-tag tone="b">در انتظار نمره</x-tag>
      @endif
      @if ($result->result_type === 'exam')
        <a class="btn s ghost" href="{{ route('student.exams.result', $result->target_id) }}">جزئیات</a>
      @else
        <a class="btn s ghost" href="{{ route('student.assignments.show', $result->target_id) }}">جزئیات</a>
      @endif
    </div>
  @empty
    <x-empty icon="i-chart" text="هنوز نتیجهٔ ثبت‌شده‌ای برای نمایش وجود ندارد." />
  @endforelse
  @if ($results->hasPages())<div class="frm">{{ $results->links() }}</div>@endif
</section>
@endsection
