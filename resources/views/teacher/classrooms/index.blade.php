@extends('layouts.teacher')
@section('title','کلاس‌های من | شیخان')
@section('header-title','کلاس‌های من')
@section('content')
<div class="teacher-detail-page">
    @if($errors->any())
        <div class="teacher-builder-alert danger">@foreach($errors->all() as $error)<span>{{ $error }}</span>@endforeach</div>
    @endif
    @if(session('success'))
        <div class="teacher-builder-alert success">{{ session('success') }}</div>
    @endif

    <section class="teacher-list-hero">
        <div>
            <span class="teacher-kicker">CLASSROOMS</span>
            <h2>کلاس‌های تحت مدیریت</h2>
            <p>هر کلاس یک workspace مستقل برای roster، حضور و غیاب، برنامه و پیگیری یادگیری است.</p>
        </div>
        <a href="{{ route('teacher.classrooms.create') }}" class="teacher-builder-btn primary" style="background:#5b5ce8;color:#fff">+ ایجاد کلاس</a>
    </section>

    <section class="teacher-class-grid">
        @forelse($classrooms as $classroom)
            <article class="teacher-class-card">
                <div class="teacher-class-card-head">
                    <div>
                        <span class="teacher-kicker">{{ $classroom->code }}</span>
                        <h3>{{ $classroom->title }}</h3>
                        <p>{{ $classroom->course?->title }}</p>
                    </div>
                    <span class="teacher-content-status {{ $classroom->status === 'active' ? 'published' : 'draft' }}">
                        {{ $classroom->status === 'active' ? 'فعال' : 'آرشیو' }}
                    </span>
                </div>

                <div class="teacher-class-metrics">
                    <div><span>دانش‌آموز</span><strong>{{ $classroom->active_students_count }}</strong></div>
                    <div><span>ظرفیت</span><strong>{{ $classroom->capacity ?: '∞' }}</strong></div>
                    <div><span>جلسه هفتگی</span><strong>{{ $classroom->schedules->count() }}</strong></div>
                </div>

                <div class="teacher-class-actions">
                    <a href="{{ route('teacher.classrooms.show', $classroom) }}">ورود به کلاس</a>
                    <a href="{{ route('teacher.classrooms.edit', $classroom) }}">ویرایش</a>
                    <a href="{{ route('teacher.classrooms.attendance.edit', $classroom) }}">حضور و غیاب</a>
                </div>

                @if($classroom->status === 'active')
                    <form method="POST" action="{{ route('teacher.classrooms.archive', $classroom) }}" data-confirm="این کلاس آرشیو شود؟" class="mt-2 text-left">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="teacher-builder-delete">آرشیو کلاس</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="teacher-detail-panel teacher-detail-empty large">هنوز کلاسی ساخته نشده است.</div>
        @endforelse
    </section>
</div>
@endsection
