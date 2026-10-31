@extends('layouts.student')
@section('title', $assignment->title)
@section('heading', 'جزئیات تکلیف')

@section('content')
@php($submission = $assignment->submissions->first())
<div class="page-head">
  <div>
    <a class="back" href="{{ route('student.assignments.index') }}"><svg class="i"><use href="#i-back"/></svg>همه تکالیف</a>
    <h2 style="font-size:22px">{{ $assignment->title }}</h2>
    <p class="muted small">{{ $assignment->course?->title ?? 'دوره فعال' }} @if($assignment->classroom) · {{ $assignment->classroom->title }} @endif</p>
  </div>
  <x-due :at="$assignment->due_at" />
</div>

<section class="card sec">
  <div class="chead"><h2>شرح تکلیف</h2><x-tag tone="b">حداکثر نمره: {{ \App\Support\PersianUi::number($assignment->max_score, 1) }}</x-tag></div>
  <p style="white-space:pre-line;line-height:2">{{ $assignment->instructions ?: 'برای این تکلیف توضیح اضافه‌ای ثبت نشده است.' }}</p>
  @if ($assignment->media->isNotEmpty())
    <div class="trk" style="flex-wrap:wrap">
      @foreach ($assignment->media as $file)
        <a class="pill" href="{{ route('media.view', $file) }}" target="_blank" rel="noopener">{{ $file->original_name }}</a>
      @endforeach
    </div>
  @endif
</section>

@if ($submission)
<section class="card sec">
  <div class="chead">
    <h2>پاسخ ثبت‌شده</h2>
    @if ($submission->graded_at)
      <x-tag tone="g">تصحیح‌شده</x-tag>
    @else
      <x-tag tone="v">در انتظار بررسی مدرس</x-tag>
    @endif
  </div>
  @if ($submission->content)<p style="white-space:pre-line;line-height:2">{{ $submission->content }}</p>@endif
  @if ($submission->media->isNotEmpty())
    @foreach ($submission->media as $file)
      <a class="trk" href="{{ route('media.view', $file) }}" target="_blank" rel="noopener"><div class="th blue"><svg class="i"><use href="#i-file"/></svg></div><div><b>{{ $file->original_name }}</b><span>فایل پاسخ</span></div><span class="btn s ghost">مشاهده امن</span></a>
    @endforeach
  @endif
  @if ($submission->score !== null)
    <div class="sum"><x-tag tone="g">نمره: {{ \App\Support\PersianUi::number($submission->score, 1) }} از {{ \App\Support\PersianUi::number($assignment->max_score, 1) }}</x-tag></div>
  @endif
  @if ($submission->feedback)<p class="note" style="margin-top:12px"><b>بازخورد مدرس:</b><br>{{ $submission->feedback }}</p>@endif
</section>
@endif

@if ($canSubmit)
<form class="card sec" method="POST" action="{{ route('student.assignments.submit', $assignment->id) }}" enctype="multipart/form-data">
  @csrf
  <div class="chead"><h2>{{ $submission ? 'ارسال مجدد پاسخ' : 'ارسال پاسخ' }}</h2></div>
  <label for="assignment-content">متن پاسخ</label>
  <textarea class="fld" id="assignment-content" name="content" rows="7" maxlength="30000" data-autosize>{{ old('content', $submission?->content) }}</textarea>
  @error('content')<p class="field-err">{{ $message }}</p>@enderror
  <x-uploader name="attachments" :max="3" :max-mb="10" accept=".pdf,.txt,.jpg,.jpeg,.png,.webp,.zip,.doc,.docx,.odt" />
  @error('attachments')<p class="field-err">{{ $message }}</p>@enderror
  @error('attachments.*')<p class="field-err">{{ $message }}</p>@enderror
  <p class="small muted" style="margin-top:10px">فایل‌ها به‌صورت خصوصی ذخیره می‌شوند و فقط کاربران مجاز می‌توانند آن‌ها را ببینند.</p>
  <div class="actions"><button class="btn" type="submit">ثبت پاسخ</button></div>
</form>
@else
  <div class="alert a">در حال حاضر امکان ارسال یا ویرایش پاسخ این تکلیف وجود ندارد.</div>
@endif
@endsection
