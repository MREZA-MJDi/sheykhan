@extends('layouts.owner')
@section('title','مدیریت آموزشگاه | شیخان')
@section('header-title','مدیریت آموزشگاه')
@section('content')
    <div class="owner-dashboard dashboard-fade-in">@php($academy=$academies->first())
        <section class="owner-hero">
            <div class="owner-hero-copy"><span class="owner-kicker">مرکز کنترل آموزشگاه</span>
                <h1>{{ $academies->count() > 1 ? 'آموزشگاه‌هایت را از یک صفحه مدیریت کن.' : (($academy?->name ?? 'آموزشگاه شما').' را از یک صفحه مدیریت کن.') }}</h1>
                <p>وضعیت دوره‌ها، مدرس‌ها، دانش‌آموزان، کلاس‌ها، درآمد و فعالیت آموزشی.</p>
                <div class="owner-hero-actions">@if($academies->count() === 1 && $academy)<a
                        href="{{ route('owner.academy.edit',$academy) }}" class="owner-btn">تنظیمات آموزشگاه</a><a
                        href="{{ route('owner.people.index',$academy) }}" class="owner-btn ghost">مدیریت اعضا</a>@endif
                    <a href="{{ route('owner.reports.index') }}" class="owner-btn ghost">گزارش‌ها</a></div>
            </div>
            <div class="owner-hero-art" aria-hidden="true">
                <div class="owner-orb one"></div>
                <div class="owner-orb two"></div>
                <div class="owner-hero-stat"><small>درآمد
                        ثبت‌شده</small><strong>{{ number_format($metrics['sales'],0,'.',',') }}</strong><span>تومان از ledger</span>
                </div>
            </div>
        </section>
        <section class="owner-stats">
            <article class="owner-stat-card">
                <span>دوره‌ها</span><strong>{{ $metrics['courses'] }}</strong><small>{{ $metrics['published'] }}
                    منتشرشده</small></article>
            <article class="owner-stat-card"><span>مدرس‌ها</span><strong>{{ $metrics['teachers'] }}</strong><small>فعال
                    در همه آموزشگاه‌ها</small></article>
            <article class="owner-stat-card"><span>دانش‌آموزان</span><strong>{{ $metrics['students'] }}</strong><small>فعال
                    در همه آموزشگاه‌ها</small></article>
            <article class="owner-stat-card">
                <span>کلاس‌های فعال</span><strong>{{ $metrics['classrooms'] }}</strong><small>{{ $metrics['pendingReviews'] }}
                    نیازمند بررسی</small></article>
        </section>
        <section class="owner-academies-grid">
            @forelse($academies as $academy)
                <article class="owner-academy-card">
                    <div class="owner-academy-copy">
                        <span class="owner-academy-kicker">آموزشگاه</span>
                        <h2>{{ $academy->name }}</h2>
                        <p>{{ $academy->city ?: 'شهر ثبت نشده' }} · فعال</p>
                    </div>
                    <div class="owner-academy-actions">
                        <a href="{{ route('owner.people.index',$academy) }}" class="owner-pill">اعضا</a>
                        <a href="{{ route('owner.classrooms.index',$academy) }}" class="owner-pill">کلاس‌ها</a>
                        <a href="{{ route('owner.academy.edit',$academy) }}" class="owner-pill success">تنظیمات</a>
                    </div>
                </article>
            @empty
                <div class="dashboard-panel owner-panel owner-empty">برای این حساب هنوز آموزشگاه فعالی ثبت نشده است.
                </div>
            @endforelse
        </section>
        <div class="owner-grid">
            <section class="dashboard-panel owner-panel">
                <div class="owner-panel-head">
                    <div><h2>برآیند مدرس‌ها</h2>
                        <p>دوره، دانش‌آموز، فروش ثبت‌نامی و پیشرفت</p></div>
                    <a href="{{ route('owner.reports.index') }}" class="owner-link">جزئیات ←</a></div>
                <div class="owner-teacher-list">@forelse($teacherReports as $teacher)
                        <article class="owner-teacher-row">
                            <div class="owner-avatar">{{ mb_substr($teacher->name,0,1) }}</div>
                            <div>
                                <div class="owner-row-title">{{ $teacher->name }}</div>
                                <div class="owner-row-meta">{{ $teacher->course_count }} دوره
                                    · {{ $teacher->student_count }} دانش‌آموز · {{ $teacher->pending_reviews }} بررسی
                                </div>
                                <div class="owner-progress"><span
                                        style="width:{{ min(100,max(0,$teacher->progress_average)) }}%"></span></div>
                            </div>
                            <div class="owner-teacher-metric"><strong>{{ $teacher->progress_average }}
                                    ٪</strong><small>{{ number_format((float)$teacher->sales,0,'.',',') }} تومان</small>
                            </div>
                        </article>@empty
                        <div class="owner-empty">هنوز مدرس فعالی ثبت نشده است.</div>@endforelse</div>
            </section>
            <section class="dashboard-panel owner-panel">
                <div class="owner-panel-head">
                    <div><h2>جلسات آنلاین آینده</h2>
                        <p>هفت روز بعد</p></div>
                </div>
                <div class="owner-live-list">@forelse($upcomingLiveClasses as $item)
                        <article class="owner-live-row">
                            <div
                                class="owner-pill">{{ \Illuminate\Support\Carbon::parse($item->scheduled_at)->format('m/d H:i') }}</div>
                            <div>
                                <div class="owner-row-title">{{ $item->title }}</div>
                                <div class="owner-row-meta">{{ $item->course_title }} · {{ $item->teacher_name }}</div>
                            </div>
                            <span class="owner-pill success">{{ $item->classroom_title??'آنلاین' }}</span>
                        </article>@empty
                        <div class="owner-empty">جلسه آنلاینی ثبت نشده است.</div>@endforelse</div>
            </section>
        </div>
        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div><h2>آخرین دوره‌ها</h2>
                    <p>انتشار و دانش‌آموز فعال</p></div>
                <a href="{{ route('owner.courses.index') }}" class="owner-link">همه دوره‌ها ←</a></div>
            <div class="owner-course-list">@forelse($recentCourses as $course)
                    <article class="owner-course-row">
                        <div>
                            <div class="owner-row-title">{{ $course->title }}</div>
                            <div class="owner-row-meta">{{ $course->academy?->name }}
                                · {{ $course->active_students_count }} دانش‌آموز
                            </div>
                        </div>
                        <span
                            class="owner-pill {{ $course->status==='published'?'success':'' }}">{{ $course->status==='published'?'منتشرشده':'پیش‌نویس' }}</span><a
                            href="{{ route('owner.courses.show',$course) }}" class="owner-pill">بازبینی</a>
                    </article>@empty
                    <div class="owner-empty">دوره‌ای هنوز ثبت نشده است.</div>@endforelse</div>
        </section>
    </div>
@endsection
