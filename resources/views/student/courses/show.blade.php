@extends('layouts.student')

@section('title', $course->title . ' | شیخان')
@section('header-title', 'مسیر دوره')

@section('content')
    <div class="student-workspace-page">
        <section class="student-welcome" aria-labelledby="course-title">
            <div class="student-welcome-copy">
                <span class="student-kicker">دوره فعال</span>
                <h1 id="course-title">{{ $course->title }}</h1>
                <p>{{ $course->short_description ?: $course->description ?: 'مسیر یادگیری شما در این دوره.' }}</p>
            </div>
            <div class="student-welcome-actions">
                <a class="student-welcome-action primary" href="{{ route('student.courses.index') }}">همه دوره‌ها ←</a>
                <a class="student-welcome-action" href="{{ route('student.resources.index') }}">منابع آموزشی</a>
            </div>
        </section>

        @foreach($course->sections as $section)
            <section class="student-panel dashboard-panel" aria-labelledby="section-{{ $section->id }}">
                <div class="student-panel-head">
                    <div>
                        <span class="student-kicker" style="color:var(--panel-primary)">سرفصل</span>
                        <h2 id="section-{{ $section->id }}">{{ $section->title }}</h2>
                    </div>
                    <span class="student-status">{{ \App\Support\PersianUi::digits($section->lessons->count()) }} درس</span>
                </div>

                <div class="student-list">
                    @forelse($section->lessons as $lesson)
                        @php($isFree=(bool)$lesson->is_free)
                        @php($progress=(float)($lesson->student_progress ?? 0))
                        <article class="student-list-row">
                            <div class="student-date">
                                <strong>{{ \App\Support\PersianUi::digits(round($progress)) }}٪</strong>
                                <small>{{ $isFree ? 'پیش‌نمایش' : 'دسترسی' }}</small>
                            </div>
                            <div class="student-row-content">
                                <a class="student-row-title" href="{{ route('student.lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                                <span class="student-row-meta">
                                    {{ $lesson->summary ?: 'درس آموزشی' }}
                                </span>
                            </div>
                            <span class="student-status {{ $progress >= 100 ? 'success' : 'primary' }}">
                                {{ $progress >= 100 ? 'تکمیل' : ($isFree ? 'رایگان' : 'باز') }}
                            </span>
                        </article>
                    @empty
                        <div class="student-empty"><strong>درسی ثبت نشده است.</strong></div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection
