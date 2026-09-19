@extends('layouts.teacher')

@section('title', 'کلاس ' . $classroom->title . ' | شیخان')
@section('header-title', 'جزئیات کلاس')

@section('content')
<div class="teacher-detail-page">
    @if(session('success'))
        <div class="teacher-builder-alert success">{{ session('success') }}</div>
    @endif

    <section class="teacher-detail-hero">
        <div>
            <span class="teacher-kicker">CLASSROOM</span>
            <h2>{{ $classroom->title }}</h2>
            <p>{{ $classroom->course?->title }} · کد کلاس {{ $classroom->code }}</p>
        </div>
        <div class="teacher-detail-hero-actions">
            <a href="{{ route('teacher.classrooms.attendance.edit', $classroom) }}" class="teacher-builder-btn primary">حضور و غیاب</a>
            <a href="{{ route('teacher.schedule.index') }}" class="teacher-builder-btn ghost">برنامه هفتگی</a>
        </div>
    </section>

    <section class="teacher-detail-stats">
        <article><span>دانش‌آموز فعال</span><strong>{{ $classroom->students->count() }}</strong></article>
        <article><span>ظرفیت</span><strong>{{ $classroom->capacity ?: '∞' }}</strong></article>
        <article><span>جلسات هفتگی</span><strong>{{ $classroom->schedules->count() }}</strong></article>
        <article><span>وضعیت</span><strong>{{ $classroom->status === 'active' ? 'فعال' : $classroom->status }}</strong></article>
    </section>

    <div class="teacher-detail-grid">
        <section class="teacher-detail-panel">
            <header><div><span class="teacher-kicker">ROSTER</span><h3>لیست دانش‌آموزان</h3></div></header>

            <div class="teacher-roster">
                @forelse($classroom->students as $student)
                    <a href="{{ route('teacher.students.show', $student) }}" class="teacher-roster-row">
                        <span class="teacher-roster-avatar">{{ mb_substr($student->name, 0, 1) }}</span>
                        <span class="teacher-roster-copy">
                            <strong>{{ $student->name }}</strong>
                            <small>{{ $student->studentProfile?->grade ? 'پایه ' . $student->studentProfile->grade : $student->email }}</small>
                        </span>
                        <span class="teacher-roster-arrow">←</span>
                    </a>
                @empty
                    <div class="teacher-detail-empty">هنوز دانش‌آموزی در این کلاس ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="teacher-detail-panel">
            <header><div><span class="teacher-kicker">SCHEDULE</span><h3>برنامه کلاس</h3></div></header>
            <div class="teacher-schedule-list">
                @forelse($classroom->schedules as $schedule)
                    <div class="teacher-schedule-row">
                        <span>{{ ['یکشنبه','دوشنبه','سه‌شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه'][$schedule->weekday] ?? 'روز' }}</span>
                        <strong>{{ IlluminateSupportCarbon::parse($schedule->start_time)->format('H:i') }} — {{ IlluminateSupportCarbon::parse($schedule->end_time)->format('H:i') }}</strong>
                    </div>
                @empty
                    <div class="teacher-detail-empty">برای این کلاس برنامه‌ای ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
