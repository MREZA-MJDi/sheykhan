@extends('layouts.teacher')
@section('title','محتوای دوره | شیخان')
@section('header-title','محتوای دوره')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">محتوای آموزشی</span>
            <h1 class="teacher-workspace-title">{{ $course->title }}</h1>
            <p class="teacher-workspace-description">{{ App\Support\PersianUi::digits($course->sections->count()) }} سرفصل · درس‌ها و فایل‌های هر سرفصل از همین صفحه مدیریت می‌شوند.</p>
        </div>
        <div class="teacher-workspace-actions">
            <a href="{{ route('teacher.courses.progress',$course) }}" class="teacher-workspace-btn secondary">پیشرفت دانش‌آموزان</a>
            <a href="{{ route('teacher.courses.edit',$course) }}" class="teacher-workspace-btn primary">تنظیمات دوره</a>
        </div>
    </header>

    @if(session('success'))<div class="teacher-workspace-alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="teacher-workspace-alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div><h2>ساختار دوره</h2><p>قبل از افزودن درس، سرفصل‌های دوره را به ترتیب آموزشی بساز.</p></div>
        </div>
        <form method="POST" action="{{ route('teacher.courses.sections.store',$course) }}" class="teacher-workspace-form-grid">
            @csrf
            <label class="teacher-workspace-field"><span>عنوان سرفصل</span><input name="title" value="{{ old('title') }}" placeholder="مثلاً فصل اول: حرکت‌شناسی" required></label>
            <label class="teacher-workspace-field"><span>توضیحات <small>اختیاری</small></span><input name="description" value="{{ old('description') }}" placeholder="هدف و محتوای این بخش"></label>
            <div style="grid-column:1/-1;display:flex;justify-content:flex-end"><button type="submit" class="teacher-workspace-btn primary">+ افزودن سرفصل</button></div>
        </form>
    </section>

    @forelse($course->sections as $section)
        <section class="teacher-workspace-card">
            <div class="teacher-workspace-card-head">
                <div><h2>{{ $section->title }}</h2><p>{{ App\Support\PersianUi::digits($section->lessons->count()) }} درس</p></div>
            </div>
            <div class="teacher-workspace-list">
                @forelse($section->lessons as $lesson)
                    <article class="teacher-workspace-item">
                        <div class="teacher-workspace-item-main">
                            <strong class="teacher-workspace-item-title">{{ $lesson->title }}</strong>
                            <div class="teacher-workspace-item-meta">
                                <span>{{ $lesson->type }}</span>
                                <span>{{ $lesson->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}</span>
                                <span>{{ App\Support\PersianUi::digits((int)($lesson->duration_seconds ?? 0)) }} ثانیه</span>
                                @if($lesson->media->isNotEmpty())<span>{{ App\Support\PersianUi::digits($lesson->media->count()) }} فایل</span>@endif
                            </div>
                        </div>
                        <div class="teacher-workspace-item-actions">
                            <form method="POST" action="{{ route('teacher.lessons.media.store',$lesson) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="file" name="media" required class="max-w-[210px] rounded-lg border border-slate-200 bg-white px-2 py-2 text-[8px]">
                                <input type="hidden" name="downloadable" value="0">
                                <label class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-2 text-[8px]">
                                    <input type="checkbox" name="downloadable" value="1" checked> قابل دانلود
                                </label>
                                <button type="submit" class="teacher-workspace-link primary">آپلود فایل</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="teacher-workspace-empty"><strong>درسی وجود ندارد.</strong>از فرم پایین اولین درس این سرفصل را بساز.</div>
                @endforelse
            </div>
        </section>
    @empty
        <section class="teacher-workspace-card"><div class="teacher-workspace-empty"><strong>این دوره هنوز سرفصل ندارد.</strong>ابتدا سرفصل‌های دوره را از مدیریت دوره بساز.</div></section>
    @endforelse

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>افزودن درس</h2><p>درس به یکی از سرفصل‌های موجود اضافه می‌شود.</p></div></div>
        <form method="POST" action="{{ route('teacher.lessons.store') }}" class="teacher-workspace-form-grid">
            @csrf
            <label class="teacher-workspace-field"><span>سرفصل</span><select name="course_section_id" required>@foreach($course->sections as $section)<option value="{{ $section->id }}">{{ $section->title }}</option>@endforeach</select></label>
            <label class="teacher-workspace-field"><span>نوع درس</span><select name="type"><option value="video">ویدیو</option><option value="text">متن</option><option value="file">فایل</option><option value="quiz">کوئیز</option><option value="live">زنده</option></select></label>
            <label class="teacher-workspace-field"><span>عنوان</span><input name="title" value="{{ old('title') }}" required></label>
            <label class="teacher-workspace-field"><span>Slug</span><input name="slug" value="{{ old('slug') }}" dir="ltr"></label>
            <label class="teacher-workspace-field" style="grid-column:1/-1"><span>خلاصه</span><input name="summary" value="{{ old('summary') }}"></label>
            <label class="teacher-workspace-field" style="grid-column:1/-1"><span>محتوا</span><textarea name="content" rows="6">{{ old('content') }}</textarea></label>
            <label class="teacher-workspace-field"><span>وضعیت</span><select name="status"><option value="draft">پیش‌نویس</option><option value="published">منتشرشده</option></select></label>
            <label class="teacher-workspace-field" style="display:flex;align-items:center;gap:8px"><input type="checkbox" name="is_free" value="1"><span>این درس رایگان باشد</span></label>
            <div style="grid-column:1/-1;display:flex;justify-content:flex-end"><button type="submit" class="teacher-workspace-btn primary">افزودن درس</button></div>
        </form>
    </section>
</div>
@endsection
