@extends('layouts.teacher-dashboard')

@section('title', 'داشبورد مدرس | شیخان')

@section('content')
    @php
        $dashboardMetrics = $metrics ?? [];
        $pendingAssignmentReviews = max(0, (int) ($dashboardMetrics['pendingAssignmentReviews'] ?? 0));
        $pendingExamReviews = max(0, (int) ($dashboardMetrics['pendingExamReviews'] ?? 0));
        $canViewClassrooms = (bool) auth()->user()?->hasPermission('classrooms.view');
        $canViewCourses = (bool) auth()->user()?->hasPermission('courses.view');
        $canViewStudents = (bool) auth()->user()?->hasPermission('students.view');
        $canViewAssignments = (bool) auth()->user()?->hasPermission('assignments.view');
        $canViewExams = (bool) auth()->user()?->hasPermission('exams.view');
        $canViewAttendance = (bool) auth()->user()?->hasPermission('attendance.view');
        $canViewLiveClasses = (bool) auth()->user()?->hasPermission('live_classes.view');
        $canViewParentMessages = (bool) auth()->user()?->hasPermission('parent_messages.view');
        $canReviewPendingAssignments = $canViewAssignments && $pendingAssignmentReviews > 0;
        $canReviewPendingExams = $canViewExams && $pendingExamReviews > 0;
        $pendingReviews = (int) ($canViewAssignments ? $pendingAssignmentReviews : 0)
            + (int) ($canViewExams ? $pendingExamReviews : 0);
        $activeClasses = max(0, (int) ($dashboardMetrics['activeClasses'] ?? 0));
        $studentCount = max(0, (int) ($dashboardMetrics['studentCount'] ?? 0));
        $weeklySessions = max(0, (int) ($dashboardMetrics['weeklySessions'] ?? 0));
        $completedSessionsCount = max(0, (int) ($completedSessions ?? 0));
        $courseCards = collect($courseProgress ?? []);
        $featuredStudents = collect($topStudents ?? []);
        $recentAssignments = collect($activities ?? []);
        $weekChart = $chart['week'] ?? ['labels' => [], 'values' => []];
        $monthChart = $chart['month'] ?? ['labels' => [], 'values' => []];
        $chartSlots = max(count($weekChart['values'] ?? []), count($monthChart['values'] ?? []));
        $dashboardDateLabel = $dashboardDate ?? \App\Support\PersianUi::date(now());
        $focusRoute = $canReviewPendingAssignments
            ? route('teacher.assignments.index')
            : ($canReviewPendingExams
                ? route('teacher.exams.index')
                : ($canViewClassrooms
                    ? route('teacher.classrooms.index')
                    : ($canViewLiveClasses ? route('teacher.schedule.index') : null)));
        $focusLabel = $canReviewPendingAssignments
            ? 'شروع تصحیح تکالیف'
            : ($canReviewPendingExams
                ? 'بررسی آزمون‌ها'
                : ($canViewClassrooms ? 'مدیریت کلاس‌ها' : 'مشاهده برنامه'));
        $kpis = [
            ['label' => 'کلاس‌های فعال', 'value' => \App\Support\PersianUi::digits($activeClasses), 'hint' => 'کلاس‌های تحت مدیریت', 'route' => 'teacher.classrooms.index', 'tone' => 'indigo', 'icon' => 'classes', 'permission' => 'classrooms.view'],
            ['label' => 'دوره‌های من', 'value' => \App\Support\PersianUi::digits($courseCards->count()), 'hint' => 'دوره‌های دارای فعالیت', 'route' => 'teacher.courses.index', 'tone' => 'blue', 'icon' => 'book', 'permission' => 'courses.view'],
            ['label' => 'جلسات این هفته', 'value' => \App\Support\PersianUi::digits($weeklySessions), 'hint' => \App\Support\PersianUi::digits($completedSessionsCount) . ' جلسه برگزار شده', 'route' => 'teacher.schedule.index', 'tone' => 'cyan', 'icon' => 'clock', 'permission' => 'live_classes.view'],
            ['label' => 'دانش‌آموزان', 'value' => \App\Support\PersianUi::digits($studentCount), 'hint' => 'دانش‌آموزان کلاس‌های شما', 'route' => 'teacher.students.index', 'tone' => 'violet', 'icon' => 'students', 'permission' => 'students.view'],
            ['label' => 'تکالیف منتظر تصحیح', 'value' => \App\Support\PersianUi::digits($pendingAssignmentReviews), 'hint' => 'ارسال‌شده و نیازمند بازخورد', 'route' => 'teacher.assignments.index', 'tone' => 'amber', 'icon' => 'assignment', 'permission' => 'assignments.view'],
            ['label' => 'آزمون‌های منتظر بررسی', 'value' => \App\Support\PersianUi::digits($pendingExamReviews), 'hint' => 'پاسخ‌های نیازمند ارزیابی', 'route' => 'teacher.exams.index', 'tone' => 'rose', 'icon' => 'exam', 'permission' => 'exams.view'],
        ];
    @endphp

    <section class="tdb-dashboard" data-teacher-dashboard data-dashboard-content data-chart='@json(["week" => $weekChart, "month" => $monthChart])'>
        <header class="tdb-page-head">
            <div class="tdb-heading">
                <span class="tdb-eyebrow">خلاصه وضعیت آموزشی · {{ $dashboardDateLabel }}</span>
                <h1>سلام، {{ $teacher->name }} <span aria-hidden="true">👋</span></h1>
                <p>کلاس‌ها، ارزیابی‌ها و جلسه‌های پیش‌رو را از یک نمای یکپارچه پیگیری کن.</p>
            </div>
            <div class="tdb-actions">
                @if($canViewLiveClasses)
                <a class="tdb-button" href="{{ route('teacher.schedule.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4m8-4v4M3 10h18"></path></svg>
                    برنامه هفتگی
                </a>
                @endif
                @if($canViewAssignments)
                <a class="tdb-button primary" href="{{ route('teacher.assignments.index') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 12h8M8 16h5"></path></svg>
                    مدیریت تکالیف
                </a>
                @endif
            </div>
        </header>

        <section class="tdb-kpis" aria-label="شاخص‌های آموزشی">
            @foreach($kpis as $metric)
                @if(auth()->user()?->hasPermission($metric['permission']))
                <a class="tdb-kpi {{ $metric['tone'] }}" href="{{ route($metric['route']) }}" data-dashboard-searchable>
                    <span class="tdb-kpi-top">
                        <span class="tdb-kpi-label">{{ $metric['label'] }}</span>
                        <span class="tdb-kpi-icon" aria-hidden="true">
                            @if($metric['icon'] === 'classes')
                                <svg viewBox="0 0 24 24"><rect x="3" y="4" width="8" height="7" rx="1.5"></rect><rect x="13" y="4" width="8" height="7" rx="1.5"></rect><rect x="3" y="13" width="8" height="7" rx="1.5"></rect><rect x="13" y="13" width="8" height="7" rx="1.5"></rect></svg>
                            @elseif($metric['icon'] === 'book')
                                <svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v18H6.5A2.5 2.5 0 0 1 4 18.5z"></path><path d="M4 5.5v13"></path></svg>
                            @elseif($metric['icon'] === 'clock')
                                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                            @elseif($metric['icon'] === 'students')
                                <svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"></circle><circle cx="17" cy="9" r="2.5"></circle><path d="M3 20v-1a6 6 0 0 1 12 0v1M16 15a5 5 0 0 1 5 5"></path></svg>
                            @elseif($metric['icon'] === 'assignment')
                                <svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 12h8M8 16h5"></path></svg>
                            @else
                                <svg viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg>
                            @endif
                        </span>
                    </span>
                    <strong>{{ $metric['value'] }}</strong>
                    <small>{{ $metric['hint'] }}</small>
                </a>
                @endif
            @endforeach
        </section>

        <section class="tdb-focus-card {{ $pendingReviews > 0 ? 'has-pending' : '' }}" aria-labelledby="tdb-focus-title">
            <span class="tdb-focus-icon" aria-hidden="true">
                @if($pendingReviews > 0)
                    <svg viewBox="0 0 24 24"><path d="M12 3 2.8 20h18.4L12 3Z"></path><path d="M12 9v5m0 3h.01"></path></svg>
                @else
                    <svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path><circle cx="12" cy="12" r="9"></circle></svg>
                @endif
            </span>
            <div class="tdb-focus-copy">
                <span class="tdb-card-kicker">{{ $pendingReviews > 0 ? 'نیازمند توجه' : 'وضعیت امروز' }}</span>
                <h2 class="tdb-focus-title" id="tdb-focus-title">
                    @if($canReviewPendingAssignments)
                        {{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }} تکلیف برای تصحیح باقی مانده است.
                    @elseif($canReviewPendingExams)
                        {{ \App\Support\PersianUi::digits($pendingExamReviews) }} آزمون برای بررسی باقی مانده است.
                    @elseif($activeClasses > 0 && $canViewClassrooms)
                        کلاس‌ها آماده‌اند؛ برنامه امروزت را مرور کن.
                    @elseif($activeClasses > 0)
                        وضعیت آموزشی شما به‌روز است؛ بخش‌های مجاز در دسترس‌اند.
                    @else
                        هنوز کلاس فعالی به این حساب متصل نشده است.
                    @endif
                </h2>
                <p>
                    @if($canReviewPendingAssignments)
                        بازخورد به‌موقع به دانش‌آموز کمک می‌کند مسیر بعدی یادگیری را روشن‌تر ببیند.
                    @elseif($canReviewPendingExams)
                        پاسخ‌های ارسال‌شده را از بخش آزمون‌ها بررسی و نهایی کن.
                    @elseif($activeClasses > 0 && $canViewClassrooms)
                        برنامه جلسات، دوره‌ها و پیگیری‌های آموزشی در همین صفحه در دسترس‌اند.
                    @elseif($activeClasses > 0)
                        از بخش‌های مجاز پنل برای ادامه کار استفاده کن.
                    @else
                        با اتصال کلاس یا دوره به حساب مدرس، اطلاعات واقعی آموزشی این صفحه نمایش داده می‌شود.
                    @endif
                </p>
            </div>
            @if($focusRoute)
                <a class="tdb-focus-action" href="{{ $focusRoute }}">{{ $focusLabel }} <span aria-hidden="true">←</span></a>
            @endif
        </section>

        <div class="tdb-layout">
            <div class="tdb-stack tdb-main-column">
                @if($canViewStudents)
                <section class="tdb-card" aria-labelledby="tdb-students-title">
                    <div class="tdb-card-head">
                        <div><span class="tdb-card-kicker">یادگیرندگان</span><h2 id="tdb-students-title">دانش‌آموزان با پیشرفت بالاتر</h2></div>
                        <a class="tdb-card-link" href="{{ route('teacher.students.index') }}">همه دانش‌آموزان <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="tdb-students">
                        @forelse($featuredStudents as $studentItem)
                            @php($studentProgress = max(0, min(100, (float) ($studentItem->progress_average ?? 0))))
                            <a class="tdb-student" href="{{ route('teacher.students.index') }}" data-dashboard-searchable>
                                <span class="tdb-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::substr(trim((string) ($studentItem->name ?? '')), 0, 1) ?: 'د' }}</span>
                                <strong>{{ $studentItem->name }}</strong>
                                <small>{{ \App\Support\PersianUi::digits($studentProgress) }}٪ پیشرفت</small>
                            </a>
                        @empty
                            <div class="tdb-empty">هنوز دادهٔ کافی برای نمایش پیشرفت دانش‌آموزان ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>

                @endif

                @if($canViewCourses)
                <section class="tdb-card" aria-labelledby="tdb-courses-title">
                    <div class="tdb-card-head">
                        <div><span class="tdb-card-kicker">محتوای آموزشی</span><h2 id="tdb-courses-title">دوره‌های من</h2></div>
                        <a class="tdb-card-link" href="{{ route('teacher.courses.index') }}">مدیریت دوره‌ها <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="tdb-course-grid">
                        @forelse($courseCards->take(3) as $course)
                            @php($courseValue = max(0, min(100, (float) ($course->progress_average ?? 0))))
                            <a href="{{ route('teacher.courses.index') }}" class="tdb-course-card" data-dashboard-searchable>
                                <span class="tdb-course-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"></path><path d="M4 5.5v16"></path></svg></span>
                                <strong>{{ $course->title }}</strong>
                                <small>{{ \App\Support\PersianUi::digits($course->student_count ?? 0) }} دانش‌آموز · میانگین {{ \App\Support\PersianUi::digits($courseValue) }}٪</small>
                                <span class="tdb-progress-track" aria-label="میانگین پیشرفت دوره {{ $courseValue }} درصد"><i style="width: {{ $courseValue }}%"></i></span>
                                <span class="tdb-course-footer">نمایش دوره <b aria-hidden="true">←</b></span>
                            </a>
                        @empty
                            <div class="tdb-empty">برای دوره‌های شما هنوز دادهٔ پیشرفتی ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>

                @endif

                <section class="tdb-card" id="tdb-progress" aria-labelledby="tdb-progress-title">
                    <div class="tdb-card-head">
                        <div><span class="tdb-card-kicker">فعالیت ثبت‌شده در درس‌ها</span><h2 id="tdb-progress-title">روند پیشرفت دانش‌آموزان</h2></div>
                        <div class="tdb-chart-controls" role="group" aria-label="بازه نمودار">
                            <button type="button" class="active" data-teacher-range="week" aria-pressed="true">هفته</button>
                            <button type="button" data-teacher-range="month" aria-pressed="false">ماه</button>
                        </div>
                    </div>
                    <div class="tdb-chart-area" data-teacher-bars role="img" aria-label="نمودار فعالیت ثبت‌شده در درس‌ها">
                        @for($index = 0; $index < $chartSlots; $index++)
                            @php($chartValue = max(0, min(100, (float) ($weekChart['values'][$index] ?? 0))))
                            <div class="tdb-chart-column" data-chart-column>
                                <strong data-chart-value>{{ \App\Support\PersianUi::digits($chartValue) }}٪</strong>
                                <span class="tdb-chart-bar-track"><i data-chart-bar style="height: {{ $chartValue }}%"></i></span>
                                <small data-chart-label>{{ $weekChart['labels'][$index] ?? '' }}</small>
                            </div>
                        @endfor
                        <p class="tdb-empty" data-chart-empty @if($chartSlots > 0) hidden @endif>هنوز داده‌ای برای رسم نمودار این بازه ثبت نشده است.</p>
                    </div>
                    <p class="tdb-chart-note">نمودار بر اساس داده‌های ثبت‌شدهٔ پیشرفت درس‌هاست؛ تاریخچهٔ دقیق تغییرات روزبه‌روز در همهٔ فعالیت‌ها ذخیره نمی‌شود.</p>
                </section>

                @if($canViewAssignments)
                <section class="tdb-card" aria-labelledby="tdb-assignments-title">
                    <div class="tdb-card-head">
                        <div><span class="tdb-card-kicker">پیگیری سریع</span><h2 id="tdb-assignments-title">آخرین تکالیف</h2></div>
                        <a class="tdb-card-link" href="{{ route('teacher.assignments.index') }}">مشاهده همه <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="tdb-assignment-list">
                        @forelse($recentAssignments->take(6) as $activity)
                            <article class="tdb-assignment-row" data-dashboard-searchable>
                                <span class="tdb-assignment-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 12h8M8 16h5"></path></svg></span>
                                <span class="tdb-assignment-copy">
                                    <strong>{{ $activity->title }}</strong>
                                    <small>{{ $activity->classroom?->title ?? 'عمومی دوره' }} · @if($activity->due_at)مهلت {{ \App\Support\PersianUi::date($activity->due_at) }}، ساعت {{ \App\Support\PersianUi::time($activity->due_at) }}@else بدون موعد مشخص @endif</small>
                                </span>
                                <span class="tdb-assignment-count"><strong>{{ \App\Support\PersianUi::digits($activity->submitted_count ?? 0) }}</strong><small>تحویل</small></span>
                                <span class="tdb-assignment-count is-pending"><strong>{{ \App\Support\PersianUi::digits($activity->pending_review_count ?? 0) }}</strong><small>منتظر بررسی</small></span>
                            </article>
                        @empty
                            <div class="tdb-empty">هنوز تکلیفی برای نمایش ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>
                @endif
            </div>

            <aside class="tdb-stack tdb-side-column">
                @if($canViewLiveClasses)
                <section class="tdb-card" aria-labelledby="tdb-today-title">
                    <div class="tdb-card-head">
                        <div><span class="tdb-card-kicker">{{ $dashboardDateLabel }}</span><h2 id="tdb-today-title">برنامه امروز</h2></div>
                        <a class="tdb-card-link" href="{{ route('teacher.schedule.index') }}">برنامه کامل <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="tdb-list">
                        @forelse(collect($todaySessions ?? [])->take(5) as $session)
                            @php($isLive = ($session['status'] ?? '') === 'در حال برگزاری')
                            <article class="tdb-list-item" data-dashboard-searchable>
                                <span class="tdb-time">{{ $session['time'] ?? '—' }}</span>
                                <span class="tdb-status-dot {{ $isLive ? 'is-live' : '' }}" aria-hidden="true"></span>
                                <span class="tdb-list-copy"><strong>{{ $session['title'] ?? 'جلسه آموزشی' }}</strong><small>{{ $session['meta'] ?? 'بدون توضیح' }}</small></span>
                                <span class="tdb-list-actions">
                                    <span class="tdb-status {{ $isLive ? 'is-live' : '' }}">{{ $session['status'] ?? 'برنامه‌ریزی‌شده' }}</span>
                                    @if(!empty($session['can_join']) && filled($session['meeting_url'] ?? null))
                                        <a class="tdb-join teacher-live-join" href="{{ $session['meeting_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="ورود به جلسه آنلاین {{ $session['title'] ?? 'کلاس' }}">ورود <span aria-hidden="true">↗</span></a>
                                    @endif
                                </span>
                            </article>
                        @empty
                            <div class="tdb-empty">برای امروز جلسه‌ای در برنامه ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>

                @endif

                <section class="tdb-card" aria-labelledby="tdb-shortcuts-title">
                    <div class="tdb-card-head"><div><span class="tdb-card-kicker">دسترسی سریع</span><h2 id="tdb-shortcuts-title">مسیرهای پرکاربرد</h2></div></div>
                    <div class="tdb-shortcuts">
                        @if($canViewAssignments)
                        <a href="{{ route('teacher.assignments.index') }}" class="tdb-shortcut" data-dashboard-searchable><span class="tdb-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 12h8M8 16h5"></path></svg></span><span class="tdb-shortcut-copy"><strong>تکالیف</strong><small>{{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }} مورد برای تصحیح</small></span><span class="tdb-shortcut-arrow" aria-hidden="true">←</span></a>
                        @endif
                        @if($canViewExams)
                        <a href="{{ route('teacher.exams.index') }}" class="tdb-shortcut" data-dashboard-searchable><span class="tdb-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span><span class="tdb-shortcut-copy"><strong>آزمون‌ها</strong><small>{{ \App\Support\PersianUi::digits($pendingExamReviews) }} مورد برای بررسی</small></span><span class="tdb-shortcut-arrow" aria-hidden="true">←</span></a>
                        @endif
                        @if($canViewAttendance)
                        <a href="{{ route('teacher.attendance.index') }}" class="tdb-shortcut" data-dashboard-searchable><span class="tdb-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"></path><path d="M20 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></span><span class="tdb-shortcut-copy"><strong>حضور و غیاب</strong><small>ثبت و پیگیری حضور کلاس‌ها</small></span><span class="tdb-shortcut-arrow" aria-hidden="true">←</span></a>
                        @endif
                        @if($canViewLiveClasses)
                        <a href="{{ route('teacher.live-classes.index') }}" class="tdb-shortcut" data-dashboard-searchable><span class="tdb-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="13" height="14" rx="2"></rect><path d="m16 10 5-3v10l-5-3z"></path></svg></span><span class="tdb-shortcut-copy"><strong>کلاس‌های آنلاین</strong><small>جلسات زنده و زمان‌بندی‌شده</small></span><span class="tdb-shortcut-arrow" aria-hidden="true">←</span></a>
                        @endif
                        @if($canViewParentMessages)
                            <a href="{{ route('teacher.messages.index') }}" class="tdb-shortcut" data-dashboard-searchable><span class="tdb-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v12H9l-5 4z"></path><path d="M8 9h8m-8 4h5"></path></svg></span><span class="tdb-shortcut-copy"><strong>پیام‌های اولیا</strong><small>پاسخ و پیگیری مکاتبات</small></span><span class="tdb-shortcut-arrow" aria-hidden="true">←</span></a>
                        @endif
                        @if($canViewLiveClasses)
                        <a href="{{ route('teacher.meetings.index') }}" class="tdb-shortcut" data-dashboard-searchable><span class="tdb-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M8 12h8m-4-4v8"></path></svg></span><span class="tdb-shortcut-copy"><strong>جلسات با اولیا</strong><small>برنامه‌ریزی و پیگیری جلسه‌ها</small></span><span class="tdb-shortcut-arrow" aria-hidden="true">←</span></a>
                        @endif
                    </div>
                </section>

                @if($canViewLiveClasses)
                <section class="tdb-card" aria-labelledby="tdb-online-title">
                    <div class="tdb-card-head"><div><span class="tdb-card-kicker">هفت روز آینده</span><h2 id="tdb-online-title">کلاس‌های آنلاین</h2></div><a class="tdb-card-link" href="{{ route('teacher.live-classes.index') }}">همه جلسه‌ها <span aria-hidden="true">←</span></a></div>
                    <div class="tdb-list">
                        @forelse(collect($upcomingClasses ?? [])->take(5) as $item)
                            <article class="tdb-list-item" data-dashboard-searchable>
                                <span class="tdb-time">{{ \App\Support\PersianUi::time($item->scheduled_at) }}</span>
                                <span class="tdb-status-dot {{ ($item->status ?? '') === 'live' ? 'is-live' : '' }}" aria-hidden="true"></span>
                                <span class="tdb-list-copy"><strong>{{ $item->title }}</strong><small>{{ \App\Support\PersianUi::date($item->scheduled_at) }} · {{ $item->course?->title ?? 'دوره آموزشی' }} · {{ $item->classroom?->title ?? 'جلسه آزاد' }}</small></span>
                                <span class="tdb-list-actions">
                                    <span class="tdb-status {{ ($item->status ?? '') === 'live' ? 'is-live' : '' }}">{{ ($item->status ?? '') === 'live' ? 'در حال برگزاری' : 'زمان‌بندی‌شده' }}</span>
                                    @if($item->can_join && filled($item->meeting_url))
                                        <a class="tdb-join teacher-live-join" href="{{ $item->meeting_url }}" target="_blank" rel="noopener noreferrer" aria-label="ورود به جلسه آنلاین {{ $item->title }}">ورود <span aria-hidden="true">↗</span></a>
                                    @endif
                                </span>
                            </article>
                        @empty
                            <div class="tdb-empty">جلسه آنلاین فعالی برای هفت روز آینده ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>
                @endif
            </aside>
        </div>
    </section>
@endsection
