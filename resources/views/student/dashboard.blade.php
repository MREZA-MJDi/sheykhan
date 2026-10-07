@extends('layouts.student')

@section('title', 'داشبورد دانش‌آموز | شیخان')
@section('header-title', 'داشبورد دانش‌آموز')

@section('content')
    @php
        $progress = max(0, min(100, (float) $overallProgress));
        $coursesCount = $courses->count();
        $sessionsCount = $sessions->count();
        $resultsCount = $recentResults->count();
    @endphp

    <div class="student-dashboard">

        {{-- =========================================================
            WELCOME
        ========================================================== --}}
        <section class="student-welcome" aria-labelledby="student-welcome-title">
            <div class="student-welcome-copy">
                <span class="student-kicker">
                    فضای اختصاصی دانش‌آموز
                </span>

                <h1 id="student-welcome-title">
                    سلام {{ $student->name }} 👋
                </h1>

                <p>
                    دوره‌ها، جلسات، تکالیف و نتیجه‌های خودت را از یک مسیر امن و یکپارچه دنبال کن.
                </p>
            </div>

            <div class="student-welcome-decoration" aria-hidden="true">
                <span class="student-welcome-orb one"></span>
                <span class="student-welcome-orb two"></span>
            </div>
        </section>


        {{-- =========================================================
            QUICK STATS
        ========================================================== --}}
        <section
            class="student-stats"
            aria-label="خلاصه وضعیت آموزشی"
        >
            <article class="student-stat">
                <span>دوره‌های فعال</span>

                <strong>
                    {{ \App\Support\PersianUi::digits($activeCourseCount) }}
                </strong>

                <small>
                    مسیرهای آموزشی در حال پیگیری
                </small>
            </article>

            <article class="student-stat">
                <span>میانگین پیشرفت</span>

                <strong>
                    {{ \App\Support\PersianUi::digits($progress) }}٪
                </strong>

                <small>
                    برآیند پیشرفت دوره‌های فعال
                </small>
            </article>

            <article class="student-stat">
                <span>تکالیف در انتظار</span>

                <strong>
                    {{ \App\Support\PersianUi::digits($pendingAssignments) }}
                </strong>

                <small>
                    مواردی که نیاز به پیگیری دارند
                </small>
            </article>
        </section>


        {{-- =========================================================
            MAIN DASHBOARD
        ========================================================== --}}
        <div class="student-grid">

            {{-- =====================================================
                ACTIVE COURSES
            ====================================================== --}}
            <section
                id="student-courses"
                class="student-panel dashboard-panel"
                aria-labelledby="student-courses-title"
            >
                <div class="student-panel-head">
                    <div>
                        <span class="student-kicker" style="color: var(--panel-primary)">
                            یادگیری من
                        </span>

                        <h2 id="student-courses-title">
                            دوره‌های فعال
                        </h2>

                        <p>
                            مسیرهایی که در حال حاضر در آن‌ها مشغول یادگیری هستی.
                        </p>
                    </div>

                    <a class="student-action" href="{{ route('student.resources.index') }}">
                        منابع آموزشی
                    </a>

                    <span class="student-status primary">
                        {{ \App\Support\PersianUi::digits($coursesCount) }} دوره
                    </span>
                </div>

                <div class="student-course-list">
                    @forelse($courses as $course)
                        @php
                            $courseProgress = max(
                                0,
                                min(100, (float) ($course->learning_progress ?? 0))
                            );
                        @endphp

                        <article class="student-course">

                            <div class="student-course-main">
                                <div class="student-course-title">
                                    {{ $course->title }}
                                </div>

                                <div class="student-course-meta">
                                    {{ $course->level ?: 'دوره آموزشی' }}
                                </div>

                                @if($course->academy?->name ?? null)
                                    <div class="student-course-badges">
                                        <span class="student-course-badge">
                                            {{ $course->academy->name }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <div
                                class="student-progress"
                                aria-label="پیشرفت {{ $course->title }}"
                            >
                                <div class="student-progress-label">
                                    <span>پیشرفت</span>

                                    <strong>
                                        {{ \App\Support\PersianUi::digits(round($courseProgress)) }}٪
                                    </strong>
                                </div>

                                <div
                                    class="student-progress-track"
                                    role="progressbar"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                    aria-valuenow="{{ round($courseProgress) }}"
                                    aria-label="پیشرفت دوره {{ $course->title }}"
                                >
                                    <span
                                        style="width: {{ $courseProgress }}%"
                                    ></span>
                                </div>
                            </div>

                        </article>
                    @empty
                        <div class="student-empty">
                            <div class="student-empty-icon" aria-hidden="true">
                                <svg
                                    width="21"
                                    height="21"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"
                                    />
                                </svg>
                            </div>

                            <strong>
                                هنوز دوره فعالی برای حساب شما ثبت نشده است.
                            </strong>

                            <span>
                                پس از ثبت‌نام در یک دوره، مسیر یادگیری و میزان پیشرفت آن از اینجا قابل پیگیری خواهد بود.
                            </span>
                        </div>
                    @endforelse
                </div>
            </section>


            {{-- =====================================================
                SESSIONS
            ====================================================== --}}
            <section
                id="student-sessions"
                class="student-panel dashboard-panel"
                aria-labelledby="student-sessions-title"
            >
                <div class="student-panel-head">
                    <div>
                        <span class="student-kicker" style="color: var(--panel-primary)">
                            چراغ جلسات
                        </span>

                        <h2 id="student-sessions-title">
                            جلسات کلاس
                        </h2>

                        <p>
                            جلسه‌های برگزارشده و برنامه‌ریزی‌شده دوره‌های تو.
                        </p>
                    </div>

                    <span class="student-status">
                        {{ \App\Support\PersianUi::digits($sessionsCount) }} جلسه
                    </span>
                </div>

                <div class="student-session-list">
                    @forelse($sessions as $session)
                        @php
                            $available = (bool) ($session['available'] ?? false);
                            $isFuture = (bool) ($session['is_future'] ?? false);
                            $lampOn = ($session['lamp'] ?? '') === 'روشن';
                        @endphp

                        <article
                            class="student-session {{ $available ? 'is-open' : 'is-locked' }}"
                            aria-label="{{ $session['title'] }}"
                        >
                            <div class="student-session-head">
                                <span
                                    class="student-lamp {{ $lampOn ? 'on' : 'off' }}"
                                    aria-hidden="true"
                                ></span>

                                <strong>
                                    {{ $lampOn ? 'چراغ روشن' : 'چراغ خاموش' }}
                                </strong>

                                <span>
                                    {{ $session['date'] ?? 'بدون تاریخ' }}
                                </span>
                            </div>

                            <div class="student-row-title">
                                {{ $session['title'] }}
                            </div>

                            <div class="student-row-meta">
                                {{ $session['course'] ?? 'دوره آموزشی' }}

                                @if(!empty($session['time']))
                                    <span aria-hidden="true"> · </span>
                                    {{ $session['time'] }}
                                @endif
                            </div>

                            @if($available)
                                <a
                                    class="student-session-action"
                                    href="{{ $session['href'] }}"
                                    aria-label="مشاهده جلسه {{ $session['title'] }}"
                                >
                                    <span>مشاهده جلسه</span>
                                    <span aria-hidden="true">←</span>
                                </a>
                            @elseif($isFuture)
                                <span
                                    class="student-session-action disabled"
                                    aria-disabled="true"
                                >
                                    جلسه هنوز برگزار نشده
                                </span>
                            @else
                                <span
                                    class="student-session-action disabled"
                                    aria-disabled="true"
                                >
                                    ویدئو هنوز منتشر نشده
                                </span>
                            @endif
                        </article>
                    @empty
                        <div class="student-empty">
                            <div class="student-empty-icon" aria-hidden="true">
                                <svg
                                    width="21"
                                    height="21"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                >
                                    <circle cx="12" cy="12" r="9"/>
                                    <path
                                        stroke-linecap="round"
                                        d="M12 8v4l2.5 1.5"
                                    />
                                </svg>
                            </div>

                            <strong>
                                هنوز جلسه‌ای برای دوره‌های شما ثبت نشده است.
                            </strong>

                            <span>
                                جلسه‌های گذشته و برنامه‌ریزی‌شده از همین بخش قابل پیگیری خواهند بود.
                            </span>
                        </div>
                    @endforelse
                </div>
            </section>

        </div>


        <section
            id="student-assignments"
            class="student-panel dashboard-panel"
            aria-labelledby="student-assignments-title"
        >
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color: var(--panel-primary)">پیگیری</span>
                    <h2 id="student-assignments-title">تکالیف من</h2>
                    <p>تکالیف منتشرشده دوره‌های فعال و وضعیت تحویل آن‌ها.</p>
                </div>
            </div>

            <div class="student-list">
                @forelse($assignments as $assignment)
                    <article class="student-list-row">
                        <div class="student-date">
                            <strong>
                                {{ $assignment->due_at ? AppSupportPersianUi::date($assignment->due_at) : '—' }}
                            </strong>
                            <small>موعد</small>
                        </div>

                        <div class="student-row-content">
                            <div class="student-row-title">{{ $assignment->title }}</div>
                            <span class="student-row-meta">
                                @if($assignment->submitted_at)
                                    تحویل‌شده
                                    @if($assignment->score !== null)
                                        · نمره {{ AppSupportPersianUi::digits($assignment->score) }}
                                    @endif
                                @else
                                    تحویل نشده
                                @endif
                            </span>
                        </div>

                        <div class="student-score">
                            {{ $assignment->submitted_at ? ($assignment->graded_at ? 'ارزیابی‌شده' : 'ارسال‌شده') : 'در انتظار اقدام' }}
                        </div>
                    </article>
                @empty
                    <div class="student-empty">
                        <strong>تکلیف فعالی برای شما ثبت نشده است.</strong>
                        <span>با انتشار تکلیف جدید، وضعیت آن در همین بخش نمایش داده می‌شود.</span>
                    </div>
                @endforelse
            </div>
        </section>

        <section
            class="student-panel dashboard-panel"
            aria-labelledby="student-resources-title"
        >
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color: var(--panel-primary)">منابع آموزشی</span>
                    <h2 id="student-resources-title">جزوه‌ها و فایل‌های من</h2>
                    <p>محتوایی که برای دوره‌ها یا کلاس‌های شما منتشر شده است.</p>
                </div>
                <a class="student-action" href="{{ route('student.resources.index') }}">مشاهده همه</a>
            </div>

            <div class="student-resource-list">
                @forelse($resources as $resource)
                    <article class="student-resource">
                        <div>
                            <strong>{{ $resource->title }}</strong>
                            <span>{{ $resource->course?->title ?? $resource->classroom?->title ?? $resource->lesson?->title ?? 'منبع آموزشی' }}</span>
                        </div>

                        <div class="student-resource-actions">
                            <a class="student-action" href="{{ route('student.resources.view', $resource) }}">مشاهده</a>
                            @if($resource->downloadable)
                                <a class="student-action" href="{{ route('student.resources.download', $resource) }}">دانلود</a>
                            @else
                                <span class="student-status">فقط مشاهده</span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="student-empty">
                        <strong>هنوز منبع آموزشی منتشر نشده است.</strong>
                        <span>پس از انتشار، منابع مرتبط با دوره‌ها و کلاس‌های شما اینجا ظاهر می‌شوند.</span>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- =========================================================
            RECENT RESULTS
        ========================================================== --}}
        <section
            id="student-results"
            class="student-panel dashboard-panel"
            aria-labelledby="student-results-title"
        >
            <div class="student-panel-head">
                <div>
                    <span class="student-kicker" style="color: var(--panel-primary)">
                        پیگیری
                    </span>

                    <h2 id="student-results-title">
                        آخرین نتیجه‌ها
                    </h2>

                    <p>
                        نمره‌ها و نتیجه‌های اخیر ثبت‌شده برای حساب شما.
                    </p>
                </div>

                <span class="student-status">
                    {{ \App\Support\PersianUi::digits($resultsCount) }} نتیجه
                </span>
            </div>

            <div class="student-list">
                @forelse($recentResults as $result)
                    @php
                        $score = $result->score ?? 0;
                        $gradedAt = $result->graded_at;
                    @endphp

                    <article class="student-list-row">

                        <div class="student-date">
                            <strong>
                                {{ \App\Support\PersianUi::digits($score) }}
                            </strong>

                            <small>
                                نمره
                            </small>
                        </div>

                        <div class="student-row-content">
                            <div class="student-row-title">
                                {{ $result->title }}
                            </div>

                            @if($gradedAt)
                                <span class="student-row-meta">
                                    {{ \App\Support\PersianUi::date($gradedAt) }}
                                </span>
                            @else
                                <span class="student-row-meta">
                                    تاریخ ثبت نتیجه مشخص نیست
                                </span>
                            @endif
                        </div>

                        <div class="student-score">
                            {{ $result->status_label ?? 'ثبت‌شده' }}
                        </div>

                    </article>
                @empty
                    <div class="student-empty">
                        <div class="student-empty-icon" aria-hidden="true">
                            <svg
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 4h12v16H6z"
                                />
                                <path
                                    stroke-linecap="round"
                                    d="M9 8h6M9 12h6M9 16h4"
                                />
                            </svg>
                        </div>

                        <strong>
                            هنوز نتیجه‌ای ثبت نشده است.
                        </strong>

                        <span>
                            نتیجه آزمون‌ها و تکالیف پس از ثبت و ارزیابی در این قسمت نمایش داده می‌شوند.
                        </span>
                    </div>
                @endforelse
            </div>
        </section>

    </div>
@endsection
