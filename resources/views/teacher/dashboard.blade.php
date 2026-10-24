@extends('layouts.teacher')

@section('title', 'داشبورد مدرس | شیخان')

@section('content')
    @php
        $teacherUser = auth()->user();
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

        $kpis = [
            ['label' => 'کلاس‌های فعال', 'value' => \App\Support\PersianUi::digits($activeClasses), 'hint' => 'کلاس‌های تحت مدیریت', 'route' => 'teacher.classrooms.index', 'tone' => 'vi', 'icon' => 'users', 'permission' => 'classrooms.view'],
            ['label' => 'دوره‌های من', 'value' => \App\Support\PersianUi::digits($courseCards->count()), 'hint' => 'دوره‌های دارای فعالیت', 'route' => 'teacher.courses.index', 'tone' => 'ok', 'icon' => 'book', 'permission' => 'courses.view'],
            ['label' => 'جلسات این هفته', 'value' => \App\Support\PersianUi::digits($weeklySessions), 'hint' => \App\Support\PersianUi::digits($completedSessionsCount) . ' جلسه برگزار شده', 'route' => 'teacher.schedule.index', 'tone' => 'wr', 'icon' => 'vid', 'permission' => 'live_classes.view'],
            ['label' => 'دانش‌آموزان', 'value' => \App\Support\PersianUi::digits($studentCount), 'hint' => 'دانش‌آموزان کلاس‌های شما', 'route' => 'teacher.students.index', 'tone' => 'sk', 'icon' => 'hs', 'permission' => 'students.view'],
            ['label' => 'تکالیف منتظر تصحیح', 'value' => \App\Support\PersianUi::digits($pendingAssignmentReviews), 'hint' => 'ارسال‌شده و نیازمند بازخورد', 'route' => 'teacher.assignments.index', 'tone' => 'er', 'icon' => 'file', 'permission' => 'assignments.view'],
            ['label' => 'آزمون‌های منتظر بررسی', 'value' => \App\Support\PersianUi::digits($pendingExamReviews), 'hint' => 'پاسخ‌های نیازمند ارزیابی', 'route' => 'teacher.exams.index', 'tone' => 'vi', 'icon' => 'clip', 'permission' => 'exams.view'],
        ];
        $kpis = array_values(array_filter($kpis, fn ($item) => $teacherUser?->hasPermission($item['permission'])));

        $studentTones = ['ac', 'ok', 'vi', 'sk', 'wr'];
        $courseTones = ['ok', 'sk', 'vi', 'wr'];
    @endphp

    <header class="ph">
                <div>
                    <span class="section-kicker">خلاصه وضعیت آموزشی · {{ $dashboardDateLabel }}</span>
                    <h1>سلام، {{ $teacherUser?->name }} <span aria-hidden="true">👋</span></h1>
                    <p>کلاس‌ها، ارزیابی‌ها و جلسه‌های پیش‌رو را از همین پنل پیگیری کن.</p>
                </div>
                <div class="ac">
                    @if($canViewLiveClasses)
                        <a class="bt g" href="{{ route('teacher.schedule.index') }}"><svg class="i" aria-hidden="true"><use href="#cal"/></svg> برنامه هفتگی</a>
                    @endif
                    @if($canViewAssignments)
                        <a class="bt" href="{{ route('teacher.assignments.index') }}"><svg class="i" aria-hidden="true"><use href="#file"/></svg> مدیریت تکالیف</a>
                    @endif
                </div>
            </header>

            <section class="k6" aria-label="شاخص‌های آموزشی">
                @forelse($kpis as $metric)
                    <a class="{{ $metric['tone'] }}" style="--c:var(--{{ $metric['tone'] === 'vi' ? 'vi' : $metric['tone'] }})"
                       href="{{ route($metric['route']) }}" data-dashboard-searchable>
                        <span class="k6-label">{{ $metric['label'] }}</span>
                        <i aria-hidden="true"><svg class="i"><use href="#{{ $metric['icon'] }}"/></svg></i>
                        <b>{{ $metric['value'] }}</b>
                        <small>{{ $metric['hint'] }}</small>
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
                    @if($canViewStudents)
                        <section class="cd" aria-labelledby="teacher-students-heading">
                            <h3 id="teacher-students-heading">دانش‌آموزان من <a href="{{ route('teacher.students.index') }}">مشاهده همه ←</a></h3>
                            @if($featuredStudents->isNotEmpty())
                                <div class="sv">
                                    @foreach($featuredStudents as $student)
                                        <a href="{{ route('teacher.students.index') }}" data-dashboard-searchable>
                                            <span class="av2 {{ $studentTones[$loop->index % count($studentTones)] }}" style="background:color-mix(in srgb,var(--c) 78%,var(--sf))">{{ \Illuminate\Support\Str::substr(trim((string) ($student->name ?? 'د')), 0, 1) }}</span>
                                            <b>{{ $student->name }}</b>
                                            <small>{{ \App\Support\PersianUi::digits(max(0, min(100, (float) ($student->progress_average ?? 0)))) }}٪ پیشرفت</small>
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
                                    <a class="{{ $courseTones[$loop->index % count($courseTones)] }}" href="{{ route('teacher.courses.index') }}" data-dashboard-searchable>
                                        <span class="tg">دوره</span>
                                        <b>{{ $course->title }}</b>
                                        <small>{{ \App\Support\PersianUi::digits($course->student_count ?? 0) }} دانش‌آموز</small>
                                        <div class="pb" role="progressbar" aria-label="میانگین پیشرفت {{ $course->title }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $courseValue }}"><i style="width:{{ $courseValue }}%"></i></div>
                                        <small>میانگین پیشرفت {{ \App\Support\PersianUi::digits($courseValue) }}٪</small>
                                        <span class="go">نمایش دوره <span aria-hidden="true">←</span></span>
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
                                    <a class="tl" href="{{ route('teacher.assignments.index') }}" data-dashboard-searchable>
                                        <span class="content-icon"><svg class="i" aria-hidden="true"><use href="#file"/></svg></span>
                                        <span class="tl-copy"><b>{{ $activity->title }}</b><small>{{ $activity->classroom?->title ?? 'دوره' }} · @if($activity->due_at)مهلت {{ \App\Support\PersianUi::date($activity->due_at) }}@else بدون موعد مشخص@endif</small></span>
                                        <span class="bg {{ (int) ($activity->pending_review_count ?? 0) > 0 ? 'wr' : 'ok' }}">{{ \App\Support\PersianUi::digits($activity->pending_review_count ?? 0) }} منتظر بررسی</span>
                                    </a>
                                @empty
                                    <div class="empty-state">هنوز تکلیفی برای نمایش ثبت نشده است.</div>
                                @endforelse
                            @endif
                            @if($canViewExams && $pendingExamReviews > 0)
                                <a class="tl" href="{{ route('teacher.exams.index') }}" data-dashboard-searchable>
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
                                    <div class="ls {{ $session['tone'] }}" data-dashboard-searchable>
                                @endif
                                    @if($session['can_join'])<span class="jn">ورود ↗</span>@endif
                                    <b>{{ $session['title'] ?? 'جلسه آموزشی' }}</b>
                                    <small>{{ $session['time'] ?? '—' }} · {{ $session['meta'] ?? '' }}</small>
                                    <small>{{ $session['status'] }}</small>
                                @if($session['can_join'])</a>@else</div>@endif
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
                                <div class="tl" data-dashboard-searchable>
                                    <span class="content-icon"><svg class="i" aria-hidden="true"><use href="#vid"/></svg></span>
                                    <span class="tl-copy">
                                        <b>{{ $class->course?->title ?? 'کلاس آنلاین' }}</b>
                                        <small>{{ $class->classroom?->title ?? '' }} · {{ \App\Support\PersianUi::date($class->scheduled_at) }} {{ \App\Support\PersianUi::time($class->scheduled_at) }}</small>
                                    </span>
                                    @if($classCanJoin)
                                        <a class="bg sk" href="{{ $class->meeting_url }}" target="_blank" rel="noopener noreferrer" aria-label="ورود به {{ $class->course?->title ?? 'کلاس آنلاین' }}">ورود ↗</a>
                                    @else
                                        <span class="bg {{ $class->status === 'live' ? 'wr' : 'mu' }}">{{ $class->status === 'live' ? 'در حال بررسی لینک' : 'زمان‌بندی‌شده' }}</span>
                                    @endif
                                </div>
                            @empty
                                <div class="empty-state">کلاس آنلاینی برای روزهای آینده ثبت نشده است.</div>
                            @endforelse
                        </section>
                    @endif
                </aside>
            </div>

@endsection