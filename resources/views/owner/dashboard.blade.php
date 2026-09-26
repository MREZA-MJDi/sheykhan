@extends('layouts.owner')

@section('title','مدیریت آموزشگاه | شیخان')
@section('header-title','داشبورد آموزشگاه')

@section('content')
@php($academy = $academies->first())
@php
    $executionLabels = [
        'running' => 'در حال اجرا',
        'scheduled' => 'برنامه‌ریزی‌شده',
        'finished' => 'پایان‌یافته',
        'active' => 'فعال',
        'archived' => 'آرشیو',
    ];
@endphp

<div class="owner-dashboard dashboard-fade-in">
    <section class="owner-hero">
        <div class="owner-hero-copy">
            <span class="owner-kicker">مرکز کنترل آموزشگاه</span>
            <h1>
                {{ $academies->count() > 1
                    ? 'وضعیت آموزشگاه‌ها را از یک صفحه مدیریت کن.'
                    : (($academy?->name ?? 'آموزشگاه شما').' را از یک صفحه مدیریت کن.') }}
            </h1>
            <p>
                اینجا تصویر واقعی عملیات آموزشی امروز را می‌بینی: کلاس‌های در حال اجرا، ظرفیت،
                حضور و غیاب، مدرس‌ها، دوره‌ها و مواردی که نیاز به پیگیری دارند.
            </p>

            <div class="owner-hero-actions">
                @if($academy)
                    <a href="{{ route('owner.classrooms.index',$academy) }}" class="owner-btn">مدیریت کلاس‌ها</a>
                    <a href="{{ route('owner.people.index',$academy) }}" class="owner-btn ghost">مدیریت اعضا</a>
                @endif
                <a href="{{ route('owner.reports.index') }}" class="owner-btn ghost">گزارش‌ها</a>
            </div>
        </div>

        <div class="owner-hero-art" aria-hidden="true">
            <div class="owner-orb one"></div>
            <div class="owner-orb two"></div>
            <div class="owner-hero-stat">
                <small>درآمد خالص ثبت‌شده</small>
                <strong>{{ number_format($metrics['sales'],0,'.',',') }}</strong>
                <span>از ledger پرداخت‌های تکمیل‌شده</span>
            </div>
        </div>
    </section>

    <section class="owner-stats">
        <article class="owner-stat-card">
            <span>کلاس‌های فعال</span>
            <strong>{{ $metrics['classrooms'] }}</strong>
            <small>{{ $metrics['liveNow'] }} کلاس آنلاین در حال اجرا</small>
        </article>
        <article class="owner-stat-card">
            <span>دانش‌آموز فعال</span>
            <strong>{{ $metrics['students'] }}</strong>
            <small>{{ $metrics['todayAttendanceRate'] === null ? 'حضور امروز ثبت نشده' : 'حضور امروز '.$metrics['todayAttendanceRate'].'٪' }}</small>
        </article>
        <article class="owner-stat-card">
            <span>مدرس فعال</span>
            <strong>{{ $metrics['teachers'] }}</strong>
            <small>{{ $metrics['courses'] }} دوره در آموزشگاه‌ها</small>
        </article>
        <article class="owner-stat-card">
            <span>نیازمند توجه</span>
            <strong>{{ $metrics['pendingReviews'] + $metrics['capacityAlerts'] + $metrics['classroomsWithoutTeacher'] }}</strong>
            <small>{{ $metrics['pendingReviews'] }} بررسی · {{ $metrics['capacityAlerts'] }} کلاس نزدیک ظرفیت</small>
        </article>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <article class="dashboard-panel p-5">
            <span class="text-[10px] font-bold text-slate-500">حضور امروز</span>
            <div class="mt-2 flex items-end gap-2">
                <strong class="text-2xl font-black">{{ $metrics['todayAttendanceRate'] === null ? '—' : $metrics['todayAttendanceRate'].'٪' }}</strong>
                <span class="pb-1 text-[10px] text-slate-400">بر اساس رکوردهای امروز</span>
            </div>
        </article>
        <article class="dashboard-panel p-5">
            <span class="text-[10px] font-bold text-slate-500">دوره‌های منتشرشده</span>
            <div class="mt-2 flex items-end gap-2">
                <strong class="text-2xl font-black">{{ $metrics['published'] }}</strong>
                <span class="pb-1 text-[10px] text-slate-400">از {{ $metrics['courses'] }} دوره</span>
            </div>
        </article>
        <article class="dashboard-panel p-5">
            <span class="text-[10px] font-bold text-slate-500">موارد کنترل</span>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="owner-pill">{{ $metrics['pendingReviews'] }} تکلیف معوق</span>
                <span class="owner-pill">{{ $metrics['capacityAlerts'] }} ظرفیت بالا</span>
                <span class="owner-pill">{{ $metrics['classroomsWithoutTeacher'] }} بدون مدرس</span>
            </div>
        </article>
    </section>

    @if($classrooms->isNotEmpty())
        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div>
                    <h2>کلاس‌های امروز</h2>
                    <p>تصویر اجرایی کلاس‌ها از داده‌های واقعی سیستم</p>
                </div>
                @if($academy)
                    <a href="{{ route('owner.classrooms.index',$academy) }}" class="owner-link">همه کلاس‌ها ←</a>
                @endif
            </div>

            <div class="mt-4 grid gap-3 xl:grid-cols-2">
                @foreach($classrooms as $classroom)
                    <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-sm font-black">{{ $classroom->title }}</h3>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-slate-600">
                                        {{ $executionLabels[$classroom->execution_status] ?? 'فعال' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-500">
                                    {{ $classroom->course?->title }} · {{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}
                                </p>
                            </div>

                            <a href="{{ route('owner.classrooms.show', [$classroom->academy, $classroom]) }}"
                               class="owner-pill">
                                جزئیات
                            </a>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <div class="rounded-xl bg-white p-3 text-center">
                                <strong class="block text-sm font-black">{{ $classroom->active_students_count }}</strong>
                                <span class="text-[9px] text-slate-400">دانش‌آموز</span>
                            </div>
                            <div class="rounded-xl bg-white p-3 text-center">
                                <strong class="block text-sm font-black">{{ $classroom->capacity ?? '∞' }}</strong>
                                <span class="text-[9px] text-slate-400">ظرفیت</span>
                            </div>
                            <div class="rounded-xl bg-white p-3 text-center">
                                <strong class="block text-sm font-black">{{ $classroom->capacity_remaining ?? '∞' }}</strong>
                                <span class="text-[9px] text-slate-400">خالی</span>
                            </div>
                            <div class="rounded-xl bg-white p-3 text-center">
                                <strong class="block text-sm font-black">{{ $classroom->today_attendance_rate === null ? '—' : $classroom->today_attendance_rate.'٪' }}</strong>
                                <span class="text-[9px] text-slate-400">حضور امروز</span>
                            </div>
                        </div>

                        @if($classroom->occupancy_percent !== null)
                            <div class="mt-4">
                                <div class="mb-2 flex items-center justify-between text-[9px] text-slate-500">
                                    <span>پرشدگی</span>
                                    <strong>{{ $classroom->occupancy_percent }}٪</strong>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-white">
                                    <span class="block h-full rounded-full bg-[var(--panel-primary)]" style="width:{{ $classroom->occupancy_percent }}%"></span>
                                </div>
                            </div>
                        @endif

                        @if($classroom->live_now)
                            <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-[9px] font-black text-emerald-700">در حال اجرا</span>
                                    <span class="truncate text-[10px] font-bold text-emerald-900">{{ $classroom->live_now->title }}</span>
                                </div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <div class="owner-grid">
        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div>
                    <h2>برآیند مدرس‌ها</h2>
                    <p>دوره، دانش‌آموز، پیشرفت و کارهای معوق</p>
                </div>
                <a href="{{ route('owner.reports.index') }}" class="owner-link">جزئیات ←</a>
            </div>

            <div class="owner-teacher-list">
                @forelse($teacherReports as $teacher)
                    <article class="owner-teacher-row">
                        <div class="owner-avatar">{{ mb_substr($teacher->name,0,1) }}</div>
                        <div>
                            <div class="owner-row-title">{{ $teacher->name }}</div>
                            <div class="owner-row-meta">
                                {{ $teacher->course_count }} دوره · {{ $teacher->student_count }} دانش‌آموز · {{ $teacher->pending_reviews }} بررسی معوق
                            </div>
                            <div class="owner-progress">
                                <span style="width:{{ min(100,max(0,$teacher->progress_average)) }}%"></span>
                            </div>
                        </div>
                        <div class="owner-teacher-metric">
                            <strong>{{ $teacher->progress_average }}٪</strong>
                            <small>پیشرفت میانگین</small>
                        </div>
                    </article>
                @empty
                    <div class="owner-empty">هنوز مدرس فعالی ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="dashboard-panel owner-panel">
            <div class="owner-panel-head">
                <div>
                    <h2>جلسات آنلاین آینده</h2>
                    <p>هفت روز آینده</p>
                </div>
            </div>

            <div class="owner-live-list">
                @forelse($upcomingLiveClasses as $item)
                    <article class="owner-live-row">
                        <div class="owner-pill">{{ \Illuminate\Support\Carbon::parse($item->scheduled_at)->format('m/d H:i') }}</div>
                        <div>
                            <div class="owner-row-title">{{ $item->title }}</div>
                            <div class="owner-row-meta">{{ $item->course?->title }} · {{ $item->teacher?->name }}</div>
                        </div>
                        <span class="owner-pill success">{{ $item->classroom?->title ?? 'آنلاین' }}</span>
                    </article>
                @empty
                    <div class="owner-empty">جلسه آنلاینی در هفت روز آینده ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="dashboard-panel owner-panel">
        <div class="owner-panel-head">
            <div>
                <h2>آخرین دوره‌ها</h2>
                <p>انتشار و دانش‌آموز فعال</p>
            </div>
            <a href="{{ route('owner.courses.index') }}" class="owner-link">همه دوره‌ها ←</a>
        </div>

        <div class="owner-course-list">
            @forelse($recentCourses as $course)
                <article class="owner-course-row">
                    <div>
                        <div class="owner-row-title">{{ $course->title }}</div>
                        <div class="owner-row-meta">{{ $course->academy?->name }} · {{ $course->active_students_count }} دانش‌آموز</div>
                    </div>
                    <span class="owner-pill {{ $course->status === 'published' ? 'success' : '' }}">
                        {{ $course->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                    </span>
                    <a href="{{ route('owner.courses.show',$course) }}" class="owner-pill">بازبینی</a>
                </article>
            @empty
                <div class="owner-empty">دوره‌ای هنوز ثبت نشده است.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
