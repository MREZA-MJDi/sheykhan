@extends('layouts.teacher')
@section('title','حضور و غیاب | شیخان')
@section('header-title','حضور و غیاب')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">پیگیری</span>
            <h1 class="teacher-workspace-title">حضور و غیاب</h1>
            <p class="teacher-workspace-description">کلاس را انتخاب کن و وضعیت حضور دانش‌آموزان همان روز را ثبت یا اصلاح کن.</p>
        </div>
        <a href="{{ route('teacher.classrooms.index') }}" class="teacher-workspace-btn secondary">کلاس‌ها</a>
    </header>

    <section class="teacher-workspace-card">
        <div class="teacher-workspace-card-head"><div><h2>کلاس‌ها</h2><p>فقط کلاس‌هایی که واقعاً تحت مدیریت این استاد هستند نمایش داده می‌شوند.</p></div></div>
        <div class="teacher-workspace-grid" style="padding:14px">
            @forelse($classrooms as $classroom)
                <article class="teacher-workspace-card">
                    <div class="teacher-workspace-card-body">
                        <div class="teacher-workspace-card-title">{{ $classroom->title }}</div>
                        <div class="teacher-workspace-card-subtitle">{{ $classroom->course?->title ?: 'بدون دوره' }}</div>
                        <div class="teacher-workspace-mini-stats">
                            <div class="teacher-workspace-mini-stat"><strong>{{ App\SupportPersianUi::digits($classroom->active_students_count) }}</strong><span>دانش‌آموز</span></div>
                            <div class="teacher-workspace-mini-stat"><strong>{{ $classroom->capacity ? App\SupportPersianUi::digits($classroom->capacity) : '—' }}</strong><span>ظرفیت</span></div>
                        </div>
                        <div class="teacher-workspace-actions-row">
                            <a href="{{ route('teacher.classrooms.attendance.edit',$classroom) }}" class="teacher-workspace-link primary">ثبت حضور امروز</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="teacher-workspace-empty" style="grid-column:1/-1"><strong>کلاسی برای حضور و غیاب وجود ندارد.</strong>ابتدا یک کلاس تحت مدیریت خودت بساز.</div>
            @endforelse
        </div>
        @if($classrooms->hasPages())<div class="teacher-workspace-pagination">{{ $classrooms->links('components.navigation.pagination') }}</div>@endif
    </section>
</div>
@endsection
