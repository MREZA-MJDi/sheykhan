@extends('layouts.teacher')

@section('title', 'مدیریت محتوای دوره | شیخان')
@section('header-title', 'مدیریت محتوای دوره')

@php
    $lessonCollection = $sections->flatMap(fn ($section) => $section->lessons);
    $publishedLessons = $lessonCollection->where('status', 'published')->count();
    $videoLessons = $lessonCollection->where('type', 'video')->count();
    $mediaCount = $lessonCollection->sum(fn ($lesson) => $lesson->media->count());
    $durationSeconds = $lessonCollection->sum('duration_seconds');
    $durationMinutes = (int) ceil($durationSeconds / 60);
@endphp

@section('content')
<div
    class="teacher-builder"
    data-course-builder
    data-course-id="{{ $course->id }}"
    data-reorder-url="{{ route('teacher.courses.sections.reorder', $course) }}"
>
    @if(session('success'))
        <div class="teacher-builder-alert success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="teacher-builder-alert danger">
            <strong>عملیات انجام نشد.</strong>
            @foreach($errors->all() as $error)
                <span>{{ $error }}</span>
            @endforeach
        </div>
    @endif

    <section class="teacher-builder-hero">
        <div class="teacher-builder-hero-copy">
            <span class="teacher-kicker">COURSE BUILDER</span>
            <h2>{{ $course->title }}</h2>
            <p>
                محتوای دوره را سرفصل‌بندی کن، ویدئو و PDF اضافه کن، درس‌های رایگان و ویژه را مشخص کن
                و قبل از انتشار ساختار آموزشی را بررسی کن.
            </p>

            <div class="teacher-builder-hero-actions">
                <a href="{{ route('teacher.courses.progress', $course) }}" class="teacher-builder-btn primary">
                    گزارش یادگیری
                </a>
                <a href="{{ route('teacher.courses.edit', $course) }}" class="teacher-builder-btn ghost">
                    تنظیمات دوره
                </a>
            </div>
        </div>

        <div class="teacher-builder-overview">
            <div><span>سرفصل</span><strong>{{ $sections->count() }}</strong></div>
            <div><span>درس منتشرشده</span><strong>{{ $publishedLessons }}</strong></div>
            <div><span>ویدئو</span><strong>{{ $videoLessons }}</strong></div>
            <div><span>فایل آموزشی</span><strong>{{ $mediaCount }}</strong></div>
            <div><span>زمان محتوا</span><strong>{{ $durationMinutes }} دقیقه</strong></div>
        </div>
    </section>

    <div class="teacher-builder-toolbar">
        <div>
            <span class="teacher-kicker">STRUCTURE</span>
            <h3>ساختار دوره</h3>
            <p>ترتیب سرفصل‌ها و درس‌ها همان ترتیبی است که دانش‌آموز در مسیر یادگیری می‌بیند.</p>
        </div>
        <details class="teacher-builder-create">
            <summary>+ افزودن سرفصل</summary>
            <form method="POST" action="{{ route('teacher.courses.sections.store', $course) }}">
                @csrf
                <input name="title" required maxlength="255" placeholder="مثلاً: مبانی HTML و CSS">
                <textarea name="description" rows="2" placeholder="توضیح کوتاه سرفصل"></textarea>
                <button type="submit">ساخت سرفصل</button>
            </form>
        </details>
    </div>

    <div class="teacher-builder-sections" data-section-list>
        @forelse($sections as $section)
            <section class="teacher-builder-section" data-section-item data-section-id="{{ $section->id }}">
                <header class="teacher-builder-section-head">
                    <div class="teacher-section-drag" draggable="true" title="جابجایی سرفصل">⋮⋮</div>

                    <div class="teacher-section-index">{{ $loop->iteration }}</div>

                    <div class="teacher-section-copy">
                        <span class="teacher-kicker">SECTION {{ $loop->iteration }}</span>
                        <h3>{{ $section->title }}</h3>
                        @if($section->description)
                            <p>{{ $section->description }}</p>
                        @endif
                    </div>

                    <div class="teacher-section-stats">
                        <span>{{ $section->lessons_count }} درس</span>
                        <span>{{ $section->published_lessons_count }} منتشرشده</span>
                    </div>

                    <details class="teacher-inline-menu">
                        <summary>•••</summary>
                        <div>
                            <details>
                                <summary>ویرایش سرفصل</summary>
                                <form method="POST" action="{{ route('teacher.sections.update', $section) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input name="title" value="{{ $section->title }}" required>
                                    <textarea name="description" rows="3">{{ $section->description }}</textarea>
                                    <button type="submit">ذخیره</button>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('teacher.sections.destroy', $section) }}" data-confirm="سرفصل خالی حذف شود؟">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="danger">حذف سرفصل</button>
                            </form>
                        </div>
                    </details>
                </header>

                <div class="teacher-builder-lessons">
                    @forelse($section->lessons as $lesson)
                        @php
                            $videos = $lesson->media->filter(fn ($media) => $media->collection === 'video');
                            $documents = $lesson->media->filter(fn ($media) => in_array($media->collection, ['pdf', 'resource'], true));
                            $previewVideo = $videos->first();
                        @endphp

                        <article class="teacher-lesson-card" data-lesson-card>
                            <div class="teacher-lesson-main">
                                <div class="teacher-lesson-type type-{{ $lesson->type }}">
                                    @switch($lesson->type)
                                        @case('video')▶@break
                                        @case('text')✎@break
                                        @case('quiz')?@break
                                        @case('live')◉@break
                                        @default▤
                                    @endswitch
                                </div>

                                <div class="teacher-lesson-copy">
                                    <div class="teacher-lesson-title-row">
                                        <h4>{{ $lesson->title }}</h4>
                                        <span class="teacher-content-status {{ $lesson->status }}">{{ $lesson->status === 'published' ? 'منتشر' : ($lesson->status === 'draft' ? 'پیش‌نویس' : 'آرشیو') }}</span>
                                        @if($lesson->is_free)
                                            <span class="teacher-content-badge free">رایگان</span>
                                        @else
                                            <span class="teacher-content-badge premium">ویژه</span>
                                        @endif
                                    </div>

                                    <div class="teacher-lesson-meta">
                                        <span>{{ $lesson->type }}</span>
                                        <span>{{ $lesson->duration_seconds ? gmdate('H:i:s', $lesson->duration_seconds) : 'بدون زمان' }}</span>
                                        <span>{{ $videos->count() }} ویدئو</span>
                                        <span>{{ $documents->count() }} فایل</span>
                                    </div>

                                    @if($lesson->summary)
                                        <p class="teacher-lesson-summary">{{ $lesson->summary }}</p>
                                    @endif
                                </div>

                                <div class="teacher-lesson-actions">
                                    <details>
                                        <summary>ویرایش درس</summary>
                                    </details>
                                </div>
                            </div>

                            @if($previewVideo)
                                <div class="teacher-video-preview">
                                    <div>
                                        <span class="teacher-kicker">VIDEO PREVIEW</span>
                                        <strong>پیش‌نمایش مدرس</strong>
                                    </div>
                                    <video controls preload="metadata" playsinline controlsList="nodownload" src="{{ route('teacher.lessons.media.stream', [$lesson, $previewVideo]) }}"></video>
                                </div>
                            @endif

                            @if($lesson->content)
                                <details class="teacher-lesson-content">
                                    <summary>متن درس</summary>
                                    <div>{!! nl2br(e($lesson->content)) !!}</div>
                                </details>
                            @endif

                            @if($documents->isNotEmpty())
                                <div class="teacher-resource-list">
                                    @foreach($documents as $media)
                                        @php
                                            $access = $media->metadata['access'] ?? 'course';
                                            $downloadable = $media->metadata['downloadable'] ?? true;
                                        @endphp
                                        <div class="teacher-resource-row">
                                            <div>
                                                <strong>{{ $media->original_name }}</strong>
                                                <small>{{ strtoupper($media->extension ?? 'FILE') }} · {{ number_format($media->size / 1048576, 1) }} MB</small>
                                            </div>
                                            <div class="teacher-resource-actions">
                                                <span class="teacher-content-badge {{ $access === 'paid' ? 'premium' : ($access === 'free' ? 'free' : '') }}">
                                                    {{ $access === 'paid' ? 'پولی' : ($access === 'free' ? 'رایگان' : 'همراه دوره') }}
                                                </span>
                                                @if($downloadable)
                                                    <a href="{{ route('media.download', $media) }}" class="teacher-builder-mini-btn">دانلود</a>
                                                @else
                                                    <span class="teacher-builder-mini-muted">فقط مشاهده</span>
                                                @endif
                                                <form method="POST" action="{{ route('teacher.lessons.media.destroy', [$lesson, $media]) }}" data-confirm="این فایل حذف شود؟">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="teacher-builder-delete">حذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <details class="teacher-lesson-editor">
                                <summary>باز کردن ویرایشگر درس</summary>
                                <form method="POST" action="{{ route('teacher.lessons.update', $lesson) }}" class="teacher-editor-grid">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="course_section_id" value="{{ $section->id }}">

                                    <label>
                                        <span>عنوان درس</span>
                                        <input name="title" value="{{ $lesson->title }}" required>
                                    </label>

                                    <label>
                                        <span>Slug</span>
                                        <input name="slug" value="{{ $lesson->slug }}" required dir="ltr">
                                    </label>

                                    <label>
                                        <span>نوع</span>
                                        <select name="type">
                                            @foreach(['video' => 'ویدئو', 'text' => 'متن', 'file' => 'فایل', 'quiz' => 'کوئیز', 'live' => 'کلاس زنده'] as $value => $label)
                                                <option value="{{ $value }}" @selected($lesson->type === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </label>

                                    <label>
                                        <span>مدت (ثانیه)</span>
                                        <input type="number" name="duration_seconds" min="0" value="{{ $lesson->duration_seconds }}">
                                    </label>

                                    <label>
                                        <span>وضعیت</span>
                                        <select name="status">
                                            <option value="draft" @selected($lesson->status === 'draft')>پیش‌نویس</option>
                                            <option value="published" @selected($lesson->status === 'published')>منتشر</option>
                                            <option value="archived" @selected($lesson->status === 'archived')>آرشیو</option>
                                        </select>
                                    </label>

                                    <label class="teacher-switch-field">
                                        <input type="hidden" name="is_free" value="0">
                                        <input type="checkbox" name="is_free" value="1" @checked($lesson->is_free)>
                                        <span>این درس برای کاربران بدون پرداخت قابل مشاهده باشد.</span>
                                    </label>

                                    <label class="wide">
                                        <span>خلاصه</span>
                                        <input name="summary" value="{{ $lesson->summary }}">
                                    </label>

                                    <label class="wide">
                                        <span>متن / سناریوی درس</span>
                                        <textarea name="content" rows="7">{{ $lesson->content }}</textarea>
                                    </label>

                                    <button type="submit" class="teacher-builder-btn primary">ذخیره تغییرات</button>
                                </form>
                            </details>

                            <details class="teacher-media-uploader">
                                <summary>+ افزودن ویدئو / PDF / فایل آموزشی</summary>
                                <form method="POST" action="{{ route('teacher.lessons.media.store', $lesson) }}" enctype="multipart/form-data">
                                    @csrf

                                    <div class="teacher-editor-grid">
                                        <label class="wide">
                                            <span>فایل</span>
                                            <input type="file" name="media" required data-media-file>
                                        </label>

                                        <label>
                                            <span>نوع فایل</span>
                                            <select name="collection" data-media-type>
                                                <option value="video">ویدئو</option>
                                                <option value="pdf">PDF</option>
                                                <option value="resource">فایل آموزشی</option>
                                                <option value="thumbnail">تصویر</option>
                                            </select>
                                        </label>

                                        <label>
                                            <span>دسترسی</span>
                                            <select name="access">
                                                <option value="course">طبق دسترسی دوره</option>
                                                <option value="free">رایگان / پیش‌نمایش</option>
                                                <option value="paid" @disabled($course->isFree())>نیازمند پرداخت{{ $course->isFree() ? ' (دوره رایگان)' : '' }}</option>
                                            </select>
                                        </label>

                                        <label>
                                            <span>ترتیب</span>
                                            <input type="number" name="sort_order" value="{{ $lesson->media->count() }}" min="0">
                                        </label>

                                        <label class="teacher-switch-field">
                                            <input type="hidden" name="downloadable" value="0">
                                            <input type="checkbox" name="downloadable" value="1" checked data-media-downloadable>
                                            <span>دانش‌آموز اجازه دانلود داشته باشد.</span>
                                        </label>
                                    </div>

                                    <button type="submit" class="teacher-builder-btn primary">آپلود محتوا</button>
                                </form>
                            </details>

                            <form method="POST" action="{{ route('teacher.lessons.destroy', $lesson) }}" class="teacher-lesson-delete" data-confirm="این درس و تمام فایل‌های متصل به آن حذف شود؟">
                                @csrf
                                @method('DELETE')
                                <button type="submit">حذف درس</button>
                            </form>
                        </article>
                    @empty
                        <div class="teacher-builder-empty">این سرفصل هنوز درسی ندارد. اولین درس را از همین بخش بساز.</div>
                    @endforelse

                    @if($section->id)
                        <details class="teacher-add-lesson">
                            <summary>+ ساخت درس جدید در این سرفصل</summary>
                            <form method="POST" action="{{ route('teacher.lessons.store') }}" class="teacher-editor-grid">
                                @csrf
                                <input type="hidden" name="course_section_id" value="{{ $section->id }}">

                                <label>
                                    <span>عنوان</span>
                                    <input name="title" required placeholder="مثلاً: معرفی متغیرها">
                                </label>

                                <label>
                                    <span>Slug</span>
                                    <input name="slug" required dir="ltr" placeholder="variables-intro">
                                </label>

                                <label>
                                    <span>نوع درس</span>
                                    <select name="type">
                                        <option value="video">ویدئو</option>
                                        <option value="text">متن</option>
                                        <option value="file">فایل</option>
                                        <option value="quiz">کوئیز</option>
                                        <option value="live">کلاس زنده</option>
                                    </select>
                                </label>

                                <label>
                                    <span>مدت (ثانیه)</span>
                                    <input name="duration_seconds" type="number" min="0" value="0">
                                </label>

                                <label>
                                    <span>وضعیت</span>
                                    <select name="status">
                                        <option value="draft">پیش‌نویس</option>
                                        <option value="published">منتشر</option>
                                    </select>
                                </label>

                                <label class="teacher-switch-field">
                                    <input type="hidden" name="is_free" value="0">
                                    <input type="checkbox" name="is_free" value="1">
                                    <span>درس رایگان / پیش‌نمایش باشد.</span>
                                </label>

                                <label class="wide">
                                    <span>خلاصه</span>
                                    <input name="summary">
                                </label>

                                <label class="wide">
                                    <span>متن اولیه</span>
                                    <textarea name="content" rows="5"></textarea>
                                </label>

                                <button type="submit" class="teacher-builder-btn primary">ساخت درس</button>
                            </form>
                        </details>
                    @endif
                </div>
            </section>
        @empty
            <div class="teacher-builder-empty large">
                <strong>هنوز سرفصلی ساخته نشده است.</strong>
                <p>اول ساختار دوره را با چند سرفصل مشخص کن، بعد درس‌ها و فایل‌های هر بخش را بساز.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
