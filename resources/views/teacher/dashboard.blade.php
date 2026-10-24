@extends('layouts.teacher')

@section('title', 'داشبورد مدرس | شیخان')

@section('content')
    @php
        $teacherUser = auth()->user();
        // Keep direct Blade renders (e.g. feature tests) safe when the shared layout has not set the avatar URL.
        $teacherPanelAvatarUrl = $teacherPanelAvatarUrl ?? null;
        $teacherInitial = \Illuminate\Support\Str::substr(trim((string) ($teacherUser?->name ?? 'م')), 0, 1);
        $dashboardMetrics = $metrics ?? [];
        $pendingAssignmentReviews = max(0, (int) ($dashboardMetrics['pendingAssignmentReviews'] ?? 0));
        $pendingExamReviews = max(0, (int) ($dashboardMetrics['pendingExamReviews'] ?? 0));
        $canViewClassrooms = (bool) $teacherUser?->hasPermission('classrooms.view');
        $canViewCourses = (bool) $teacherUser?->hasPermission('courses.view');
        $canViewStudents = (bool) $teacherUser?->hasPermission('students.view');
        $canViewAssignments = (bool) $teacherUser?->hasPermission('assignments.view');
        $canManageAssignments = (bool) $teacherUser?->hasPermission('assignments.manage');
        $canViewExams = (bool) $teacherUser?->hasPermission('exams.view');
        $canManageExams = (bool) $teacherUser?->hasPermission('exams.manage');
        $canViewAttendance = (bool) $teacherUser?->hasPermission('attendance.view');
        $canViewLiveClasses = (bool) $teacherUser?->hasPermission('live_classes.view');
        $canViewParentMessages = (bool) $teacherUser?->hasPermission('parent_messages.view');
        $canViewReports = (bool) $teacherUser?->hasPermission('reports.view');
        $canViewStudentMessages = (bool) $teacherUser?->hasPermission('student_messages.view');
        $canManageParentMessages = (bool) $teacherUser?->hasPermission('parent_messages.manage');
        $canViewPayments = (bool) $teacherUser?->hasPermission('teacher_payments.view');
        $canViewProfile = (bool) $teacherUser?->hasPermission('profile.view');

        $hasPendingAssignments = $canViewAssignments && $pendingAssignmentReviews > 0;
        $hasPendingExams = $canViewExams && $pendingExamReviews > 0;
        $pendingReviews = (int) ($canViewAssignments ? $pendingAssignmentReviews : 0)
            + (int) ($canViewExams ? $pendingExamReviews : 0);

        $activeClasses = max(0, (int) ($dashboardMetrics['activeClasses'] ?? 0));
        $studentCount = max(0, (int) ($dashboardMetrics['studentCount'] ?? 0));
        $weeklySessions = max(0, (int) ($dashboardMetrics['weeklySessions'] ?? 0));
        $completedSessionsCount = max(0, (int) ($completedSessions ?? 0));
        $courseCards = collect($courseProgress ?? []);
        $featuredStudents = collect($topStudents ?? []);
        $recentAssignments = collect($activities ?? []);
        $todayList = collect($todaySessions ?? [])->map(function (array $session): array {
            $session['status'] = (string) ($session['status'] ?? 'برنامه‌ریزی‌شده');
            $session['tone'] = $session['status'] === 'در حال برگزاری'
                ? 'er'
                : ($session['status'] === 'پایان یافته' ? 'mu' : 'sk');
            $session['can_join'] = ! empty($session['can_join']) && filled($session['meeting_url'] ?? null);

            return $session;
        });
        $upcomingList = collect($upcomingClasses ?? []);
        $weekChart = $chart['week'] ?? ['labels' => [], 'values' => []];
        $monthChart = $chart['month'] ?? ['labels' => [], 'values' => []];
        $chartHasSignal = collect($weekChart['values'] ?? [])->contains(fn ($value) => (float) $value > 0);
        $chartSlots = $chartHasSignal ? count($weekChart['values'] ?? []) : 0;
        $dashboardDateLabel = $dashboardDate ?? \App\Support\PersianUi::date(now());
        $focusRoute = $hasPendingAssignments
            ? route('teacher.assignments.index')
            : ($hasPendingExams
                ? route('teacher.exams.index')
                : ($canViewClassrooms
                    ? route('teacher.classrooms.index')
                    : ($canViewCourses
                        ? route('teacher.courses.index')
                        : ($canViewLiveClasses ? route('teacher.schedule.index') : null))));
        $focusLabel = $hasPendingAssignments
            ? ($canManageAssignments ? 'شروع تصحیح تکالیف' : 'مشاهده تکالیف')
            : ($hasPendingExams
                ? ($canManageExams ? 'بررسی آزمون‌ها' : 'مشاهده آزمون‌ها')
                : ($canViewClassrooms ? 'مدیریت کلاس‌ها' : ($canViewCourses ? 'مدیریت دوره‌ها' : 'مشاهده برنامه')));

        // KPIs are summaries; the canonical navigation lives in the shared sidebar.
        $kpis = [
            ['label' => 'کلاس‌های فعال', 'value' => \App\Support\PersianUi::digits($activeClasses), 'hint' => 'مشاهدهٔ کلاس‌ها و فهرست اعضا', 'tone' => 'sk', 'icon' => 'users', 'permission' => 'classrooms.view', 'url' => route('teacher.classrooms.index')],
            ['label' => 'دوره‌های من', 'value' => \App\Support\PersianUi::digits($courseCards->count()), 'hint' => 'مشاهدهٔ محتوای دوره‌ها', 'tone' => 'ok', 'icon' => 'book', 'permission' => 'courses.view', 'url' => route('teacher.courses.index')],
            ['label' => 'جلسات این هفته', 'value' => \App\Support\PersianUi::digits($weeklySessions), 'hint' => \App\Support\PersianUi::digits($completedSessionsCount) . ' جلسه برگزار شده · برنامه کامل', 'tone' => 'wr', 'icon' => 'vid', 'permission' => 'live_classes.view', 'url' => route('teacher.schedule.index')],
            ['label' => 'دانش‌آموزان', 'value' => \App\Support\PersianUi::digits($studentCount), 'hint' => 'مشاهدهٔ دانش‌آموزان کلاس‌های شما', 'tone' => 'sk', 'icon' => 'hs', 'permission' => 'students.view', 'url' => route('teacher.students.index')],
            ['label' => 'تکالیف منتظر تصحیح', 'value' => \App\Support\PersianUi::digits($pendingAssignmentReviews), 'hint' => 'رفتن به فهرست تکالیف و بازخورد', 'tone' => 'er', 'icon' => 'file', 'permission' => 'assignments.view', 'url' => route('teacher.assignments.index')],
            ['label' => 'آزمون‌های منتظر بررسی', 'value' => \App\Support\PersianUi::digits($pendingExamReviews), 'hint' => 'رفتن به پاسخ‌های آزمون', 'tone' => 'vi', 'icon' => 'clip', 'permission' => 'exams.view', 'url' => route('teacher.exams.index')],
        ];
        $kpis = array_values(array_filter($kpis, fn ($item) => $teacherUser?->hasPermission($item['permission'])));

        $studentTones = ['ac', 'ok', 'vi', 'sk', 'wr'];
        $courseTones = ['ok', 'sk', 'vi', 'wr'];
    @endphp

    <div id="teacher-dashboard" class="teacher-dashboard-page">
        <header class="ph teacher-dashboard-hero">
            <div class="teacher-dashboard-avatar" aria-hidden="true">
                    @if($teacherPanelAvatarUrl)
                        <img src="{{ $teacherPanelAvatarUrl }}" alt="" width="104" height="104" loading="eager" decoding="async">
                    @else
                        <span>{{ $teacherInitial }}</span>
                    @endif
                </div>
            <div class="teacher-dashboard-hero-copy">
                <span class="section-kicker">خلاصه وضعیت آموزشی · {{ $dashboardDateLabel }}</span>
                <h1>سلام استاد {{ $teacherUser?->name }} <span aria-hidden="true">👋</span></h1>
                <p>کلاس‌ها، ارزیابی‌ها و جلسه‌های پیش‌رو را از همین پنل پیگیری کن.</p>
                <div class="teacher-dashboard-hero-meta">
                    <span aria-hidden="true"></span>
                    <small>شاخص‌ها از سوابق واقعی کلاس‌ها، ارزیابی‌ها و پیشرفت ثبت‌شده محاسبه می‌شوند.</small>
                </div>
            </div>
        </header>

            <p class="dashboard-search-empty" data-dashboard-search-empty role="status" aria-live="polite" hidden>
                موردی با این عبارت پیدا نشد. عبارت دیگری را امتحان کن.
            </p>

            <section class="k6" aria-label="شاخص‌های آموزشی">
                @forelse($kpis as $metric)
                    <a class="teacher-kpi {{ $metric['tone'] }}" style="--c:var(--{{ $metric['tone'] }})" href="{{ $metric['url'] }}" aria-label="{{ $metric['label'] }}: {{ $metric['value'] }}. {{ $metric['hint'] }}" data-dashboard-searchable>
                        <span class="k6-label">{{ $metric['label'] }}</span>
                        <i aria-hidden="true"><svg class="i"><use href="#{{ $metric['icon'] }}"/></svg></i>
                        <b>{{ $metric['value'] }}</b>
                        <small>{{ $metric['hint'] }}</small>
                        <span class="teacher-kpi-action" aria-hidden="true">مشاهده جزئیات ←</span>
                    </a>
                @empty
                    <div class="cd empty-state">برای این حساب هنوز شاخصی با دسترسی مجاز وجود ندارد.</div>
                @endforelse
            </section>

            <section class="al {{ $pendingReviews === 0 ? 'clear' : '' }}" aria-label="وضعیت نیازمند توجه">
                <svg class="i" aria-hidden="true"><use href="{{ $pendingReviews > 0 ? '#clip' : '#chk' }}"/></svg>
                <div class="al-copy">
                    <strong>
                        @if($hasPendingAssignments)
                            {{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }} تکلیف منتظر بررسی است.
                        @elseif($hasPendingExams)
                            {{ \App\Support\PersianUi::digits($pendingExamReviews) }} آزمون منتظر بررسی است.
                        @elseif($canViewClassrooms && $activeClasses > 0)
                            کلاس‌های شما آماده‌اند؛ برنامه امروز را مرور کن.
                        @else
                            نمای امروز بر اساس اطلاعات ثبت‌شده در حساب شما آماده است.
                        @endif
                    </strong>
                    <small>
                        @if($hasPendingAssignments && !$canManageAssignments)
                            تکالیف قابل مشاهده‌اند؛ برای ثبت بازخورد به مجوز مدیریت تکالیف نیاز است.
                        @elseif($hasPendingExams && !$canManageExams)
                            آزمون‌ها قابل مشاهده‌اند؛ برای ثبت نمره به مجوز مدیریت آزمون‌ها نیاز است.
                        @else
                            آمار و فهرست‌های این صفحه از اطلاعات واقعی سامانه بارگذاری شده‌اند.
                        @endif
                    </small>
                </div>
                @if($focusRoute)
                    <a href="{{ $focusRoute }}">{{ $focusLabel }} ←</a>
                @endif
            </section>

            <div class="dh">
                <div class="col">
                    @if($canViewClassrooms)
                        <section class="cd teacher-classroom-roster-section" aria-labelledby="teacher-classroom-roster-heading">
                            <h3 id="teacher-classroom-roster-heading">کلاس‌های من و اعضای هر کلاس <a href="{{ route('teacher.classrooms.index') }}">مشاهده همه کلاس‌ها ←</a></h3>
                            <div class="teacher-classroom-roster-grid">
                                @forelse(($classroomRosterPreviews ?? collect()) as $classPreview)
                                    <a class="teacher-classroom-roster-card" href="{{ $classPreview['url'] }}" aria-label="مشاهده کلاس {{ $classPreview['title'] }} و اعضای آن">
                                        <span class="teacher-roster-card-head">
                                            <span class="teacher-roster-card-icon"><svg class="i" aria-hidden="true"><use href="#users"/></svg></span>
                                            <span class="teacher-roster-class-copy">
                                                <strong>{{ $classPreview['title'] }}</strong>
                                                <small>{{ $classPreview['course_title'] }}</small>
                                            </span>
                                            <b class="teacher-roster-count">{{ \App\Support\PersianUi::digits($classPreview['student_count']) }} نفر</b>
                                        </span>
                                        @if($classPreview['students']->isNotEmpty())
                                            <span class="teacher-roster-students">
                                                @foreach($classPreview['students'] as $classStudent)
                                                    <span class="teacher-roster-student">
                                                        <span class="teacher-roster-avatar">
                                                            @if($classStudent->avatar_url)
                                                                <img src="{{ $classStudent->avatar_url }}" alt="" width="42" height="42" loading="lazy" decoding="async">
                                                            @else
                                                                <span>{{ \Illuminate\Support\Str::substr(trim((string) $classStudent->name), 0, 1) }}</span>
                                                            @endif
                                                        </span>
                                                        <span class="teacher-roster-student-name">{{ $classStudent->name }}</span>
                                                    </span>
                                                @endforeach
                                                @if($classPreview['student_count'] > $classPreview['students']->count())
                                                    <span class="teacher-roster-more">+{{ \App\Support\PersianUi::digits($classPreview['student_count'] - $classPreview['students']->count()) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="teacher-roster-empty">هنوز دانش‌آموز فعالی با ثبت‌نام معتبر در این کلاس نیست.</span>
                                        @endif
                                        <span class="teacher-roster-card-action">باز کردن فهرست کلاس ←</span>
                                    </a>
                                @empty
                                    <div class="empty-state">هنوز کلاسی به حساب شما متصل نشده است.</div>
                                @endforelse
                            </div>
                        </section>
                    @endif

                    @if($canViewStudents)
                        <section class="cd" aria-labelledby="teacher-students-heading">
                            <h3 id="teacher-students-heading">دانش‌آموزان من <a href="{{ route('teacher.students.index') }}">مشاهده همه ←</a></h3>
                            @if($featuredStudents->isNotEmpty())
                                <div class="sv">
                                    @foreach($featuredStudents as $student)
                                        <a class="teacher-student-row" href="{{ route('teacher.students.index') }}" aria-label="مشاهده دانش‌آموزان، {{ $student->name }}" data-dashboard-searchable>
                                            <span class="av2 {{ $studentTones[$loop->index % count($studentTones)] }}">
                                                @if($student->avatar_url ?? false)
                                                    <img src="{{ $student->avatar_url }}" alt="" width="36" height="36" loading="lazy" decoding="async">
                                                @else
                                                    {{ \Illuminate\Support\Str::substr(trim((string) ($student->name ?? 'د')), 0, 1) }}
                                                @endif
                                            </span>
                                            <b>{{ $student->name }}</b>
                                            <small>{{ \App\Support\PersianUi::digits(max(0, min(100, (float) ($student->progress_average ?? 0)))) }}٪ پیشرفت · مشاهده اعضا ←</small>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-state">هنوز دادهٔ کافی برای نمایش پیشرفت دانش‌آموزان ثبت نشده است.</div>
                            @endif
                        </section>
                    @endif

                    @if($canViewCourses)
                        <section class="cd" aria-labelledby="teacher-courses-heading">
                            <h3 id="teacher-courses-heading">دوره‌های من <a href="{{ route('teacher.courses.index') }}">مدیریت دوره‌ها ←</a></h3>
                            <div class="crs">
                                @forelse($courseCards->take(6) as $course)
                                    @php($courseValue = max(0, min(100, (float) ($course->progress_average ?? 0))))
                                    <a class="teacher-course-card {{ $courseTones[$loop->index % count($courseTones)] }}" href="{{ route('teacher.courses.progress', $course->course_id) }}" data-dashboard-searchable aria-label="مشاهده روند دوره {{ $course->title }}">
                                        <span class="tg">دوره</span>
                                        <b>{{ $course->title }}</b>
                                        <small>{{ \App\Support\PersianUi::digits($course->student_count ?? 0) }} دانش‌آموز</small>
                                        <div class="pb" role="progressbar" aria-label="میانگین پیشرفت {{ $course->title }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $courseValue }}"><i style="width:{{ $courseValue }}%"></i></div>
                                        <small>میانگین پیشرفت {{ \App\Support\PersianUi::digits($courseValue) }}٪</small>
                                    </a>
                                @empty
                                    <div class="empty-state">برای دوره‌های شما هنوز دادهٔ پیشرفتی ثبت نشده است.</div>
                                @endforelse
                            </div>
                        </section>
                    @endif

                    @if($canViewAssignments || $canViewExams)
                        <section class="cd" id="teacher-reviews" aria-labelledby="teacher-reviews-heading">
                            <h3 id="teacher-reviews-heading">آخرین فعالیت‌ها <a href="{{ $canViewAssignments ? route('teacher.assignments.index') : route('teacher.exams.index') }}">مشاهده موارد ←</a></h3>
                            @if($canViewAssignments)
                                @forelse($recentAssignments->take(6) as $activity)
                                    <a class="tl teacher-dashboard-action-row" href="{{ route('teacher.assignments.index') }}" data-dashboard-searchable>
                                        <span class="content-icon"><svg class="i" aria-hidden="true"><use href="#file"/></svg></span>
                                        <span class="tl-copy"><b>{{ $activity->title }}</b><small>{{ $activity->classroom?->title ?? 'دوره' }} · @if($activity->due_at)مهلت {{ \App\Support\PersianUi::date($activity->due_at) }}@else بدون موعد مشخص@endif</small></span>
                                        <span class="bg {{ (int) ($activity->pending_review_count ?? 0) > 0 ? 'wr' : 'ok' }}">{{ \App\Support\PersianUi::digits($activity->pending_review_count ?? 0) }} منتظر بررسی</span>
                                    </a>
                                @empty
                                    <div class="empty-state">هنوز تکلیفی برای نمایش ثبت نشده است.</div>
                                @endforelse
                            @endif
                            @if($canViewExams && $pendingExamReviews > 0)
                                <a class="tl teacher-dashboard-action-row" href="{{ route('teacher.exams.index') }}" data-dashboard-searchable>
                                    <span class="content-icon"><svg class="i" aria-hidden="true"><use href="#clip"/></svg></span>
                                    <span class="tl-copy"><b>پاسخ‌های آزمون در انتظار بررسی</b><small>فهرست آزمون‌ها و پاسخ‌های ارسال‌شده</small></span>
                                    <span class="bg wr">{{ \App\Support\PersianUi::digits($pendingExamReviews) }}</span>
                                </a>
                            @endif
                        </section>
                    @endif

                    @if($canViewReports)
                <section class="cd" id="teacher-progress" data-dashboard-chart data-chart='@json(["week" => $weekChart, "month" => $monthChart])' aria-labelledby="teacher-progress-heading">
                        <h3 id="teacher-progress-heading">
                            روند پیشرفت ثبت‌شده
                            <span class="seg" role="group" aria-label="بازه نمودار">
                                <button type="button" class="on" data-chart-range="week" aria-pressed="true">هفته</button>
                                <button type="button" data-chart-range="month" aria-pressed="false">ماه</button>
                            </span>
                        </h3>
                        <div class="bars" data-chart-bars role="img" aria-label="نمودار پیشرفت ثبت‌شده">
                            @for($index = 0; $index < $chartSlots; $index++)
                                @php($chartValue = max(0, min(100, (float) ($weekChart['values'][$index] ?? 0))))
                                <div><b>{{ \App\Support\PersianUi::digits(round($chartValue)) }}٪</b><i style="height:{{ $chartValue }}%"></i><small>{{ $weekChart['labels'][$index] ?? '' }}</small></div>
                            @endfor
                        </div>
                        <p class="muted-note" data-chart-empty @if($chartSlots > 0) hidden @endif>هنوز داده‌ای برای رسم نمودار این بازه ثبت نشده است.</p>
                        <p class="muted-note">نمودار بر اساس داده‌های ثبت‌شدهٔ پیشرفت درس‌هاست؛ دادهٔ ثبت‌نشده به‌عنوان فعالیت فرضی نمایش داده نمی‌شود.</p>
                    </section>
                @endif
                </div>

                <aside class="col">
                    @if($canViewLiveClasses)
                        <section class="cd" aria-labelledby="teacher-today-heading">
                            <h3 id="teacher-today-heading">برنامه امروز <a href="{{ route('teacher.schedule.index') }}">برنامه کامل ←</a></h3>
                            @forelse($todayList as $session)
                                @if($session['can_join'])
                                    <a class="ls {{ $session['tone'] }}" href="{{ $session['meeting_url'] }}" target="_blank" rel="noopener noreferrer" data-dashboard-searchable>
                                @else
                                    <a class="ls {{ $session['tone'] }} teacher-dashboard-action-row" href="{{ route('teacher.schedule.index') }}" data-dashboard-searchable>
                                @endif
                                    @if($session['can_join'])<span class="jn">ورود ↗</span>@endif
                                    <b>{{ $session['title'] ?? 'جلسه آموزشی' }}</b>
                                    <small>{{ $session['time'] ?? '—' }} · {{ $session['meta'] ?? '' }}</small>
                                    <small>{{ $session['status'] }}</small>
                                </a>
                            @empty
                                <div class="empty-state">برای امروز جلسه‌ای در برنامه ثبت نشده است.</div>
                            @endforelse
                        </section>
                    @endif

                    @if($canViewLiveClasses)
                        <section class="cd" aria-labelledby="teacher-upcoming-heading">
                            <h3 id="teacher-upcoming-heading">کلاس‌های پیش‌رو <a href="{{ route('teacher.live-classes.index') }}">مشاهده همه ←</a></h3>
                            @forelse($upcomingList as $class)
                                @php($classCanJoin = (bool) ($class->can_join ?? false) && filled($class->meeting_url ?? null))
                                @if($classCanJoin)
                                    <a class="tl teacher-dashboard-action-row" href="{{ $class->meeting_url }}" target="_blank" rel="noopener noreferrer" data-dashboard-searchable>
                                @else
                                    <a class="tl teacher-dashboard-action-row" href="{{ route('teacher.live-classes.index') }}" data-dashboard-searchable>
                                @endif
                                    <span class="content-icon"><svg class="i" aria-hidden="true"><use href="#vid"/></svg></span>
                                    <span class="tl-copy">
                                        <b>{{ $class->course?->title ?? 'کلاس آنلاین' }}</b>
                                        <small>{{ $class->classroom?->title ?? '' }} · {{ \App\Support\PersianUi::date($class->scheduled_at) }} {{ \App\Support\PersianUi::time($class->scheduled_at) }}</small>
                                    </span>
                                    @if($classCanJoin)
                                        <span class="bg sk teacher-live-join">ورود ↗</span>
                                    @else
                                        <span class="bg {{ $class->status === 'live' ? 'wr' : 'mu' }}">{{ $class->status === 'live' ? 'در حال بررسی لینک' : 'زمان‌بندی‌شده' }}</span>
                                    @endif
                                </a>
                            @empty
                                <div class="empty-state">کلاس آنلاینی برای روزهای آینده ثبت نشده است.</div>
                            @endforelse
                        </section>
                    @endif
                </aside>
            </div>

    </div>
@endsection