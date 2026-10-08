@extends('layouts.teacher')

@section('title', 'داشبورد استاد | شیخان')
@section('header-title', 'داشبورد استاد')

@section('content')
    @php
        $progress = max(0, min(100, (float) $weeklyProgress));
        $pendingReviews = max(0, (int) ($metrics['pendingReviews'] ?? 0));
        $pendingAssignmentReviews = max(0, (int) ($metrics['pendingAssignmentReviews'] ?? 0));
        $pendingExamReviews = max(0, (int) ($metrics['pendingExamReviews'] ?? 0));
    @endphp

    <div
        class="teacher-dashboard"
        data-teacher-dashboard
        data-chart='@json([
            "week" => $chart["week"] ?? [],
            "month" => $chart["month"] ?? [],
        ])'
    >
        <section class="teacher-welcome-card dashboard-fade-in">
            <div class="teacher-welcome-copy">
                <span class="teacher-welcome-kicker">گزارش آموزشی</span>

                <h1>
                    سلام استاد {{ $teacher->name }} 👋
                </h1>

                <p>
                    میانگین پیشرفت دانش‌آموزان دوره‌های شما
                    <strong>{{ \App\Support\PersianUi::digits($progress) }}٪</strong>
                    است. وضعیت کلاس‌ها، ارزیابی‌ها و جلسات را از همین‌جا دنبال کن.
                </p>

                <div class="teacher-welcome-actions">
                    <a href="{{ route('teacher.courses.index') }}" class="teacher-primary-btn">
                        مدیریت دوره‌ها
                        <span aria-hidden="true">←</span>
                    </a>

                    <a href="{{ route('teacher.assignments.create') }}" class="teacher-primary-btn teacher-secondary-hero-btn">
                        تکلیف جدید
                        <span aria-hidden="true">+</span>
                    </a>
                </div>
            </div>

            <div class="teacher-welcome-visual" aria-hidden="true">
                <div class="teacher-orb one"></div>
                <div class="teacher-orb two"></div>

                <div class="teacher-visual-card teacher-visual-main">
                    <span>میانگین پیشرفت</span>
                    <strong>{{ \App\Support\PersianUi::digits($progress) }}٪</strong>

                    <div class="teacher-track">
                        <span style="width: {{ $progress }}%"></span>
                    </div>
                </div>

                <div class="teacher-visual-card teacher-visual-small">
                    <span>بررسی در انتظار</span>
                    <strong>{{ \App\Support\PersianUi::digits($pendingReviews) }}</strong>
                </div>
            </div>
        </section>

        <section class="teacher-stats" aria-label="آمار آموزشی">
            <article class="teacher-stat">
                <div class="teacher-stat-icon" aria-hidden="true">ک</div>
                <div class="teacher-stat-copy">
                    <span>کلاس‌های فعال</span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['activeClasses'] ?? 0) }}</strong>
                    <small>کلاس‌های تحت مدیریت شما</small>
                </div>
            </article>

            <article class="teacher-stat">
                <div class="teacher-stat-icon" aria-hidden="true">د</div>
                <div class="teacher-stat-copy">
                    <span>دانش‌آموزان</span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['studentCount'] ?? 0) }}</strong>
                    <small>دانش‌آموز فعال در کلاس‌ها</small>
                </div>
            </article>

            <article class="teacher-stat">
                <div class="teacher-stat-icon" aria-hidden="true">ج</div>
                <div class="teacher-stat-copy">
                    <span>جلسات این هفته</span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['weeklySessions'] ?? 0) }}</strong>
                    <small>{{ \App\Support\PersianUi::digits($completedSessions ?? 0) }} جلسه برگزار شده</small>
                </div>
            </article>

            <article class="teacher-stat">
                <div class="teacher-stat-icon" aria-hidden="true">پ</div>
                <div class="teacher-stat-copy">
                    <span>نیازمند بررسی</span>
                    <strong>{{ \App\Support\PersianUi::digits($pendingReviews) }}</strong>
                    <small>تکلیف و آزمون ارسال‌شده</small>
                </div>
            </article>
        </section>

        <section class="teacher-task-board" aria-labelledby="teacher-task-board-title">
            <div class="teacher-board-head"><div><span>کارهای من</span><h2 id="teacher-task-board-title">امروز چه چیزی نیاز به توجه دارد؟</h2></div><a href="{{ route('teacher.assignments.index') }}">همه فعالیت‌ها ←</a></div>
            <div class="teacher-board-cards">
                <a class="teacher-board-card is-purple" href="{{ route('teacher.assignments.index') }}"><div class="teacher-board-icon">✓</div><div><small>تکالیف</small><strong>{{ AppSupportPersianUi::digits($pendingAssignmentReviews) }} مورد برای تصحیح</strong><span>نمره و بازخورد دانش‌آموزان</span></div><b>→</b></a>
                <a class="teacher-board-card is-orange" href="{{ route('teacher.exams.index') }}"><div class="teacher-board-icon">▤</div><div><small>آزمون‌ها</small><strong>{{ AppSupportPersianUi::digits($pendingExamReviews) }} مورد برای بررسی</strong><span>پاسخ‌ها و نتایج ارسال‌شده</span></div><b>→</b></a>
                <a class="teacher-board-card is-green" href="{{ route('teacher.classrooms.index') }}"><div class="teacher-board-icon">◷</div><div><small>کلاس‌ها</small><strong>{{ AppSupportPersianUi::digits($metrics['weeklySessions'] ?? 0) }} جلسه این هفته</strong><span>{{ AppSupportPersianUi::digits($metrics['studentCount'] ?? 0) }} دانش‌آموز فعال</span></div><b>→</b></a>
            </div>
        </section>

        <div class="teacher-grid">
            <section class="teacher-panel" aria-labelledby="teacher-today-title">
                <div class="teacher-panel-head">
                    <div>
                        <span class="teacher-panel-label">امروز</span>
                        <h2 id="teacher-today-title">جلسه‌های پیش‌رو</h2>
                    </div>

                    <a href="{{ route('teacher.classrooms.index') }}" class="teacher-panel-link">
                        کلاس‌های من ←
                    </a>
                </div>

                <div class="teacher-session-list">
                    @forelse($todaySessions as $session)
                        @php($isLive = ($session['status'] ?? '') === 'در حال برگزاری')

                        <article class="teacher-session-row {{ $isLive ? 'is-live' : '' }}">
                            <span class="teacher-session-time">
                                {{ $session['time'] ?? '—' }}
                            </span>

                            <span class="teacher-session-dot" aria-hidden="true"></span>

                            <div class="min-w-0">
                                <div class="teacher-session-title">
                                    {{ $session['title'] ?? 'جلسه آموزشی' }}
                                </div>

                                <span class="teacher-session-meta">
                                    {{ $session['meta'] ?? 'بدون توضیح' }}
                                </span>
                            </div>

                            <span class="teacher-status {{ $isLive ? 'live' : '' }}">
                                {{ $session['status'] ?? 'برنامه‌ریزی‌شده' }}
                            </span>
                        </article>
                    @empty
                        <div class="teacher-empty">
                            برای امروز جلسه‌ای ثبت نشده است.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="teacher-panel" aria-labelledby="teacher-live-title">
                <div class="teacher-panel-head">
                    <div>
                        <span class="teacher-panel-label">هفت روز آینده</span>
                        <h2 id="teacher-live-title">کلاس‌های آنلاین</h2>
                    </div>

                    <a href="{{ route('teacher.live-classes.index') }}" class="teacher-panel-link">
                        همه جلسات ←
                    </a>
                </div>

                <div class="teacher-upcoming-list">
                    @forelse($upcomingClasses as $item)
                        <article class="teacher-upcoming-item">
                            <div class="teacher-time-box">
                                <strong>{{ \App\Support\PersianUi::time($item->scheduled_at) }}</strong>
                                <small>{{ \App\Support\PersianUi::date($item->scheduled_at) }}</small>
                            </div>

                            <div class="min-w-0">
                                <div class="teacher-upcoming-title">
                                    {{ $item->title }}
                                </div>

                                <span class="teacher-upcoming-meta">
                                    {{ $item->course?->title ?? 'دوره آموزشی' }}
                                    ·
                                    {{ $item->classroom?->title ?? 'جلسه آزاد' }}
                                </span>
                            </div>

                            <span class="teacher-type-badge">
                                آنلاین
                            </span>
                        </article>
                    @empty
                        <div class="teacher-empty">
                            جلسه آنلاین آینده‌ای ثبت نشده است.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="teacher-grid">
            <section class="teacher-panel" aria-labelledby="teacher-progress-title">
                <div class="teacher-panel-head">
                    <div>
                        <span class="teacher-panel-label">عملکرد آموزشی</span>
                        <h2 id="teacher-progress-title">روند پیشرفت دانش‌آموزان</h2>
                    </div>

                    <div class="teacher-filter" role="group" aria-label="بازه نمودار">
                        <button type="button" class="active" data-teacher-range="week" aria-pressed="true">
                            هفته
                        </button>

                        <button type="button" data-teacher-range="month" aria-pressed="false">
                            ماه
                        </button>
                    </div>
                </div>

                <div class="teacher-chart">
                    <div class="teacher-y-axis" aria-hidden="true">
                        <span>۱۰۰٪</span>
                        <span>۷۵٪</span>
                        <span>۵۰٪</span>
                        <span>۲۵٪</span>
                        <span>۰٪</span>
                    </div>

                    <div class="teacher-chart-main">
                        <div class="teacher-chart-lines" aria-hidden="true">
                            <i></i><i></i><i></i><i></i><i></i>
                        </div>

                        <div class="teacher-bars" data-teacher-bars aria-label="نمودار پیشرفت">
                            @foreach(($chart['week']['values'] ?? []) as $index => $value)
                                @php($chartValue = max(0, min(100, (float) $value)))

                                <div
                                    class="teacher-bar"
                                    style="height: {{ $chartValue }}%"
                                    aria-label="{{ $chart['week']['labels'][$index] ?? '' }}"
                                >
                                    <small>
                                        {{ $chart['week']['labels'][$index] ?? '' }}
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($courseProgress->isNotEmpty())
                    <div class="teacher-course-progress">
                        @foreach($courseProgress as $course)
                            @php($courseValue = max(0, min(100, (float) $course->progress_average)))

                            <article class="teacher-progress-card">
                                <strong>{{ $course->title }}</strong>

                                <small>
                                    {{ \App\Support\PersianUi::digits($course->student_count) }} دانش‌آموز
                                    ·
                                    میانگین {{ \App\Support\PersianUi::digits($courseValue) }}٪
                                </small>

                                <div class="teacher-progress-track">
                                    <span style="width: {{ $courseValue }}%"></span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="teacher-panel" aria-labelledby="teacher-attention-title">
                <div class="teacher-panel-head">
                    <div>
                        <span class="teacher-panel-label">عملیات ضروری</span>
                        <h2 id="teacher-attention-title">نیازمند پیگیری</h2>
                        <p>{{ \App\Support\PersianUi::digits($pendingReviews) }} مورد برای بررسی</p>
                    </div>
                </div>

                <div class="teacher-upcoming-list">
                    <a href="{{ route('teacher.assignments.index') }}" class="teacher-upcoming-item">
                        <div class="teacher-time-box">
                            <strong>{{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }}</strong>
                            <small>تکلیف</small>
                        </div>

                        <div class="min-w-0">
                            <div class="teacher-upcoming-title">تکالیف نیازمند تصحیح</div>
                            <span class="teacher-upcoming-meta">بررسی نمره و بازخورد دانش‌آموزان</span>
                        </div>

                        <span class="teacher-type-badge">باز کردن</span>
                    </a>

                    <a href="{{ route('teacher.exams.index') }}" class="teacher-upcoming-item">
                        <div class="teacher-time-box">
                            <strong>{{ \App\Support\PersianUi::digits($pendingExamReviews) }}</strong>
                            <small>آزمون</small>
                        </div>

                        <div class="min-w-0">
                            <div class="teacher-upcoming-title">آزمون‌های نیازمند بررسی</div>
                            <span class="teacher-upcoming-meta">نتایج ارسال‌شده را بررسی کن</span>
                        </div>

                        <span class="teacher-type-badge">بررسی</span>
                    </a>

                    <a href="{{ route('teacher.classrooms.index') }}" class="teacher-upcoming-item">
                        <div class="teacher-time-box">
                            <strong>{{ \App\Support\PersianUi::digits($metrics['activeClasses'] ?? 0) }}</strong>
                            <small>کلاس</small>
                        </div>

                        <div class="min-w-0">
                            <div class="teacher-upcoming-title">حضور و غیاب کلاس‌ها</div>
                            <span class="teacher-upcoming-meta">ثبت وضعیت حضور دانش‌آموزان</span>
                        </div>

                        <span class="teacher-type-badge">مدیریت</span>
                    </a>
                </div>
            </section>
        </div>

        <section class="teacher-people-panel">
            <div class="teacher-board-head"><div><span>دانش‌آموزان</span><h2>دانش‌آموزان با پیشرفت بالاتر</h2></div><a href="{{ route('teacher.classrooms.index') }}">کلاس‌ها ←</a></div>
            <div class="teacher-people-grid">
                @forelse(($topStudents ?? collect()) as $studentItem)
                    <article class="teacher-person-card">
                        <img src="{{ asset('images/default-account-avatar.svg') }}" alt="" loading="lazy">
                        <div><strong>{{ $studentItem->name }}</strong><span>{{ AppSupportPersianUi::digits($studentItem->progress_average) }}٪ پیشرفت</span></div>
                        <b>{{ AppSupportPersianUi::digits($studentItem->progress_average) }}٪</b>
                    </article>
                @empty
                    <div class="teacher-empty">هنوز داده کافی برای نمایش دانش‌آموزان وجود ندارد.</div>
                @endforelse
            </div>
        </section>

        <section class="teacher-panel" aria-labelledby="teacher-assignments-title">
            <div class="teacher-panel-head">
                <div>
                    <span class="teacher-panel-label">پیگیری سریع</span>
                    <h2 id="teacher-assignments-title">آخرین تکالیف</h2>
                </div>

                <a href="{{ route('teacher.assignments.index') }}" class="teacher-panel-link">
                    مشاهده همه ←
                </a>
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

                                <td>
                                    {{ $activity->classroom?->title ?? 'عمومی دوره' }}
                                </td>

                                <td>
                                    @if($activity->due_at)
                                        {{ \App\Support\PersianUi::date($activity->due_at) }}
                                        ·
                                        {{ \App\Support\PersianUi::time($activity->due_at) }}
                                    @else
                                        بدون موعد
                                    @endif
                                </td>

                                <td>
                                    {{ \App\Support\PersianUi::digits($activity->submitted_count) }}
                                </td>

                                <td>
                                    <span class="teacher-activity-pill">
                                        {{ \App\Support\PersianUi::digits($activity->pending_review_count) }}
                                        مورد
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">هنوز تکلیفی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection