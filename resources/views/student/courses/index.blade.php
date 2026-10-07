@extends('layouts.student')

@section('title', 'دوره‌های من | شیخان')
@section('header-title', 'دوره‌های من')

@section('content')
    <section class="student-panel dashboard-panel" aria-labelledby="student-courses-title">
        <div class="student-panel-head">
            <div>
                <span class="student-kicker" style="color:var(--panel-primary)">مسیر یادگیری</span>
                <h2 id="student-courses-title">دوره‌های فعال من</h2>
                <p>فقط دوره‌هایی که دسترسی آموزشی این حساب برای آن‌ها معتبر است.</p>
            </div>
            <span class="student-status primary">{{ \App\Support\PersianUi::digits($courses->count()) }} دوره</span>
        </div>

        <div class="student-course-list">
            @forelse($courses as $course)
                @php($p=max(0,min(100,(float)($course->learning_progress ?? 0))))
                <article class="student-course">
                    <div class="student-course-main">
                        <div class="student-course-title">
                            <a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
                        </div>
                        <div class="student-course-meta">
                            {{ $course->academy?->name ?? 'آکادمی شیخان' }}
                            · {{ $course->level ?: 'دوره آموزشی' }}
                        </div>
                    </div>
                    <div class="student-progress">
                        <div class="student-progress-label">
                            <span>پیشرفت</span>
                            <strong>{{ \App\Support\PersianUi::digits(round($p)) }}٪</strong>
                        </div>
                        <div class="student-progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($p) }}">
                            <span style="width:{{ $p }}%"></span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="student-empty">
                    <strong>هنوز دوره فعالی برای شما ثبت نشده است.</strong>
                    <span>پس از ثبت‌نام معتبر، مسیر آموزشی شما در این بخش نمایش داده می‌شود.</span>
                </div>
            @endforelse
        </div>

        @if($courses->isNotEmpty())
            <div class="student-detail-actions">
                @foreach($courses->take(1) as $course)
                    <a class="student-welcome-action primary" href="{{ route('student.courses.show', $course) }}">ادامه یادگیری ←</a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
