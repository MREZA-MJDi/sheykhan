@extends('layouts.owner')

@section('title','گزارش آموزشگاه | شیخان')
@section('header-title','گزارش آموزشگاه')

@section('content')
<div class="owner-page">
    <div class="owner-page-head">
        <div><span class="owner-eyebrow">گزارش مدیریتی</span><h1>تصویر کامل عملکرد آموزشگاه</h1><p>شاخص‌های آموزشی و مالی را کنار هم ببین و از هر مورد به صفحه مدیریت مربوط برو.</p></div>
        <a href="{{ route('owner.dashboard') }}" class="owner-page-action">بازگشت به داشبورد</a>
    </div>

    <section class="owner-stats owner-stats-six">
        <article class="owner-stat-card"><span>دوره</span><strong>{{ $metrics['courses'] }}</strong><small>{{ $metrics['published'] }} منتشرشده</small></article>
        <article class="owner-stat-card"><span>ثبت‌نام فعال</span><strong>{{ $metrics['activeEnrollments'] }}</strong><small>در تمام دوره‌ها</small></article>
        <article class="owner-stat-card"><span>دانش‌آموز</span><strong>{{ $metrics['students'] }}</strong><small>عضو فعال</small></article>
        <article class="owner-stat-card"><span>مدرس</span><strong>{{ $metrics['teachers'] }}</strong><small>عضو فعال</small></article>
        <article class="owner-stat-card"><span>کلاس</span><strong>{{ $metrics['classrooms'] }}</strong><small>کلاس فعال</small></article>
        <article class="owner-stat-card"><span>فروش</span><strong>{{ number_format((float) $metrics['sales'],0,'.',',') }}</strong><small>تومان</small></article>
    </section>

    <div class="owner-report-grid">
        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head"><div><span class="owner-eyebrow">مدرس‌ها</span><h3>گزارش عملکرد مدرس</h3><p>خروجی مناسب برای پیگیری کیفیت و بار کاری.</p></div></div>
            <div class="overflow-x-auto owner-report-table-wrap">
                <table class="owner-report-table">
                    <thead><tr><th>مدرس</th><th>دوره</th><th>دانش‌آموز</th><th>پیشرفت</th><th>بررسی باز</th><th>فروش</th></tr></thead>
                    <tbody>
                    @forelse($teacherReports as $report)
                        <tr><td><strong>{{ $report->name }}</strong></td><td>{{ $report->course_count }}</td><td>{{ $report->student_count }}</td><td><span class="owner-table-progress">{{ $report->progress_average }}٪</span></td><td>{{ $report->pending_reviews }}</td><td>{{ number_format((float) $report->sales,0,'.',',') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="owner-table-empty">گزارشی برای مدرس‌ها موجود نیست.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head"><div><span class="owner-eyebrow">اقدام</span><h3>موارد نیازمند بررسی</h3><p>مواردی که نباید در پنل مدیریت گم شوند.</p></div></div>
            <div class="owner-report-action-list">
                <div><span>تکالیف</span><strong>{{ $metrics['pendingReviews'] }}</strong><small>منتظر نمره‌دهی</small></div>
                <div><span>آزمون‌ها</span><strong>{{ $metrics['pendingExams'] }}</strong><small>نیازمند بررسی</small></div>
                <div><span>جلسات آنلاین</span><strong>{{ $upcomingLiveClasses->count() }}</strong><small>۷ روز آینده</small></div>
            </div>
        </section>
    </div>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">دوره‌ها</span><h2>گزارش دوره</h2></div><a href="{{ route('owner.courses.index') }}" class="owner-link">مدیریت دوره‌ها ←</a></div>
        <div class="owner-course-report-grid">
            @forelse($courseReports as $course)
                <article class="owner-course-report-card">
                    <div class="owner-course-report-head"><div><strong>{{ $course->title }}</strong><span>{{ $course->academy?->name }}</span></div><span class="owner-status {{ $course->isFree() ? 'is-success' : 'is-warning' }}">{{ $course->isFree() ? 'رایگان' : 'پولی' }}</span></div>
                    <div class="owner-course-report-metrics"><div><small>دانش‌آموز</small><strong>{{ $course->active_students_count }}</strong></div><div><small>فروش</small><strong>{{ number_format((float) $course->sales,0,'.',',') }}</strong></div></div>
                    <div class="owner-course-report-teachers"><small>مدرس‌ها</small><span>{{ $course->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}</span></div>
                </article>
            @empty
                <div class="dashboard-panel owner-empty-card">دوره‌ای برای گزارش وجود ندارد.</div>
            @endforelse
        </div>
    </section>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">ثبت‌نام</span><h2>آخرین ثبت‌نام‌ها</h2></div></div>
        <div class="owner-enrollment-list">
            @forelse($recentEnrollments as $item)
                <article><span class="owner-avatar">{{ mb_substr($item->student_name,0,1) }}</span><div><strong>{{ $item->student_name }}</strong><small>{{ $item->course_title }}</small></div><span class="owner-enrollment-price">{{ number_format((float)$item->paid_amount,0,'.',',') }} تومان</span></article>
            @empty
                <div class="dashboard-panel owner-empty-card">ثبت‌نامی وجود ندارد.</div>
            @endforelse
        </div>
    </section>

    <section class="owner-section">
        <div class="owner-section-head"><div><span class="owner-eyebrow">جلسات</span><h2>هفت روز آینده</h2></div></div>
        <div class="owner-live-list owner-live-list-wide">
            @forelse($upcomingLiveClasses as $item)
                <article class="owner-live-row"><div class="owner-pill">{{ \Illuminate\Support\Carbon::parse($item->scheduled_at)->format('m/d H:i') }}</div><div><div class="owner-row-title">{{ $item->title }}</div><div class="owner-row-meta">{{ $item->course_title }} · {{ $item->teacher_name ?: 'مدرس مشخص نشده' }}</div></div><span class="owner-pill success">{{ $item->classroom_title ?: 'آنلاین' }}</span></article>
            @empty
                <div class="dashboard-panel owner-empty-card">جلسه آنلاین آینده‌ای ثبت نشده است.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
