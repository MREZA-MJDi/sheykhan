@extends('layouts.student')
@section('title', 'حضور و غیاب')
@section('heading', 'حضور و غیاب')

@section('content')
@php
    $statusLabels = ['present' => 'حاضر', 'absent' => 'غایب', 'late' => 'با تأخیر', 'excused' => 'موجه'];
    $statusTones = ['present' => 'g', 'absent' => 'r', 'late' => 'a', 'excused' => 'b'];
@endphp
<div class="alert b">سوابق این صفحه از ثبت‌های واقعی حضور و غیاب ساخته شده‌اند. رکوردهایی که دادهٔ دقیقه‌ای ندارند در درصد حضور محاسبه نمی‌شوند.</div>

<div class="stats">
  <x-stat color="mint" icon="i-cal" label="درصد حضور" :value="($metrics['attendance_percent'] ?? null) === null ? '—' : \App\Support\PersianUi::digits($metrics['attendance_percent']).'٪'" hint="بر اساس دقیقه‌های ثبت‌شده" />
  <x-stat color="blue" icon="i-video" label="جلسات اندازه‌گیری‌شده" :value="\App\Support\PersianUi::digits($metrics['measured_sessions'] ?? 0)" hint="دارای دقیقه برنامه و حضور" />
  <x-stat color="amber" icon="i-clock" label="جلسات بدون داده دقیقه‌ای" :value="\App\Support\PersianUi::digits($metrics['unmeasured_sessions'] ?? 0)" hint="از درصد حضور کنار گذاشته شده" />
  <x-stat color="violet" icon="i-chart" label="دقایق حضور ثبت‌شده" :value="\App\Support\PersianUi::digits($metrics['attended_minutes'] ?? 0)" hint="از {{ \App\Support\PersianUi::digits($metrics['scheduled_minutes'] ?? 0) }} دقیقه برنامه‌ریزی‌شده" />
</div>

<section class="card">
  <div class="chead"><h2>سوابق حضور و غیاب</h2><span class="pill">{{ \App\Support\PersianUi::digits($attendance->total()) }} رکورد</span></div>
  @forelse ($attendance as $row)
    <div class="trk">
      <div class="th {{ $row->status === 'present' ? 'mint' : ($row->status === 'absent' ? 'red' : 'amber') }}">
        <svg class="i"><use href="#{{ $row->status === 'present' ? 'i-check' : 'i-cal' }}"/></svg>
      </div>
      <div>
        <b>{{ $row->classroom?->title ?? 'کلاس' }}</b>
        <span>{{ \App\Support\PersianUi::date($row->attendance_date) }} @if($row->note) · {{ $row->note }} @endif</span>
        @if($row->attended_minutes !== null && $row->scheduled_minutes !== null)
          <span>{{ \App\Support\PersianUi::digits($row->attended_minutes) }} از {{ \App\Support\PersianUi::digits($row->scheduled_minutes) }} دقیقه</span>
        @endif
      </div>
      <x-tag :tone="$statusTones[$row->status] ?? 'b'">{{ $statusLabels[$row->status] ?? $row->status }}</x-tag>
    </div>
  @empty
    <x-empty icon="i-cal" text="سابقهٔ حضور و غیابی برای کلاس‌های فعال تو ثبت نشده است." />
  @endforelse
  @if ($attendance->hasPages())<div class="frm">{{ $attendance->links() }}</div>@endif
</section>
@endsection
