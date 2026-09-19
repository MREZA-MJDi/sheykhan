@extends('layouts.teacher')
@section('title','تکالیف | شیخان')
@section('header-title','تکالیف')
@section('content')
<div class="teacher-detail-page">
    @if(session('success'))<div class="teacher-builder-alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="teacher-builder-alert danger">@foreach($errors->all() as $error)<span>{{ $error }}</span>@endforeach</div>@endif

    <section class="teacher-list-hero">
        <div><span class="teacher-kicker">ASSIGNMENTS</span><h2>تکالیف</h2><p>تکلیف بساز، وضعیت انتشار را کنترل کن و پاسخ‌های دانش‌آموزان را تصحیح کن.</p></div>
        <a href="{{ route('teacher.assignments.create') }}" class="teacher-builder-btn primary" style="background:#5b5ce8;color:#fff">+ تکلیف جدید</a>
    </section>

    <section class="teacher-student-grid">
        @forelse($assignments as $assignment)
            <article class="teacher-course-card">
                <div class="teacher-course-card-top">
                    <div><span class="teacher-kicker">{{ $assignment->classroom?->title ?? 'عمومی دوره' }}</span><h3>{{ $assignment->title }}</h3></div>
                    <span class="teacher-content-status {{ $assignment->status === 'published' ? 'published' : 'draft' }}">{{ $assignment->status === 'published' ? 'منتشر' : ($assignment->status === 'closed' ? 'بسته' : 'پیش‌نویس') }}</span>
                </div>
                <p class="teacher-course-card-desc">موعد: {{ $assignment->due_at?->format('Y/m/d H:i') ?? 'بدون موعد' }} · نمره کل: {{ $assignment->max_score ?? '—' }}</p>
                <div class="teacher-course-card-meta"><span>{{ $assignment->submitted_count }} تحویل</span><span>{{ $assignment->pending_review_count }} در انتظار تصحیح</span></div>
                <div class="teacher-course-card-actions">
                    <a class="primary" href="{{ route('teacher.assignments.submissions',$assignment) }}">بررسی پاسخ‌ها</a>
                    <a href="{{ route('teacher.assignments.edit',$assignment) }}">ویرایش</a>
                    <form method="POST" action="{{ route('teacher.assignments.destroy',$assignment) }}" data-confirm="تکلیف حذف شود؟">
                        @csrf @method('DELETE')
                        <button type="submit" class="teacher-builder-delete">حذف</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="teacher-detail-panel teacher-detail-empty large">تکلیفی ثبت نشده است.</div>
        @endforelse
    </section>
</div>
@endsection
