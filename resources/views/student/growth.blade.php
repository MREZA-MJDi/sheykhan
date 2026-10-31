@extends('layouts.student')

@section('title', 'رشد تحصیلی')
@section('heading', 'رشد تحصیلی من')

@section('content')
@php
    $meta = [
        'exams' => ['آزمون‌ها', 'میانگین نمرهٔ آزمون‌ها', 40, '#3d7bff'],
        'homework' => ['تکالیف', 'نمره و تحویل تکالیف', 25, '#ff9a2e'],
        'progress' => ['پیشرفت درسی', 'میانگین پیشرفت درس‌های منتشرشده', 20, '#7c5cff'],
        'attendance' => ['حضور', 'درصد حضور بر اساس دقایق معتبر', 15, '#16b894'],
    ];
@endphp

<section class="card">
  <div class="gtop">
    <div class="ring2" style="--p:{{ $growth['score'] ?? 0 }}">
      <div>
        <b>{{ $growth['score'] === null ? '—' : \App\Support\PersianUi::digits($growth['score']) }}</b>
        <small>امتیاز یادگیری</small>
      </div>
    </div>
    <div class="meter">
      @foreach ($meta as $key => [$label, $description, $weight, $color])
        @php($value = $growth['components'][$key] ?? null)
        <div>
          <div class="h">
            <span>{{ $label }}<small>{{ $description }} · وزن {{ \App\Support\PersianUi::digits($weight) }}٪</small></span>
            <span>{{ $value === null ? 'هنوز دادهٔ معتبر نداریم' : \App\Support\PersianUi::digits($value).'٪' }}</span>
          </div>
          <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" @if($value !== null) aria-valuenow="{{ $value }}" @endif>
            <i style="width:{{ $value ?? 0 }}%;background:{{ $color }}"></i>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  <p class="small muted" style="margin-top:14px">شاخص‌های بدون داده از امتیاز حذف می‌شوند؛ برای آن‌ها نمرهٔ صفر فرض نشده است.</p>
</section>

<div class="row">
  <section class="card">
    <div class="chead"><h2>رشد هر درس</h2></div>
    <x-empty icon="i-exam" text="تفکیک نمره و تغییرات هر درس هنوز در داده‌های کارنامه ذخیره نمی‌شود." />
  </section>
  <section class="card">
    <div class="chead"><h2>روند هفته‌ها</h2></div>
    <x-empty icon="i-chart" text="برای نمایش روند تاریخی، به ثبت snapshotهای دوره‌ای نیاز است." />
  </section>
</div>
<details class="card">
  <summary style="cursor:pointer;font-weight:600">امتیاز چطور حساب می‌شود؟</summary>
  <p class="muted" style="margin-top:10px;line-height:2">وزن شاخص‌ها مطابق طرح داشبورد است: آزمون‌ها ۴۰٪، تکالیف ۲۵٪، پیشرفت درسی ۲۰٪ و حضور ۱۵٪. فقط بخش‌هایی که دادهٔ معتبر دارند در میانگین لحاظ می‌شوند. رشد نسبت به دورهٔ قبل و نمرهٔ تفکیکی دروس تا زمان ذخیره‌شدن دادهٔ تاریخی نمایش داده نمی‌شود.</p>
</details>
@endsection
