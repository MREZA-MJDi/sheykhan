@extends('layouts.teacher')
@section('title','کلاس‌های من | شیخان')
@section('header-title','کلاس‌های من')

@section('content')
<div class="teacher-workspace-page">
    <header class="teacher-workspace-head">
        <div class="teacher-workspace-head-copy">
            <span class="teacher-workspace-kicker">کلاس و گروه</span>
            <h1 class="teacher-workspace-title">کلاس‌های من</h1>
            <p class="teacher-workspace-description">کلاس‌ها، ظرفیت، برنامه هفتگی و حضور و غیاب را بدون مخلوط‌شدن با منطق دوره مدیریت کن.</p>
        </div>
        <div class="teacher-workspace-actions">
            <a href="{{ route('teacher.schedule.index') }}" class="teacher-workspace-btn secondary">برنامه هفتگی</a>
            <a href="{{ route('teacher.classrooms.create') }}" class="teacher-workspace-btn primary">+ ایجاد کلاس</a>
        </div>
    </header>

    @if(session('success'))
        <div class="teacher-workspace-alert success">{{ session('success') }}</div>
    @endif

    <section class="teacher-workspace-stats">
        <div class="teacher-workspace-stat"><small>کلاس‌های فعال</small><strong>{{ App\SupportPersianUi::digits($classrooms->total()) }}</strong><span>تحت مدیریت شما</span></div>
        <div class="teacher-workspace-stat"><small>در این صفحه</small><strong>{{ App\SupportPersianUi::digits($classrooms->count()) }}</strong><span>کلاس نمایش‌داده‌شده</span></div>
        <div class="teacher-workspace-stat"><small>دانش‌آموز فعال</small><strong>{{ App\SupportPersianUi::digits($classrooms->getCollection()->sum('active_students_count')) }}</strong><span>در کلاس‌های این صفحه</span></div>
        <div class="teacher-workspace-stat"><small>زمان‌های هفتگی</small><strong>{{ App\SupportPersianUi::digits($classrooms->getCollection()->sum('schedules_count')) }}</strong><span>برنامه ثبت‌شده</span></div>
    </section>

    <section class="teacher-workspace-grid">
        @forelse($classrooms as $classroom)
            <article class="teacher-workspace-card">
                <div class="teacher-workspace-card-body">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="teacher-workspace-card-title truncate">{{ $classroom->title }}</div>
                            <div class="teacher-workspace-card-subtitle">{{ $classroom->course?->title ?: 'بدون دوره' }} · کد {{ $classroom->code }}</div>
                        </div>
                        <span class="teacher-workspace-chip {{ $classroom->status === 'active' ? 'success' : 'muted' }}">{{ $classroom->status === 'active' ? 'فعال' : $classroom->status }}</span>
                    </div>

                    <div class="teacher-workspace-mini-stats">
                        <div class="teacher-workspace-mini-stat"><strong>{{ App\SupportPersianUi::digits($classroom->active_students_count) }}</strong><span>دانش‌آموز</span></div>
                        <div class="teacher-workspace-mini-stat"><strong>{{ $classroom->capacity ? App\SupportPersianUi::digits($classroom->capacity) : '—' }}</strong><span>ظرفیت</span></div>
                        <div class="teacher-workspace-mini-stat"><strong>{{ App\SupportPersianUi::digits($classroom->schedules_count) }}</strong><span>برنامه</span></div>
                    </div>

                    <div class="teacher-workspace-actions-row">
                        <a href="{{ route('teacher.classrooms.attendance.edit',$classroom) }}" class="teacher-workspace-link primary">حضور و غیاب</a>
                        <a href="{{ route('teacher.courses.progress',$classroom->course) }}" class="teacher-workspace-link">پیشرفت دوره</a>
                        <a href="{{ route('teacher.students.index') }}" class="teacher-workspace-link">دانش‌آموزان</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="teacher-workspace-card" style="grid-column:1/-1">
                <div class="teacher-workspace-empty"><strong>هنوز کلاسی ساخته نشده است.</strong>از همین صفحه یک کلاس به یکی از دوره‌های خودت متصل کن.</div>
            </div>
        @endforelse
    </section>

    @if($classrooms->hasPages())
        <div class="teacher-workspace-card teacher-workspace-pagination">{{ $classrooms->links('components.navigation.pagination') }}</div>
    @endif
</div>
@endsection
