@extends('layouts.student')

@section('title', $lesson->title . ' | شیخان')
@section('header-title', 'درس')

@section('content')
    @php
        $hasPlayableVideo = $lesson->media->contains(
            fn ($media) => str_starts_with((string) $media->mime_type, 'video/')
        );
    @endphp

    <div class="student-workspace-page">
        <section class="student-panel dashboard-panel" aria-labelledby="lesson-title">
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color:var(--panel-primary)">
                        {{ $lesson->is_free ? 'پیش‌نمایش رایگان' : 'محتوای دوره' }}
                    </span>
                    <h1 id="lesson-title">{{ $lesson->title }}</h1>
                    <p>{{ $course->title }}</p>
                </div>
                <span class="student-status {{ $lesson->student_progress >= 100 ? 'success' : 'primary' }}" data-student-progress-label>
                    {{ \App\Support\PersianUi::digits(round($lesson->student_progress)) }}٪
                </span>
            </div>

            <div class="student-notice" style="margin-top:16px">
                <span class="student-notice-icon" aria-hidden="true">i</span>
                <div class="student-notice-copy">
                    <strong>این صفحه کجای مسیر یادگیری است؟</strong>
                    <p>دوره، مسیر کامل یادگیری شامل چند سرفصل و درس است. کلاس گروهی، گروهی از دانش‌آموزان با برنامه، مدرس، حضور و غیاب و جلسه‌های مشخص است. اینجا پیشرفت شخصی مطالعه ثبت می‌شود؛ نمره رسمی فقط از تکلیف و آزمون ارزیابی‌شده می‌آید.</p>
                </div>
            </div>

            @if($lesson->summary)
                <div class="student-notice" style="margin-top:16px">
                    <span class="student-notice-icon" aria-hidden="true">✦</span>
                    <div class="student-notice-copy">
                        <strong>خلاصه درس</strong>
                        <p>{{ $lesson->summary }}</p>
                    </div>
                </div>
            @endif

            <article class="student-lesson-content">
                {!! nl2br(e($lesson->content ?: 'محتوای متنی این درس هنوز تکمیل نشده است.')) !!}
            </article>

            @if($lesson->media->isNotEmpty())
                <section class="student-list" aria-labelledby="lesson-media-title">
                    <div class="student-panel-head">
                        <div>
                            <span class="student-kicker" style="color:var(--panel-primary)">محتوای درس</span>
                            <h2 id="lesson-media-title">رسانه‌ها و فایل‌ها</h2>
                        </div>
                        <span class="student-status">دسترسی کنترل‌شده</span>
                    </div>

                    @foreach($lesson->media as $media)
                        @if(str_starts_with((string) $media->mime_type, 'video/'))
                            <article class="student-workspace-card" style="margin:12px 0">
                                <div class="student-workspace-card-head">
                                    <div>
                                        <h3>{{ $media->original_name }}</h3>
                                        <p>پیشرفت با پخش ویدئو ثبت می‌شود؛ دانلود مستقیم فایل آموزشی بسته است.</p>
                                    </div>
                                    <span class="student-workspace-status warning">فقط مشاهده</span>
                                </div>
                                <video
                                    controls
                                    preload="metadata"
                                    controlslist="nodownload"
                                    disablepictureinpicture
                                    playsinline
                                    data-student-video
                                    data-progress-url="{{ route('student.lessons.progress.update', $lesson) }}"
                                    data-duration-seconds="{{ (int) $lesson->duration_seconds }}"
                                    style="display:block;width:100%;max-height:70vh;border-radius:16px;background:#0f172a"
                                >
                                    <source src="{{ route('media.view', $media) }}" type="{{ $media->mime_type }}">
                                    مرورگر شما از پخش این ویدئو پشتیبانی نمی‌کند.
                                </video>
                                <p class="mt-3 text-xs leading-6 text-slate-500">درصد پیشرفت از رویدادهای پخش و فاصله زمانی ثبت‌شده در سرور محاسبه می‌شود؛ عدد دلخواهی از فرم ارسال نمی‌شود.</p>
                            </article>
                        @else
                            <article class="student-list-row">
                                <div class="student-date"><strong>↗</strong><small>فایل</small></div>
                                <div class="student-row-content">
                                    <div class="student-row-title">{{ $media->original_name }}</div>
                                    <span class="student-row-meta">{{ $media->mime_type ?: 'رسانه آموزشی' }}</span>
                                </div>
                                <a class="student-action" target="_blank" rel="noopener" href="{{ route('media.view', $media) }}">مشاهده امن</a>
                            </article>
                        @endif
                    @endforeach
                </section>
            @elseif($lesson->type === 'video')
                <div class="student-notice" style="margin-top:16px">
                    <span class="student-notice-icon" aria-hidden="true">!</span>
                    <div class="student-notice-copy">
                        <strong>ویدئوی این درس هنوز آماده نیست</strong>
                        <p>مدرس باید فایل ویدئویی معتبر بارگذاری کند. تا آن زمان، سامانه این درس را به‌عنوان ویدئوی تکمیل‌شده ثبت نمی‌کند.</p>
                    </div>
                </div>
            @endif

            @if(!$hasPlayableVideo && $lesson->type !== 'video')
                <form method="POST" action="{{ route('student.lessons.progress.update', $lesson) }}" data-student-progress-form class="student-progress-form">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="event" value="mark_read">
                    <div class="student-notice" style="margin-top:16px">
                        <span class="student-notice-icon" aria-hidden="true">✓</span>
                        <div class="student-notice-copy">
                            <strong>ثبت مطالعه شخصی</strong>
                            <p>وقتی محتوا را خواندی یا مرور کردی، ثبت کن تا مسیر شخصی یادگیری‌ات به‌روز شود. این دکمه نمره یا ارزیابی رسمی ایجاد نمی‌کند.</p>
                        </div>
                    </div>
                    <div class="student-detail-actions">
                        <button type="submit" class="student-welcome-action primary" {{ $lesson->student_progress >= 100 ? 'disabled' : '' }}>
                            {{ $lesson->student_progress >= 100 ? 'مطالعه ثبت شده است' : 'مطالعه کردم' }}
                        </button>
                        @if($previousLesson)
                            <a class="student-welcome-action" href="{{ route('student.lessons.show', $previousLesson) }}">← درس قبل</a>
                        @endif
                        @if($nextLesson)
                            <a class="student-welcome-action" href="{{ route('student.lessons.show', $nextLesson) }}">درس بعد →</a>
                        @endif
                    </div>
                </form>
            @else
                <div class="student-detail-actions" style="margin-top:16px">
                    @if($previousLesson)
                        <a class="student-welcome-action" href="{{ route('student.lessons.show', $previousLesson) }}">← درس قبل</a>
                    @endif
                    @if($nextLesson)
                        <a class="student-welcome-action" href="{{ route('student.lessons.show', $nextLesson) }}">درس بعد →</a>
                    @endif
                </div>
            @endif
        </section>

        <section class="student-panel dashboard-panel" aria-labelledby="note-title">
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color:var(--panel-primary)">یادداشت شخصی</span>
                    <h2 id="note-title">یادداشت برای این درس</h2>
                    <p>یادداشت فقط برای خودت است و جای پاسخ تکلیف یا آزمون را نمی‌گیرد.</p>
                </div>
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
