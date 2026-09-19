@extends('layouts.teacher')

@section('title', 'پرونده ' . $student->name . ' | شیخان')
@section('header-title', 'پرونده دانش‌آموز')

@section('content')
<div class="teacher-detail-page">
    <section class="teacher-detail-hero student">
        <div class="teacher-student-identity">
            <span class="teacher-student-avatar">{{ mb_substr($student->name, 0, 1) }}</span>
            <div>
                <span class="teacher-kicker">STUDENT PROFILE</span>
                <h2>{{ $student->name }}</h2>
                <p>{{ $student->email }} · {{ $student->studentProfile?->grade ? 'پایه ' . $student->studentProfile->grade : 'پایه مشخص نشده' }}</p>
            </div>
        </div>
        <a href="{{ route('teacher.students.index') }}" class="teacher-builder-btn ghost">بازگشت به دانش‌آموزان</a>
    </section>

    <section class="teacher-detail-stats">
        <article><span>کلاس فعال</span><strong>{{ $classrooms->count() }}</strong></article>
        <article><span>دوره فعال</span><strong>{{ $enrollments->count() }}</strong></article>
        <article><span>فعالیت درسی</span><strong>{{ $progress->count() }}</strong></article>
        <article><span>تکلیف / آزمون</span><strong>{{ $assignmentCount }} / {{ $examAttemptCount }}</strong></article>
    </section>

    <div class="teacher-detail-grid">
        <section class="teacher-detail-panel">
            <header><div><span class="teacher-kicker">LEARNING ACTIVITY</span><h3>آخرین مشاهده‌های درس</h3></div></header>
            <div class="teacher-learning-feed">
                @forelse($progress as $item)
                    <div class="teacher-learning-row">
                        <div class="teacher-learning-icon">{{ $item->progress_percent >= 100 ? '✓' : '▶' }}</div>
                        <div>
                            <strong>{{ $item->lesson_title }}</strong>
                            <small>{{ $item->section_title }}</small>
                        </div>
                        <div class="teacher-learning-progress">
                            <strong>{{ round((float) $item->progress_percent) }}٪</strong>
                            <span><i style="width:{{ min(100, max(0, (float) $item->progress_percent)) }}%"></i></span>
                        </div>
                        <small>{{ $item->last_watched_at ? IlluminateSupportCarbon::parse($item->last_watched_at)->format('Y/m/d H:i') : '—' }}</small>
                    </div>
                @empty
                    <div class="teacher-detail-empty">هنوز هیچ مشاهده‌ای از محتوای این دانش‌آموز ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="teacher-detail-panel">
            <header><div><span class="teacher-kicker">CLASSROOMS</span><h3>کلاس‌های فعال</h3></div></header>
            <div class="teacher-mini-list">
                @forelse($classrooms as $classroom)
                    <a href="{{ route('teacher.classrooms.show', $classroom) }}">
                        <strong>{{ $classroom->title }}</strong>
                        <small>{{ $classroom->course?->title }}</small>
                    </a>
                @empty
                    <div class="teacher-detail-empty">کلاسی ندارد.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="teacher-detail-panel">
        <header><div><span class="teacher-kicker">ENROLLMENTS</span><h3>دوره‌های فعال</h3></div></header>
        <div class="teacher-enrollment-grid">
            @forelse($enrollments as $enrollment)
                <article>
                    <span>{{ $enrollment->course?->title }}</span>
                    <small>{{ $enrollment->started_at ? IlluminateSupportCarbon::parse($enrollment->started_at)->format('Y/m/d') : 'شروع نشده' }}</small>
                    <a href="{{ route('teacher.courses.progress', $enrollment->course) }}">گزارش دوره ←</a>
                </article>
            @empty
                <div class="teacher-detail-empty">دوره فعالی ندارد.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
