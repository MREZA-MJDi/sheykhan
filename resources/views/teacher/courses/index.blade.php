@extends('layouts.teacher')

@section('title', 'دوره‌های من | شیخان')
@section('header-title', 'دوره‌های من')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">آموزش</span>
            <h1 class="teacher-workspace-title">دوره‌های من</h1>
            <p class="teacher-workspace-description">دوره‌ها، قیمت‌گذاری، محتوای آموزشی و روند یادگیری دانش‌آموزان را از یک فضای یکپارچه مدیریت کن.</p>
        </div>
        <div class="teacher-workspace-actions">
            <a href="{{ route('teacher.courses.create') }}" class="teacher-workspace-btn primary">+ ساخت دوره</a>
        </div>
    </header>

    @if(session('success'))
        <div class="teacher-workspace-alert success">{{ session('success') }}</div>
    @endif

    <section class="teacher-workspace-stats">
        <div class="teacher-workspace-stat"><small>کل دوره‌ها</small><strong>{{ App\SupportPersianUi::digits($courses->total()) }}</strong><span>دوره تحت مدیریت</span></div>
        <div class="teacher-workspace-stat"><small>در این صفحه</small><strong>{{ App\SupportPersianUi::digits($courses->count()) }}</strong><span>نمایش داده‌شده</span></div>
        <div class="teacher-workspace-stat"><small>منتشرشده</small><strong>{{ App\SupportPersianUi::digits($courses->getCollection()->where('status','published')->count()) }}</strong><span>آماده ارائه</span></div>
        <div class="teacher-workspace-stat"><small>دانش‌آموزان فعال</small><strong>{{ App\SupportPersianUi::digits($courses->getCollection()->sum('active_students_count')) }}</strong><span>در دوره‌های این صفحه</span></div>
    </section>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head">
            <div><h2>فهرست دوره‌ها</h2><p>آخرین دوره‌های به‌روزشده در ابتدا قرار دارند.</p></div>
            <span class="teacher-workspace-chip">{{ App\SupportPersianUi::digits($courses->currentPage()) }} / {{ App\SupportPersianUi::digits($courses->lastPage()) }}</span>
        </div>

        @forelse($courses as $course)
            <div class="teacher-workspace-list">
                <article class="teacher-workspace-item">
                    <div class="teacher-workspace-item-main">
                        <strong class="teacher-workspace-item-title">{{ $course->title }}</strong>
                        <div class="teacher-workspace-item-meta">
                            <span>{{ $course->academy?->name ?: 'آموزشگاه' }}</span>
                            <span>{{ App\SupportPersianUi::digits($course->sections_count) }} بخش</span>
                            <span>{{ App\SupportPersianUi::digits($course->classrooms_count) }} کلاس</span>
                            <span>{{ App\SupportPersianUi::digits($course->active_students_count) }} دانش‌آموز فعال</span>
                        </div>
                    </div>
                    <div class="teacher-workspace-item-actions">
                        @if($course->access_type === 'free')
                            <span class="teacher-workspace-chip success">رایگان</span>
                        @else
                            <span class="teacher-workspace-chip warning">{{ App\SupportPersianUi::digits(number_format((float)$course->price,0,'.','٬')) }} تومان</span>
                        @endif
                        <span class="teacher-workspace-chip {{ $course->status === 'published' ? 'success' : ($course->status === 'archived' ? 'danger' : 'muted') }}">
                            {{ $course->status === 'published' ? 'منتشرشده' : ($course->status === 'archived' ? 'آرشیو' : 'پیش‌نویس') }}
                        </span>
                        <a href="{{ route('teacher.courses.content',$course) }}" class="teacher-workspace-link primary">محتوا</a>
                        <a href="{{ route('teacher.courses.progress',$course) }}" class="teacher-workspace-link">پیشرفت</a>
                        <a href="{{ route('teacher.courses.edit',$course) }}" class="teacher-workspace-link">تنظیمات</a>
                        @if($course->isPublished())
                            <a href="{{ route('courses.show',$course) }}" class="teacher-workspace-link" target="_blank" rel="noopener">نمایش سایت</a>
                        @endif
                    </div>
                </article>
            </div>
        @empty
            <div class="teacher-workspace-empty">
                <strong>هنوز دوره‌ای نداری.</strong>
                اولین دوره را بساز و بعد محتوا، کلاس و ارزیابی‌های آن را اضافه کن.
            </div>
        @endforelse

        @if($courses->hasPages())
            <div class="teacher-workspace-pagination">{{ $courses->links('components.navigation.pagination') }}</div>
        @endif
    </section>
</div>
@endsection
