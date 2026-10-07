@extends('layouts.student')

@section('title',$assignment->title.' | شیخان')
@section('header-title','جزئیات تکلیف')

@section('content')
<div class="student-dashboard">
<section class="student-panel dashboard-panel" aria-labelledby="assignment-title">
    <div class="student-panel-head">
        <div>
            <span class="student-kicker" style="color:var(--panel-primary)">تکلیف</span>
            <h2 id="assignment-title">{{ $assignment->title }}</h2>
            <p>{{ $assignment->course?->title }} @if($assignment->classroom) · {{ $assignment->classroom->title }} @endif</p>
        </div>
        @php($submission=$assignment->submissions->first())
        @if($submission?->graded_at)
            <span class="student-status success">ارزیابی‌شده</span>
        @elseif($submission?->submitted_at)
            <span class="student-status primary">ارسال‌شده</span>
        @else
            <span class="student-status warning">منتظر ارسال</span>
        @endif
    </div>

    @if($assignment->instructions)
        <article class="student-lesson-content">{{ $assignment->instructions }}</article>
    @endif

    <div class="student-notice" style="margin-top:16px">
        <span class="student-notice-icon" aria-hidden="true">!</span>
        <div class="student-notice-copy">
            <strong>مهلت ارسال</strong>
            <p>{{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at) : 'بدون مهلت مشخص' }}</p>
        </div>
    </div>

    @if($submission?->feedback)
        <div class="student-notice" style="margin-top:12px">
            <span class="student-notice-icon" aria-hidden="true">✓</span>
            <div class="student-notice-copy"><strong>بازخورد مدرس</strong><p>{{ $submission->feedback }}</p></div>
        </div>
    @endif

    @if(!$submission?->graded_at && (!$assignment->due_at || now()->lte($assignment->due_at)))
        <form method="POST" action="{{ route('student.assignments.submit',$assignment) }}" enctype="multipart/form-data" class="student-form">
            @csrf
            <label for="assignment-content">پاسخ شما</label>
            <textarea id="assignment-content" name="content" rows="8" maxlength="30000" placeholder="پاسخ، راه‌حل یا توضیحات خودت را بنویس...">{{ $submission?->content }}</textarea>
            <label for="assignment-attachments">پیوست (حداکثر ۳ فایل)</label>
            <input id="assignment-attachments" type="file" name="attachments[]" multiple accept=".pdf,.txt,.jpg,.jpeg,.png,.webp,.zip">
            <div class="student-detail-actions">
                <button type="submit" class="student-welcome-action primary">{{ $submission?->submitted_at ? 'به‌روزرسانی پاسخ' : 'ارسال پاسخ' }}</button>
            </div>
        </form>
    @else
        <div class="student-detail-actions">
            <span class="student-status">{{ $submission?->graded_at ? 'ارسال نهایی شده است' : 'مهلت ارسال به پایان رسیده است' }}</span>
        </div>
    @endif

    @if($submission?->media?->isNotEmpty())
        <div class="student-list">
            @foreach($submission->media as $media)
                <div class="student-list-row">
                    <div class="student-date"><strong>↗</strong><small>پیوست</small></div>
                    <div class="student-row-content"><div class="student-row-title">{{ $media->original_name }}</div><span class="student-row-meta">{{ $media->mime_type }}</span></div>
                    <a class="student-action" target="_blank" rel="noopener" href="{{ route('media.view',$media) }}">مشاهده امن</a>
                </div>
            @endforeach
        </div>
    @endif
</section>
</div>
@endsection
