@extends('layouts.owner')

@section('title', 'مدیریت آموزشگاه | شیخان')
@section('header-title', 'مرکز مدیریت')

@section('content')
<div class="owner-dashboard dashboard-fade-in">
    @if(session('success'))
        <div class="owner-alert owner-alert-success">{{ session('success') }}</div>
    @endif

    <section class="owner-hero">
        <div class="owner-hero-copy">
            <span class="owner-kicker">مرکز کنترل آموزشگاه</span>
            <h2>مدیریت آموزشگاه را از یک صفحه انجام بده.</h2>
            <p>دوره‌ها، مدرس‌ها، دانش‌آموزان، کلاس‌ها، ثبت‌نام‌ها و وضعیت آموزشی را با داده واقعی دنبال کن.</p>
            <div class="owner-hero-actions">
                @if($academies->first())
                    <a href="{{ route('owner.people.index', $academies->first()) }}" class="owner-btn">مدیریت اعضا</a>
                    <a href="{{ route('owner.classrooms.index', $academies->first()) }}" class="owner-btn ghost">مدیریت کلاس‌ها</a>
                @endif
                <a href="{{ route('owner.reports.index') }}" class="owner-btn ghost">گزارش کامل</a>
            </div>
        </div>
        <div class="owner-hero-art" aria-hidden="true">
            <div class="owner-orb one"></div>
            <div class="owner-orb two"></div>
            <div class="owner-hero-stat">
                <small>فروش ثبت‌شده</small>
                <strong>{{ number_format((float) $metrics['sales'], 0, '.', ',') }}</strong>
                <span>تومان · ثبت‌نام‌های فعال</span>
            </div>
        </div>
    </section>

    <section class="owner-stats owner-stats-six">
        <article class="owner-stat-card"><span>دوره‌ها</span><strong>{{ $metrics['courses'] }}</strong><small>{{ $metrics['published'] }} منتشرشده</small></article>
        <article class="owner-stat-card"><span>ثبت‌نام فعال</span><strong>{{ $metrics['activeEnrollments'] }}</strong><small>دانش‌آموز در دوره‌ها</small></article>
        <article class="owner-stat-card"><span>مدرس‌ها</span><strong>{{ $metrics['teachers'] }}</strong><small>عضو فعال</small></article>
        <article class="owner-stat-card"><span>دانش‌آموزان</span><strong>{{ $metrics['students'] }}</strong><small>عضو فعال</small></article>
        <article class="owner-stat-card"><span>کلاس‌ها</span><strong>{{ $metrics['classrooms'] }}</strong><small>کلاس فعال</small></article>
        <article class="owner-stat-card"><span>در انتظار بررسی</span><strong>{{ $metrics['pendingReviews'] + $metrics['pendingExams'] }}</strong><small>{{ $metrics['pendingReviews'] }} تکلیف · {{ $metrics['pendingExams'] }} آزمون</small></article>
    </section>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">اقدام‌های مهم</span><h2>چیزهایی که باید ببینی</h2></div><a href="{{ route('owner.reports.index') }}" class="owner-link">گزارش کامل ←</a></div>
        <div class="owner-action-grid">
            <a href="{{ route('owner.reports.index') }}" class="owner-action-card {{ ($metrics['pendingReviews'] + $metrics['pendingExams']) > 0 ? 'has-alert' : '' }}">
                <span class="owner-action-icon">✦</span>
                <div><small>بررسی آموزشی</small><strong>{{ $metrics['pendingReviews'] + $metrics['pendingExams'] }} مورد</strong><p>تکلیف و آزمون منتظر بررسی مدرس</p></div>
                <span class="owner-action-arrow">←</span>
            </a>
            <a href="{{ $academies->first() ? route('owner.classrooms.index', $academies->first()) : route('owner.dashboard') }}" class="owner-action-card">
                <span class="owner-action-icon">⌂</span>
                <div><small>کلاس‌ها</small><strong>{{ $metrics['classrooms'] }} کلاس</strong><p>ظرفیت، مدرس و زمان‌بندی کلاس‌ها</p></div>
                <span class="owner-action-arrow">←</span>
            </a>
            <a href="{{ route('owner.courses.index') }}" class="owner-action-card">
                <span class="owner-action-icon">◫</span>
                <div><small>دوره‌ها</small><strong>{{ $metrics['courses'] }} دوره</strong><p>انتشار، قیمت‌گذاری و فایل‌های دوره</p></div>
                <span class="owner-action-arrow">←</span>
            </a>
        </div>
    </section>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">آموزشگاه‌ها</span><h2>ساختار سازمانی</h2><p>آمار هر آموزشگاه را بدون از دست دادن نمای کلی ببین.</p></div></div>
        <div class="owner-academy-grid">
            @forelse($academies as $academy)
                <article class="owner-academy-card">
                    <div class="owner-academy-head"><div><strong>{{ $academy->name }}</strong><span>{{ $academy->city ?: 'موقعیت ثبت نشده' }}</span></div><span class="owner-status is-success">فعال</span></div>
                    <div class="owner-academy-metrics">
                        <div><small>دوره</small><strong>{{ $academy->course_count }}</strong></div>
                        <div><small>مدرس</small><strong>{{ $academy->teacher_count }}</strong></div>
                        <div><small>دانش‌آموز</small><strong>{{ $academy->student_count }}</strong></div>
                    </div>
                    <div class="owner-academy-actions">
                        <a href="{{ route('owner.academy.edit', $academy) }}">تنظیمات</a>
                        <a href="{{ route('owner.people.index', $academy) }}">اعضا</a>
                        <a href="{{ route('owner.classrooms.index', $academy) }}">کلاس‌ها</a>
                    </div>
                </article>
            @empty
                <div class="dashboard-panel owner-empty-card">هنوز آموزشگاه فعالی برای این حساب ثبت نشده است.</div>
            @endforelse
        </div>
    </section>

    <div class="owner-grid">
        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div><span class="owner-eyebrow">عملکرد مدرس‌ها</span><h3>وضعیت مدرس‌ها</h3><p>دوره، دانش‌آموز، پیشرفت و موارد نیازمند بررسی.</p></div>
                <a href="{{ route('owner.reports.index') }}" class="owner-link">مشاهده گزارش ←</a>
            </div>
            <div class="owner-teacher-list">
                @forelse($teacherReports as $teacher)
                    <article class="owner-teacher-row">
                        <div class="owner-avatar">{{ mb_substr($teacher->name, 0, 1) }}</div>
                        <div>
                            <div class="owner-row-title">{{ $teacher->name }}</div>
                            <div class="owner-row-meta">{{ $teacher->course_count }} دوره · {{ $teacher->student_count }} دانش‌آموز · {{ $teacher->pending_reviews }} بررسی باز</div>
                            <div class="owner-progress"><span style="width:{{ min(100, max(0, $teacher->progress_average)) }}%"></span></div>
                        </div>
                        <div class="owner-teacher-metric"><strong>{{ $teacher->progress_average }}٪</strong><small>{{ number_format((float) $teacher->sales, 0, '.', ',') }} تومان</small></div>
                    </article>
                @empty
                    <div class="owner-empty">هنوز مدرس فعالی در آموزشگاه ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head"><div><span class="owner-eyebrow">کلاس آنلاین</span><h3>جلسات هفت روز آینده</h3><p>برنامه نزدیک آموزشگاه.</p></div></div>
            <div class="owner-live-list">
                @forelse($upcomingLiveClasses as $item)
                    <article class="owner-live-row">
                        <div class="owner-pill">{{ \Illuminate\Support\Carbon::parse($item->scheduled_at)->format('m/d H:i') }}</div>
                        <div><div class="owner-row-title">{{ $item->title }}</div><div class="owner-row-meta">{{ $item->course_title }} · {{ $item->teacher_name ?: 'مدرس مشخص نشده' }}</div></div>
                        <span class="owner-pill success">{{ $item->classroom_title ?: 'آنلاین' }}</span>
                    </article>
                @empty
                    <div class="owner-empty">جلسه آنلاین آینده‌ای ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">عملکرد دوره‌ها</span><h2>دوره‌های اخیر</h2></div><a href="{{ route('owner.courses.index') }}" class="owner-link">همه دوره‌ها ←</a></div>
        <div class="owner-course-report-grid">
            @forelse($courseReports as $course)
                <article class="owner-course-report-card">
                    <div class="owner-course-report-head"><div><strong>{{ $course->title }}</strong><span>{{ $course->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}</span></div><span class="owner-status {{ $course->isFree() ? 'is-success' : 'is-warning' }}">{{ $course->isFree() ? 'رایگان' : 'پولی' }}</span></div>
                    <div class="owner-course-report-metrics"><div><small>دانش‌آموز</small><strong>{{ $course->active_students_count }}</strong></div><div><small>فروش</small><strong>{{ number_format((float) $course->sales, 0, '.', ',') }}</strong></div></div>
                    <div class="owner-course-report-teachers"><small>مدرس‌ها</small><span>{{ $course->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}</span></div>
                    <a href="{{ route('owner.courses.edit', $course) }}" class="owner-page-action">مدیریت دوره</a>
                </article>
            @empty
                <div class="dashboard-panel owner-empty-card">هنوز دوره‌ای برای گزارش وجود ندارد.</div>
            @endforelse
        </div>
    </section>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">ثبت‌نام‌های اخیر</span><h2>ورودی‌های جدید</h2></div><a href="{{ route('owner.people.index', $academies->first()) }}" class="owner-link {{ $academies->isEmpty() ? 'pointer-events-none opacity-40' : '' }}">مدیریت اعضا ←</a></div>
        <div class="owner-enrollment-list">
            @forelse($recentEnrollments as $enrollment)
                <article><span class="owner-avatar">{{ mb_substr($enrollment->student_name, 0, 1) }}</span><div><strong>{{ $enrollment->student_name }}</strong><small>{{ $enrollment->course_title }}</small></div><div class="owner-enrollment-price">{{ number_format((float) $enrollment->paid_amount, 0, '.', ',') }} تومان</div></article>
            @empty
                <div class="dashboard-panel owner-empty-card">ثبت‌نام جدیدی هنوز ثبت نشده است.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
