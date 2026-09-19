@extends('layouts.teacher')

@section('title', 'دوره‌های من | شیخان')
@section('header-title', 'دوره‌های من')

@section('content')
<div class="teacher-detail-page">
    <section class="teacher-list-hero">
        <div>
            <span class="teacher-kicker">MY COURSES</span>
            <h2>دوره‌های من</h2>
            <p>از ساختار دوره و قیمت‌گذاری تا محتوای درس، فایل‌ها و گزارش یادگیری از همین‌جا مدیریت می‌شوند.</p>
        </div>
        <a href="{{ route('teacher.courses.create') }}" class="teacher-builder-btn primary" style="background:#5b5ce8;color:#fff">+ ساخت دوره</a>
    </section>

    @if(session('success'))
        <div class="teacher-builder-alert success">{{ session('success') }}</div>
    @endif

    <section class="teacher-detail-stats">
        <article><span>دوره فعال</span><strong>{{ $courses->where('status','published')->count() }}</strong></article>
        <article><span>دانش‌آموز</span><strong>{{ $courses->sum('active_students_count') }}</strong></article>
        <article><span>سرفصل</span><strong>{{ $courses->sum('sections_count') }}</strong></article>
        <article><span>دوره کل</span><strong>{{ $courses->count() }}</strong></article>
    </section>

    <section class="teacher-course-grid">
        @forelse($courses as $course)
            <article class="teacher-course-card">
                <div class="teacher-course-card-top">
                    <div>
                        <span class="teacher-kicker">{{ $course->academy?->name }}</span>
                        <h3>{{ $course->title }}</h3>
                    </div>
                    <span class="teacher-content-status {{ $course->status === 'published' ? 'published' : ($course->status === 'draft' ? 'draft' : 'archived') }}">
                        {{ $course->status === 'published' ? 'منتشر' : ($course->status === 'draft' ? 'پیش‌نویس' : 'آرشیو') }}
                    </span>
                </div>

                <p class="teacher-course-card-desc">{{ $course->short_description ?: 'برای این دوره هنوز توضیح کوتاهی ثبت نشده است.' }}</p>

                <div class="teacher-course-card-meta">
                    <span>{{ $course->sections_count }} سرفصل</span>
                    <span>{{ $course->active_students_count }} دانش‌آموز</span>
                    @if($course->isFree())
                        <span class="free">رایگان</span>
                    @else
                        <span class="premium">{{ number_format((float)$course->price, 0, '.', ',') }} تومان</span>
                    @endif
                </div>

                <div class="teacher-course-card-actions">
                    <a class="primary" href="{{ route('teacher.courses.content', $course) }}">ساخت محتوای دوره</a>
                    <a href="{{ route('teacher.courses.progress', $course) }}">گزارش یادگیری</a>
                    <a href="{{ route('teacher.courses.edit', $course) }}">تنظیمات دوره</a>
                </div>
            </article>
        @empty
            <div class="teacher-detail-panel teacher-detail-empty large">
                هنوز دوره‌ای به شما اختصاص داده نشده است.
            </div>
        @endforelse
    </section>
</div>
@endsection
