@extends('layouts.teacher')
@section('title','دانش‌آموزان | شیخان')
@section('header-title','دانش‌آموزان')
@section('content')
<div class="teacher-detail-page">
    <section class="teacher-list-hero">
        <div>
            <span class="teacher-kicker">STUDENTS</span>
            <h2>دانش‌آموزان من</h2>
            <p>به‌جای یک جدول خشک، پرونده یادگیری هر دانش‌آموز را از همین‌جا باز کن و آخرین فعالیتش را ببین.</p>
        </div>
        <span class="teacher-list-count">{{ $students->count() }} نفر</span>
    </section>

    <section class="teacher-student-grid">
        @forelse($students as $student)
            <a href="{{ route('teacher.students.show', $student) }}" class="teacher-student-card">
                <span class="teacher-student-card-avatar">{{ mb_substr($student->name, 0, 1) }}</span>
                <div>
                    <strong>{{ $student->name }}</strong>
                    <small>{{ $student->studentProfile?->grade ? 'پایه ' . $student->studentProfile->grade : $student->email }}</small>
                </div>
                <div class="teacher-student-card-stats">
                    <span><b>{{ $student->progress_items_count }}</b> فعالیت</span>
                    <span><b>{{ $student->assignment_submissions_count }}</b> تکلیف</span>
                    <span><b>{{ $student->exam_attempts_count }}</b> آزمون</span>
                </div>
                <span class="teacher-roster-arrow">←</span>
            </a>
        @empty
            <div class="teacher-detail-panel teacher-detail-empty large">دانش‌آموزی در کلاس‌های شما ثبت نشده است.</div>
        @endforelse
    </section>
</div>
@endsection
