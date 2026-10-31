@extends('layouts.student')

@section('title', 'کلاس‌های من')
@section('heading', 'کلاس‌های من')

@section('content')
@php
    $sections = [
        'sessions' => 'جلسه‌های تاریخ‌دار',
        'weekly' => 'برنامهٔ هفتگی',
        'recordings' => 'ضبط‌های منتشرشده',
    ];
    $statusLabels = [
        'scheduled' => 'در انتظار',
        'live' => 'در حال برگزاری',
        'completed' => 'برگزارشده',
        'published' => 'ضبط منتشرشده',
    ];
    $statusTones = [
        'scheduled' => 'b',
        'live' => 'a',
        'completed' => 'g',
        'published' => 'g',
    ];
@endphp

<div class="page-head">
  <p class="muted small">جلسه‌ها، برنامه‌های تکرارشونده و ضبط‌های قابل‌دسترسی</p>
  <x-tag tone="b">{{ \App\Support\PersianUi::digits($classes->total() + $weeklySchedules->count() + $recordings->total()) }} مورد</x-tag>
</div>

<div class="chips" data-filter="section" role="group" aria-label="نوع جلسه">
  <button type="button" data-value="sessions" class="on" aria-pressed="true">جلسه‌ها ({{ \App\Support\PersianUi::digits($classes->total()) }})</button>
  <button type="button" data-value="weekly" aria-pressed="false">برنامه هفتگی ({{ \App\Support\PersianUi::digits($weeklySchedules->count()) }})</button>
  <button type="button" data-value="recordings" aria-pressed="false">ضبط‌ها ({{ \App\Support\PersianUi::digits($recordings->total()) }})</button>
</div>

<section class="card" data-filter-item data-section="sessions">
  <div class="chead"><h2>جلسه‌های تاریخ‌دار</h2><span class="pill">{{ \App\Support\PersianUi::digits($classes->total()) }} جلسه</span></div>
  @forelse($classes as $class)
    @php
      $statusLabel = $statusLabels[$class->status] ?? 'غیرقابل ورود';
      $statusTone = $statusTones[$class->status] ?? 'r';
    @endphp
    <div class="trk">
      <div class="th {{ $class->status === 'live' ? 'mint' : 'blue' }}"><svg class="i"><use href="#i-video"/></svg></div>
      <div>
        <b>{{ $class->title }}</b>
        <span>{{ $class->course?->title }} @if($class->classroom) · {{ $class->classroom->title }} @endif</span>
        <span>{{ \App\Support\PersianUi::dateTime($class->scheduled_at) }} @if($class->teacher) · {{ $class->teacher->name }} @endif</span>
      </div>
      @if($class->can_join)
        <a class="btn s" href="{{ route('student.live-classes.join', $class->id) }}">ورود به کلاس</a>
      @else
        <x-tag :tone="$statusTone">{{ $statusLabel }}</x-tag>
      @endif
    </div>
  @empty
    <x-empty icon="i-video" text="جلسه‌ای در بازهٔ برنامه‌ریزی‌شدهٔ کلاس‌ها پیدا نشد." />
  @endforelse
  @if($classes->hasPages())<div class="frm">{{ $classes->links() }}</div>@endif
</section>

<section class="card" data-filter-item data-section="weekly" hidden>
  <div class="chead"><h2>برنامهٔ هفتگی کلاس‌ها</h2><span class="pill">{{ \App\Support\PersianUi::digits($weeklySchedules->count()) }} برنامه</span></div>
  @forelse($weeklySchedules as $schedule)
    <div class="trk">
      <div class="th violet"><svg class="i"><use href="#i-cal"/></svg></div>
      <div>
        <b>{{ $schedule->classroom?->title ?? 'کلاس هفتگی' }}</b>
        <span>{{ $schedule->classroom?->course?->title }}</span>
        <span>{{ \App\Support\PersianUi::digits($schedule->start_time) }} – {{ \App\Support\PersianUi::digits($schedule->end_time) }} · {{ ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'][(int) $schedule->weekday] ?? 'نامشخص' }}</span>
      </div>
      @if($schedule->can_join)
        <a class="btn s" href="{{ route('student.schedule.join', $schedule->id) }}">ورود به کلاس</a>
      @else
        <x-tag tone="b">برنامهٔ تکرارشونده</x-tag>
      @endif
    </div>
  @empty
    <x-empty icon="i-cal" text="برنامهٔ هفتگی برای کلاس‌های فعال تو ثبت نشده است." />
  @endforelse
</section>

<section class="card" data-filter-item data-section="recordings" hidden>
  <div class="chead"><h2>ضبط‌های منتشرشده</h2><span class="pill">{{ \App\Support\PersianUi::digits($recordings->total()) }} فایل</span></div>
  @forelse($recordings as $recording)
    <a class="trk" href="{{ route('student.live-classes.recording', $recording->id) }}">
      <div class="th blue"><svg class="i"><use href="#i-play"/></svg></div>
      <div>
        <b>{{ $recording->title }}</b>
        <span>{{ $recording->course?->title }} · {{ \App\Support\PersianUi::dateTime($recording->scheduled_at) }}</span>
        <span>{{ $recording->recording?->original_name ?? 'ضبط کلاس' }}</span>
      </div>
      <span class="btn s">تماشای ضبط</span>
    </a>
  @empty
    <x-empty icon="i-play" text="ضبط منتشرشده‌ای برای دوره‌هایت در دسترس نیست." />
  @endforelse
  @if($recordings->hasPages())<div class="frm">{{ $recordings->links() }}</div>@endif
</section>
@endsection
