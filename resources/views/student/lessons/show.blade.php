@extends('layouts.student')

@section('title', $lesson->title . ' | شیخان')
@section('header-title', 'درس')

@section('content')
    <div class="student-dashboard">
        <section class="student-panel dashboard-panel" aria-labelledby="lesson-title">
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color:var(--panel-primary)">
                        {{ $lesson->is_free ? 'پیش‌نمایش رایگان' : 'محتوای دوره' }}
                    </span>
                    <h2 id="lesson-title">{{ $lesson->title }}</h2>
                    <p>{{ $course->title }}</p>
                </div>
                <span class="student-status {{ $lesson->student_progress >= 100 ? 'success' : 'primary' }}">
                    {{ \App\Support\PersianUi::digits(round($lesson->student_progress)) }}٪
                </span>
            </div>

            @if($lesson->summary)
                <div class="student-notice" style="margin-top:16px">
                    <span class="student-notice-icon" aria-hidden="true">i</span>
                    <div class="student-notice-copy"><strong>خلاصه درس</strong><p>{{ $lesson->summary }}</p></div>
                </div>
            @endif

            <article class="student-lesson-content">
                {!! nl2br(e($lesson->content ?: 'محتوای متنی این درس هنوز تکمیل نشده است.')) !!}
            </article>

            @if($lesson->media->isNotEmpty())
                <div class="student-list">
                    <div class="student-panel-head">
                        <div><span class="student-kicker" style="color:var(--panel-primary)">رسانه امن</span><h3>فایل‌های این درس</h3></div>
                        <span class="student-status">فقط مشاهده</span>
                    </div>
                    @foreach($lesson->media as $media)
                        <div class="student-list-row">
                            <div class="student-date"><strong>↗</strong><small>امن</small></div>
                            <div class="student-row-content">
                                <div class="student-row-title">{{ $media->original_name }}</div>
                                <span class="student-row-meta">{{ $media->mime_type ?: 'رسانه آموزشی' }}</span>
                            </div>
                            <a class="student-action" target="_blank" rel="noopener" href="{{ route('media.view', $media) }}">مشاهده امن</a>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('student.lessons.progress.update', $lesson) }}" data-student-progress-form class="student-progress-form">
                @csrf
                @method('PATCH')
                <input type="hidden" name="progress_percent" value="{{ min(100, max(0, (float)$lesson->student_progress)) }}" data-progress-input>
                <input type="hidden" name="seconds_watched" value="{{ (int)$lesson->seconds_watched }}" data-seconds-input>
                <div class="student-detail-actions">
                    <button type="submit" class="student-welcome-action primary">ثبت پیشرفت این درس</button>
                    @if($previousLesson)
                        <a class="student-welcome-action" href="{{ route('student.lessons.show', $previousLesson) }}">← درس قبل</a>
                    @endif
                    @if($nextLesson)
                        <a class="student-welcome-action" href="{{ route('student.lessons.show', $nextLesson) }}">درس بعد →</a>
                    @endif
                </div>
            </form>
        </section>

        <section class="student-panel dashboard-panel" aria-labelledby="note-title">
            <div class="student-panel-head">
                <div><span class="student-kicker" style="color:var(--panel-primary)">یادداشت شخصی</span><h2 id="note-title">یادداشت برای این درس</h2></div>
            </div>
            <form method="POST" action="{{ route('student.lessons.notes.store', $lesson) }}" class="student-form">
                @csrf
                <label for="note-content">یادداشت</label>
                <textarea id="note-content" name="content" rows="5" maxlength="20000" required placeholder="نکته‌ای که می‌خواهی بعداً به آن برگردی..."></textarea>
                <button type="submit" class="student-welcome-action primary">ذخیره یادداشت</button>
            </form>
        </section>
    </div>
@endsection
