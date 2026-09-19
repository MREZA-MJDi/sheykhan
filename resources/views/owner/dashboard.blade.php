@extends('layouts.owner')

@section('title', 'مدیریت آموزشگاه | شیخان')
@section('header-title', 'مدیریت آموزشگاه')

@section('content')
<div class="owner-dashboard dashboard-fade-in">
    @php($academy = $academies->first())

    <section class="owner-hero">
        <div class="owner-hero-copy">
            <span class="owner-kicker">مرکز کنترل آموزشگاه</span>
            <h2>{{ $academy?->name ?? 'آموزشگاه شما' }} را از یک صفحه مدیریت کن.</h2>
            <p>
                وضعیت دوره‌ها، مدرس‌ها، دانش‌آموزان، کلاس‌ها و عملکرد آموزشی در یک داشبورد واقعی و قابل پیگیری.
            </p>
            <div class="owner-hero-actions">
                @if($academy)
                    <a href="{{ route('owner.academy.edit', $academy) }}" class="owner-btn">تنظیمات آموزشگاه</a>
                    <a href="{{ route('owner.people.index', $academy) }}" class="owner-btn ghost">مدیریت اعضا</a>
                @endif
            </div>
        </div>

        <div class="owner-hero-art" aria-hidden="true">
            <div class="owner-orb one"></div>
            <div class="owner-orb two"></div>
            <div class="owner-hero-stat">
                <small>فروش ثبت‌شده</small>
                <strong>{{ number_format($metrics['sales'], 0, '.', ',') }}</strong>
                <span>تومان در ثبت‌نام‌های فعال</span>
            </div>
        </div>
    </section>

    <section class="owner-stats">
        <article class="owner-stat-card"><span>دوره‌ها</span><strong>{{ $metrics['courses'] }}</strong><small>{{ $metrics['published'] }} دوره منتشرشده</small></article>
        <article class="owner-stat-card"><span>مدرس‌ها</span><strong>{{ $metrics['teachers'] }}</strong><small>عضو فعال آموزشگاه</small></article>
        <article class="owner-stat-card"><span>دانش‌آموزان</span><strong>{{ $metrics['students'] }}</strong><small>عضو فعال آموزشگاه</small></article>
        <article class="owner-stat-card"><span>کلاس‌ها</span><strong>{{ $metrics['classrooms'] }}</strong><small>{{ $metrics['pendingReviews'] }} مورد نیازمند بررسی</small></article>
    </section>

    <div class="owner-grid">
        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div><h3>گزارش مدرس‌ها</h3><p>تعداد دوره، دانش‌آموز، فروش و میانگین پیشرفت</p></div>
                @if($academy)<a href="{{ route('owner.people.index', $academy) }}" class="owner-link">اعضا ←</a>@endif
            </div>

            <div class="owner-teacher-list">
                @forelse($teacherReports as $teacher)
                    <article class="owner-teacher-row">
                        <div class="owner-avatar">{{ mb_substr($teacher->name, 0, 1) }}</div>
                        <div>
                            <div class="owner-row-title">{{ $teacher->name }}</div>
                            <div class="owner-row-meta">{{ $teacher->course_count }} دوره · {{ $teacher->student_count }} دانش‌آموز · {{ $teacher->pending_reviews }} بررسی</div>
                            <div class="owner-progress"><span style="width:{{ min(100, max(0, $teacher->progress_average)) }}%"></span></div>
                        </div>
                        <div class="owner-teacher-metric">
                            <strong>{{ $teacher->progress_average }}٪</strong>
                            <small>{{ number_format((float)$teacher->sales, 0, '.', ',') }} تومان</small>
                        </div>
                    </article>
                @empty
                    <div class="owner-empty">هنوز مدرس فعالی در آموزشگاه ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div><h3>جلسات آنلاین آینده</h3><p>برنامه هفت روز بعد</p></div>
            </div>
            <div class="owner-live-list">
                @forelse($upcomingLiveClasses as $item)
                    <article class="owner-live-row">
                        <div class="owner-pill">{{ \Illuminate\Support\Carbon::parse($item->scheduled_at)->format('m/d H:i') }}</div>
                        <div>
                            <div class="owner-row-title">{{ $item->title }}</div>
                            <div class="owner-row-meta">{{ $item->course_title }} · {{ $item->teacher_name }}</div>
                        </div>
                        <span class="owner-pill success">{{ $item->classroom_title ?? 'آنلاین' }}</span>
                    </article>
                @empty
                    <div class="owner-empty">جلسه آنلاین آینده‌ای ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="dashboard-panel owner-panel">
        <div class="owner-panel-head">
            <div><h3>آخرین دوره‌ها</h3><p>وضعیت انتشار و تعداد دانش‌آموز فعال</p></div>
            <a href="{{ route('owner.courses.index') }}" class="owner-link">همه دوره‌ها ←</a>
        </div>

        <div class="owner-course-list">
            @forelse($recentCourses as $course)
                <article class="owner-course-row">
                    <div>
                        <div class="owner-row-title">{{ $course->title }}</div>
                        <div class="owner-row-meta">{{ $course->academy?->name }} · {{ $course->active_students_count }} دانش‌آموز</div>
                    </div>
                    <span class="owner-pill {{ $course->status === 'published' ? 'success' : '' }}">{{ $course->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}</span>
                    <a href="{{ route('owner.courses.edit', $course) }}" class="owner-pill">مدیریت</a>
                </article>
            @empty
                <div class="owner-empty">دوره‌ای هنوز ثبت نشده است.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
