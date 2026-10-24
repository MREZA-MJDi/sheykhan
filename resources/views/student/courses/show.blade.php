@extends('layouts.student')

@section('title', $course->title)
@section('header-title', 'جزئیات دوره')

@section('content')
@php
    $allLessons = $lessons;
    $completed = $allLessons->filter(fn ($lesson) => (float) ($lesson->student_progress ?? 0) >= 100)->count();
@endphp
<section class="page">
    <div class="hero">
        <div class="av"><svg viewBox="0 0 120 120" width="100%" height="100%" aria-hidden="true"><use href="#i-book"/></svg></div>
        <div class="txt">
            <h2>{{ $course->title }}</h2>
            <p>{{ $course->short_description ?: 'مسیر یادگیری این دوره را از همین‌جا دنبال کن.' }}</p>
            <span class="tag">{{ \App\Support\PersianUi::digits($completed) }} از {{ \App\Support\PersianUi::digits($allLessons->count()) }} درس تکمیل‌شده</span>
        </div>
    </div>
    @if($course->spotplayer_course_id)
        <div class="card" id="spotplayer-access">
            <div class="chead"><h2>ویدئوهای محافظت‌شدهٔ دوره</h2><span class="pill">SpotPlayer DRM</span></div>
            <p>لایسنس شخصی این دوره فقط پس از تأیید دسترسی آموزشی صادر می‌شود. کلید لایسنس را با فرد دیگری به اشتراک نگذار.</p>
            @if($spotplayerLicense)
                <label class="mt-3 grid gap-2">
                    <span>کلید لایسنس اختصاصی</span>
                    <input type="text" readonly dir="ltr" value="{{ $spotplayerLicense->license_key }}" onclick="this.select()" class="course-input text-left" aria-label="کلید لایسنس SpotPlayer">
                </label>
                <a class="btn s mt-3 inline-flex" href="https://app.spotplayer.ir/" target="_blank" rel="noopener noreferrer">باز کردن پلیر تحت‌وب SpotPlayer</a>
                <p class="mt-2 text-xs leading-6 text-slate-500">پلیر تحت‌وب SpotPlayer را باز کن و کلید بالا را در آن وارد کن. کلید API سامانه هرگز به مرورگر ارسال نمی‌شود.</p>
            @elseif($spotplayerConfigured)
                <form method="POST" action="{{ route('student.courses.spotplayer-license', $course) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn s">دریافت لایسنس و دسترسی به ویدئوها</button>
                </form>
                @error('spotplayer')<p class="mt-2 text-sm text-rose-600" role="alert">{{ $message }}</p>@enderror
            @else
                <p class="mt-3 rounded-xl bg-amber-50 p-3 text-sm leading-6 text-amber-900" role="status">اتصال SpotPlayer هنوز در سرور تنظیم نشده است. برای جلوگیری از صدور لایسنس آزمایشی، این قابلیت فعلاً غیرفعال است.</p>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="chead"><h2>محتوای دوره</h2><a class="pill" href="{{ route('student.courses.index') }}">بازگشت به دوره‌ها</a></div>
        @forelse($course->sections as $section)
            <div class="chead"><h2>{{ $section->title }}</h2><span class="tag">{{ \App\Support\PersianUi::digits($section->lessons->count()) }} درس</span></div>
            @forelse($section->lessons as $lesson)
                @php $progress = max(0, min(100, (float) ($lesson->student_progress ?? 0))); @endphp
                <div class="trk">
                    <div class="th"><svg class="i" aria-hidden="true"><use href="#i-book"/></svg></div>
                    <div>
                        <b>{{ $lesson->title }}</b>
                        <span>{{ $lesson->summary ?: ($lesson->type === 'video' ? 'درس ویدئویی' : 'محتوای آموزشی') }} · {{ \App\Support\PersianUi::number($progress) }}٪</span>
                        <div class="prog"><i style="width:{{ $progress }}%"></i></div>
                    </div>
                    <a class="pl" href="{{ route('student.lessons.show', $lesson->id) }}" aria-label="باز کردن درس {{ $lesson->title }}"><svg class="i" aria-hidden="true"><use href="#i-play"/></svg></a>
                </div>
            @empty
                <div class="empty">در این بخش هنوز درس منتشرشده‌ای نیست.</div>
            @endforelse
        @empty
            <div class="empty">محتوای منتشرشده‌ای برای این دوره وجود ندارد.</div>
        @endforelse
    </div>
</section>
@endsection
