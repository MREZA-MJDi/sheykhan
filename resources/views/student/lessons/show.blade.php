@extends('layouts.student')

@section('title', $lesson->title)
@section('header-title', 'مشاهده درس')

@section('content')
@php
    $progress = max(0, min(100, (float) ($lesson->student_progress ?? 0)));
    $duration = max(0, (int) ($lesson->duration_seconds ?? 0));
@endphp
<section class="page">
    <div class="card">
        <div class="chead">
            <h2>{{ $lesson->title }}</h2>
            <a class="pill" href="{{ route('student.courses.show', $course->id) }}">{{ $course->title }}</a>
        </div>
        @if($lesson->summary)
            <p class="note">{{ $lesson->summary }}</p>
        @endif

        @if($lesson->content)
            <article class="lesson-content" style="white-space:pre-line;line-height:2.1;overflow-wrap:anywhere">{{ $lesson->content }}</article>
        @endif

        @if($lesson->media->isNotEmpty())
            <div class="chead" style="margin-top:20px"><h2>فایل‌های درس</h2></div>
            @foreach($lesson->media as $media)
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-file"/></svg></div>
                    <div><b>{{ $media->original_name }}</b><span>{{ strtoupper($media->extension ?? '') }} · {{ \App\Support\PersianUi::number(((int) $media->size) / 1024) }} کیلوبایت</span></div>
                    @if(str_starts_with(strtolower((string) $media->mime_type), 'video/'))
                        <div class="flex flex-col gap-2">
                            <span class="rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold leading-6 text-indigo-900">ویدئوی آموزشی از فایل خام ارائه نمی‌شود تا دسترسی محافظت‌شده حفظ شود.</span>
                            @if(filled($course->spotplayer_course_id))
                                <a class="btn s" href="{{ route('student.courses.show', $course->id) }}#spotplayer-access">پخش امن ویدئو در صفحه دوره</a>
                            @else
                                <span class="text-xs leading-6 text-amber-800">اتصال SpotPlayer این دوره هنوز تنظیم نشده است. مدیر آموزشگاه باید شناسهٔ رسمی دوره را ثبت کند.</span>
                            @endif
                        </div>
                    @else
                        <a class="btn s" href="{{ route('media.view', $media->id) }}">مشاهده فایل</a>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <div class="card">
        <div class="chead"><h2>پیشرفت این درس</h2><span class="tag">{{ \App\Support\PersianUi::number($progress) }}٪</span></div>
        <div class="prog" role="progressbar" aria-label="پیشرفت درس" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($progress) }}"><i data-progress-meter style="width:{{ $progress }}%"></i></div>
        <p class="note" style="margin-top:12px">پیشرفت ذخیره‌شده فقط برای حساب خودت ثبت می‌شود.</p>
        @if(auth()->user()->hasPermission('lessons.progress'))
            <form data-progress-form method="POST" action="{{ route('student.lessons.progress.update', $lesson->id) }}" style="margin-top:12px">
                @csrf
                @method('PATCH')
                <input type="hidden" name="progress_percent" value="{{ $progress }}" data-progress-value>
                <input type="hidden" name="seconds_watched" value="{{ (int) ($lesson->seconds_watched ?? 0) + $duration }}">
                <div class="frm">
                    <button class="btn s" type="button" data-mark-complete>علامت‌گذاری به‌عنوان تکمیل‌شده</button>
                    <span data-progress-feedback role="status"></span>
                </div>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="chead"><h2>یادداشت این درس</h2></div>
        <form method="POST" action="{{ route('student.lessons.notes.store', $lesson->id) }}">
            @csrf
            <label for="lesson-note" style="display:block;font-size:12px;margin-bottom:8px">یادداشت شخصی</label>
            <textarea class="fld" id="lesson-note" name="content" rows="4" maxlength="20000" required placeholder="نکته‌های مهم این درس را بنویس...">{{ old('content', $note?->content ?? '') }}</textarea>
            <div class="frm"><button class="btn s" type="submit">ذخیره یادداشت</button></div>
        </form>
    </div>

    <div class="frm">
        @if($previousLesson)
            <a class="pill" href="{{ route('student.lessons.show', $previousLesson->id) }}">درس قبلی: {{ $previousLesson->title }}</a>
        @endif
        @if($nextLesson)
            <a class="btn s" href="{{ route('student.lessons.show', $nextLesson->id) }}">درس بعدی: {{ $nextLesson->title }}</a>
        @endif
    </div>
</section>
@endsection
