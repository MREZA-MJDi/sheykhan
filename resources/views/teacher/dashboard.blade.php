@extends('layouts.teacher')

@section('title', 'داشبورد استاد | شیخان')
@section('header-title', 'داشبورد استاد')

@section('content')
<div class="teacher-dashboard" data-teacher-dashboard data-chart='@json([
    "week" => $chart["week"] ?? [],
    "month" => $chart["month"] ?? [],
])'>

    <section class="teacher-welcome-card dashboard-fade-in">
        <div class="teacher-welcome-copy">
            <span class="teacher-welcome-kicker">گزارش آموزشی</span>
            <h2>سلام استاد {{ $teacher->name }} 👋</h2>
            <p>
                میانگین پیشرفت دانش‌آموزان دوره‌های شما
                <strong>{{ $weeklyProgress }}٪</strong> است.
                این صفحه وضعیت کلاس، دانش‌آموز، ارزیابی و جلسات را یکجا نشان می‌دهد.
            </p>
            <div class="teacher-welcome-actions">
                <a href="{{ route('teacher.courses.index') }}" class="teacher-primary-btn">مدیریت دوره‌ها ←</a>
                <a href="{{ route('teacher.assignments.create') }}" class="teacher-primary-btn" style="background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2)">تکلیف جدید</a>
            </div>
        </div>

        <div class="teacher-welcome-visual" aria-hidden="true">
            <div class="teacher-orb one"></div>
            <div class="teacher-orb two"></div>
            <div class="teacher-visual-card teacher-visual-main">
                <span>میانگین پیشرفت</span>
                <strong>{{ $weeklyProgress }}٪</strong>
                <div class="teacher-track"><span style="width:{{ min(100, max(0, $weeklyProgress)) }}%"></span></div>
            </div>
            <div class="teacher-visual-card teacher-visual-small">
                <span>بررسی در انتظار</span>
                <strong>{{ $metrics['pendingReviews'] }}</strong>
            </div>
        </div>
    </section>

    <section class="teacher-stats" aria-label="آمار آموزشی">
        <article class="teacher-stat">
            <div class="teacher-stat-icon">ک</div>
            <div class="teacher-stat-copy">
                <span>کلاس‌های فعال</span>
                <strong>{{ $metrics['activeClasses'] }}</strong>
                <small>کلاس‌های تحت مدیریت شما</small>
            </div>
        </article>
        <article class="teacher-stat">
            <div class="teacher-stat-icon">د</div>
            <div class="teacher-stat-copy">
                <span>دانش‌آموزان</span>
                <strong>{{ $metrics['studentCount'] }}</strong>
                <small>دانش‌آموز فعال در کلاس‌ها</small>
            </div>
        </article>
        <article class="teacher-stat">
            <div class="teacher-stat-icon">ج</div>
            <div class="teacher-stat-copy">
                <span>جلسات این هفته</span>
                <strong>{{ $metrics['weeklySessions'] }}</strong>
                <small>{{ $completedSessions }} جلسه برگزار شده</small>
            </div>
        </article>
        <article class="teacher-stat">
            <div class="teacher-stat-icon">ت</div>
            <div class="teacher-stat-copy">
                <span>فروش دوره‌های من</span>
                <strong>{{ number_format($metrics['monthlySales'], 0, '.', ',') }}</strong>
                <small>تومان · این ماه</small>
            </div>
        </article>
    </section>

    <div class="teacher-grid">
        <section class="teacher-panel">
            <div class="teacher-panel-head">
                <div>
                    <span class="teacher-panel-label">امروز</span>
                    <h3>جلسه‌های پیش‌رو</h3>
                </div>
                <a href="{{ route('teacher.classrooms.index') }}" class="teacher-panel-link">کلاس‌های من ←</a>
            </div>

            <div class="teacher-session-list">
                @forelse($todaySessions as $session)
                    <div class="teacher-session-row {{ $session['status'] === 'در حال برگزاری' ? 'is-live' : '' }}">
                        <span class="teacher-session-time">{{ $session['time'] }}</span>
                        <span class="teacher-session-dot"></span>
                        <div>
                            <div class="teacher-session-title">{{ $session['title'] }}</div>
                            <span class="teacher-session-meta">{{ $session['meta'] }}</span>
                        </div>
                        <span class="teacher-status {{ $session['status'] === 'در حال برگزاری' ? 'live' : '' }}">{{ $session['status'] }}</span>
                    </div>
                @empty
                    <div class="teacher-empty">برای امروز جلسه‌ای ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="teacher-panel">
            <div class="teacher-panel-head">
                <div>
                    <span class="teacher-panel-label">هفت روز آینده</span>
                    <h3>کلاس‌های آنلاین</h3>
                </div>
                <a href="{{ route('teacher.live-classes.index') }}" class="teacher-panel-link">همه جلسات ←</a>
            </div>

            <div class="teacher-upcoming-list">
                @forelse($upcomingClasses as $item)
                    <article class="teacher-upcoming-item">
                        <div class="teacher-time-box">
                            <strong>{{ $item->scheduled_at->format('H:i') }}</strong>
                            <small>{{ $item->scheduled_at->format('m/d') }}</small>
                        </div>
                        <div>
                            <div class="teacher-upcoming-title">{{ $item->title }}</div>
                            <span class="teacher-upcoming-meta">{{ $item->course?->title }} · {{ $item->classroom?->title ?? 'جلسه آزاد' }}</span>
                        </div>
                        <span class="teacher-type-badge">آنلاین</span>
                    </article>
                @empty
                    <div class="teacher-empty">جلسه آنلاین آینده‌ای ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="teacher-grid">
        <section class="teacher-panel">
            <div class="teacher-panel-head">
                <div>
                    <span class="teacher-panel-label">عملکرد آموزشی</span>
                    <h3>روند پیشرفت دانش‌آموزان</h3>
                </div>
                <div class="teacher-filter">
                    <button type="button" class="active" data-teacher-range="week">هفته</button>
                    <button type="button" data-teacher-range="month">ماه</button>
                </div>
            </div>

            <div class="teacher-chart">
                <div class="teacher-y-axis">
                    <span>۱۰۰٪</span>
                    <span>۷۵٪</span>
                    <span>۵۰٪</span>
                    <span>۲۵٪</span>
                    <span>۰٪</span>
                </div>
                <div class="teacher-chart-main">
                    <div class="teacher-chart-lines"><i></i><i></i><i></i><i></i><i></i></div>
                    <div class="teacher-bars" data-teacher-bars>
                        @foreach(($chart['week'] ?? []) as $index => $value)
                            <div class="teacher-bar" style="height:{{ $value }}%"><small>{{ $chart['labels'][$index] ?? '' }}</small></div>
                        @endforeach
                    </div>
                </div>
            </div>

            @if($courseProgress->isNotEmpty())
                <div class="teacher-course-progress">
                    @foreach($courseProgress as $course)
                        <article class="teacher-progress-card">
                            <strong>{{ $course->title }}</strong>
                            <small>{{ $course->student_count }} دانش‌آموز · میانگین {{ $course->progress_average }}٪</small>
                            <div class="teacher-progress-track">
                                <span style="width:{{ $course->progress_average }}%"></span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="teacher-panel">
            <div class="teacher-calendar-head">
                <div>
                    <span class="teacher-panel-label">عملیات ضروری</span>
                    <h3>نیازمند پیگیری</h3>
                    <span class="teacher-calendar-title">{{ $metrics['pendingReviews'] }} مورد برای بررسی</span>
                </div>
            </div>

            <div class="teacher-upcoming-list">
                <a href="{{ route('teacher.assignments.index') }}" class="teacher-upcoming-item">
                    <div class="teacher-time-box"><strong>{{ $activities->sum('pending_review_count') }}</strong><small>تکلیف</small></div>
                    <div><div class="teacher-upcoming-title">تکالیف نیازمند تصحیح</div><span class="teacher-upcoming-meta">بررسی نمره و بازخورد دانش‌آموزان</span></div>
                    <span class="teacher-type-badge">باز کردن</span>
                </a>

                <a href="{{ route('teacher.exams.index') }}" class="teacher-upcoming-item">
                    <div class="teacher-time-box"><strong>{{ $metrics['pendingReviews'] - $activities->sum('pending_review_count') }}</strong><small>آزمون</small></div>
                    <div><div class="teacher-upcoming-title">آزمون‌های نیازمند بررسی</div><span class="teacher-upcoming-meta">نتایج ارسال‌شده را بررسی کن</span></div>
                    <span class="teacher-type-badge">بررسی</span>
                </a>

                <a href="{{ route('teacher.classrooms.index') }}" class="teacher-upcoming-item">
                    <div class="teacher-time-box"><strong>{{ $metrics['activeClasses'] }}</strong><small>کلاس</small></div>
                    <div><div class="teacher-upcoming-title">حضور و غیاب کلاس‌ها</div><span class="teacher-upcoming-meta">ثبت وضعیت حضور دانش‌آموزان</span></div>
                    <span class="teacher-type-badge">مدیریت</span>
                </a>
            </div>
        </section>
    </div>

    <section class="teacher-panel">
        <div class="teacher-panel-head">
            <div>
                <span class="teacher-panel-label">پیگیری سریع</span>
                <h3>آخرین تکالیف</h3>
            </div>
            <a href="{{ route('teacher.assignments.index') }}" class="teacher-panel-link">مشاهده همه ←</a>
        </div>

        <div class="teacher-activity-table-wrap">
            <table class="teacher-activity-table">
                <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>کلاس</th>
                        <th>موعد</th>
                        <th>تحویل</th>
                        <th>بررسی</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->title }}</td>
                        <td>{{ $activity->classroom?->title ?? 'عمومی دوره' }}</td>
                        <td>{{ $activity->due_at?->format('Y/m/d H:i') ?? 'بدون موعد' }}</td>
                        <td>{{ $activity->submitted_count }}</td>
                        <td><span class="teacher-activity-pill">{{ $activity->pending_review_count }} مورد</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5">هنوز تکلیفی ثبت نشده است.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
