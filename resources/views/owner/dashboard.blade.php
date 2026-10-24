@extends('layouts.owner')

@section('title', 'کارتابل ادمین / منشی | شیخان')
@section('header-title', 'کارتابل ادمین / منشی')

@section('content')
    @php
        $executionLabels = [
            'running' => 'در حال اجرا',
            'scheduled' => 'برنامه‌ریزی‌شده',
            'finished' => 'پایان‌یافته',
            'active' => 'فعال',
            'archived' => 'آرشیو',
        ];

        $ownerUser = auth()->user();
        $firstAcademy = $academies->first();

        // Keep visible links and metrics in sync with the permission on their GET route.
        $canViewAcademy = (bool) $ownerUser?->hasPermission('academy.view');
        $canOpenPeople = $canViewAcademy && (bool) $firstAcademy;
        $canViewClassrooms = (bool) $ownerUser?->hasPermission('classrooms.view') && (bool) $firstAcademy;
        $canViewCourses = (bool) $ownerUser?->hasPermission('courses.view');
        $canViewAttendance = (bool) $ownerUser?->hasPermission('attendance.view') && (bool) $firstAcademy;
        $canViewFinance = (bool) $ownerUser?->hasPermission('finance.view');
        $canViewReports = (bool) $ownerUser?->hasPermission('reports.view');
        $canViewAssignments = $canViewCourses && (bool) $firstAcademy;
        $canViewMeetings = (bool) $ownerUser?->hasPermission('live_classes.view') && (bool) $firstAcademy;
        $hasMultipleAcademies = $academies->count() > 1;

        // Suppress numbers for workflows the account cannot open.
        $visiblePendingReviews = $canViewAssignments ? max(0, (int) ($metrics['pendingReviews'] ?? 0)) : 0;
        $visibleCapacityAlerts = $canViewClassrooms ? max(0, (int) ($metrics['capacityAlerts'] ?? 0)) : 0;
        $visibleClassroomsWithoutTeacher = $canViewClassrooms ? max(0, (int) ($metrics['classroomsWithoutTeacher'] ?? 0)) : 0;
        $attentionCount = $visiblePendingReviews + $visibleCapacityAlerts + $visibleClassroomsWithoutTeacher;
        $sales = $canViewFinance ? (float) ($metrics['sales'] ?? 0) : 0;
        $attendanceRate = $canViewAttendance ? ($metrics['todayAttendanceRate'] ?? null) : null;

        $focusRoute = null;
        if (! $hasMultipleAcademies && $visibleClassroomsWithoutTeacher > 0 && $firstAcademy) {
            $focusRoute = route('owner.classrooms.index', $firstAcademy);
        } elseif (! $hasMultipleAcademies && $visibleCapacityAlerts > 0 && $firstAcademy) {
            $focusRoute = route('owner.classrooms.index', $firstAcademy);
        } elseif (! $hasMultipleAcademies && $visiblePendingReviews > 0 && $firstAcademy) {
            $focusRoute = route('owner.assignments.index', $firstAcademy);
        }
    @endphp

    <div class="owner-dashboard space-y-6 pb-8">

        {{-- =========================================================
            HERO
        ========================================================== --}}
        <section class="owner-hero relative overflow-hidden">
            <div class="owner-hero-copy relative z-10">
                <span class="owner-kicker">
                    کارتابل ادمین / منشی
                </span>

                <h1 class="mt-3">
                    @if($academies->count() > 1)
                        وضعیت آموزشگاه‌ها را از یک صفحه مدیریت کن.
                    @else
                        {{ $firstAcademy?->name ?? 'آموزشگاه شما' }} را از یک صفحه مدیریت کن.
                    @endif
                </h1>

                <p class="mt-4 max-w-2xl">
                    تصویر واقعی عملیات آموزشی امروز را ببین؛
                    از کلاس‌ها و مدرس‌ها تا دانش‌آموزان، حضور و غیاب، دوره‌ها و مواردی که نیاز به پیگیری دارند.
                </p>

                <div class="owner-hero-meta" aria-label="خلاصه کارتابل">
                    <span class="owner-hero-meta-dot" aria-hidden="true"></span>
                    <span>نمای کلی شاخص‌ها و عملیات آموزشگاه</span>
                </div>
            </div>

            @if($canViewFinance)
                <div class="owner-hero-art owner-hero-finance-link" role="group" aria-label="درآمد خالص ثبت‌شده">
                    <div class="owner-orb one"></div>
                    <div class="owner-orb two"></div>
                    <div class="owner-hero-stat">
                        <small>درآمد خالص ثبت‌شده</small>
                        <strong>{{ \App\Support\PersianUi::number($sales) }}</strong>
                        <span>تومان · از دفتر پرداخت‌های تکمیل‌شده</span>
                    </div>
                </div>
            @endif
        </section>


        @if($canViewClassrooms || $canViewAssignments || $canViewReports)
            <section class="owner-focus-card {{ $attentionCount > 0 ? 'has-alert' : 'is-clear' }}" aria-labelledby="owner-focus-title">
                <div class="owner-focus-status" aria-hidden="true">
                    <span class="{{ $attentionCount > 0 ? 'has-alert' : 'is-clear' }}"></span>
                </div>
                <div class="owner-focus-copy">
                    <span class="owner-focus-kicker">صف امروز</span>
                    <h2 id="owner-focus-title">
                        @if($visibleClassroomsWithoutTeacher > 0)
                            {{ \App\Support\PersianUi::digits($visibleClassroomsWithoutTeacher) }} کلاس بدون مدرس داری.
                        @elseif($visibleCapacityAlerts > 0)
                            {{ \App\Support\PersianUi::digits($visibleCapacityAlerts) }} کلاس نزدیک ظرفیت است.
                        @elseif($visiblePendingReviews > 0)
                            {{ \App\Support\PersianUi::digits($visiblePendingReviews) }} مورد هنوز نیازمند بررسی است.
                        @else
                            امروز موردی برای پیگیری در بخش‌های مجاز این حساب ثبت نشده است.
                        @endif
                    </h2>
                    <p>شاخص‌های این بخش فقط از داده‌های واقعی و قابل‌دسترسی همین کارتابل می‌آیند.</p>
                </div>
                @if($focusRoute)
                    <div class="owner-focus-actions"><a href="{{ $focusRoute }}">رفتن به بخش مربوط ←</a></div>
                @endif
            </section>
        @endif

        {{-- KPI band: information only; navigation stays in the shared sidebar. --}}
        <section class="owner-stats" aria-label="شاخص‌های کارتابل">
            @if($canViewClassrooms)
                <article class="owner-stat-card tone-indigo" aria-label="کلاس‌های فعال">
                    <span class="owner-stat-label">کلاس‌های فعال <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['classrooms'] ?? 0) }}</strong>
                    <small>{{ \App\Support\PersianUi::digits($metrics['liveNow'] ?? 0) }} کلاس آنلاین در حال اجرا</small>
                </article>
            @endif
            @if($canOpenPeople)
                <article class="owner-stat-card tone-cyan" aria-label="دانش‌آموزان فعال">
                    <span class="owner-stat-label">دانش‌آموز فعال <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['students'] ?? 0) }}</strong>
                    <small>فهرست اعضای آموزشگاه</small>
                </article>
                <article class="owner-stat-card tone-violet" aria-label="مدرس‌های فعال">
                    <span class="owner-stat-label">مدرس فعال <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['teachers'] ?? 0) }}</strong>
                    <small>مدرس‌های آموزشگاه</small>
                </article>
            @endif
            @if($canViewCourses)
                <article class="owner-stat-card tone-blue" aria-label="دوره‌های منتشرشده">
                    <span class="owner-stat-label">دوره‌های منتشرشده <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['published'] ?? 0) }}</strong>
                    <small>از {{ \App\Support\PersianUi::digits($metrics['courses'] ?? 0) }} دوره ثبت‌شده</small>
                </article>
            @endif
            @if($canViewAttendance)
                <article class="owner-stat-card tone-emerald" aria-label="حضور امروز">
                    <span class="owner-stat-label">حضور امروز <i aria-hidden="true">↗</i></span>
                    <strong>{{ $attendanceRate === null ? '—' : \App\Support\PersianUi::digits($attendanceRate) . '٪' }}</strong>
                    <small>{{ $attendanceRate === null ? 'رکورد حضور امروز ثبت نشده' : 'بر اساس رکوردهای امروز' }}</small>
                </article>
            @endif
            @if($canViewClassrooms || $canViewAssignments || $canViewReports)
                @if($focusRoute)
                    <article class="owner-stat-card tone-amber" aria-label="موارد نیازمند توجه">
                @else
                    <article class="owner-stat-card tone-amber">
                @endif
                    <span class="owner-stat-label">نیازمند توجه @if($focusRoute)<i aria-hidden="true">↗</i>@endif</span>
                    <strong>{{ \App\Support\PersianUi::digits($attentionCount) }}</strong>
                    <small>
                        {{ \App\Support\PersianUi::digits($visiblePendingReviews) }} بررسی ·
                        {{ \App\Support\PersianUi::digits($visibleCapacityAlerts) }} کلاس نزدیک ظرفیت ·
                        {{ \App\Support\PersianUi::digits($visibleClassroomsWithoutTeacher) }} کلاس بدون مدرس
                    </small>
                @if($focusRoute)
                    </article>
                @else
                    </article>
                @endif
            @endif
            @if(!$canViewClassrooms && !$canOpenPeople && !$canViewCourses && !$canViewAttendance && !$canViewReports && !$canViewAssignments)
                <div class="dashboard-panel owner-empty" role="status">برای نمایش شاخص‌ها، دسترسی مشاهده به بخش‌های آموزشگاه برای این حساب فعال نیست.</div>
            @endif
        </section>

        {{-- Capacity chart and classroom status are calculated from active classroom records. --}}
        @if($canViewClassrooms)
            <section class="dashboard-panel owner-panel owner-classroom-overview" aria-labelledby="owner-classroom-title">
                <div class="owner-panel-head">
                    <div>
                        <h2 id="owner-classroom-title">وضعیت کلاس‌ها و ظرفیت</h2>
                        <p>میزان تکمیل ظرفیت و وضعیت کلاس‌های فعال؛ فقط بر اساس رکوردهای موجود</p>
                    </div>
                    @if($firstAcademy && !$hasMultipleAcademies)
                        <a href="{{ route('owner.classrooms.index', $firstAcademy) }}" class="owner-link">همه کلاس‌ها <span aria-hidden="true">←</span></a>
                    @elseif($hasMultipleAcademies)
                        <span class="owner-panel-hint">جزئیات هر آموزشگاه از منوی کناری</span>
                    @endif
                </div>

                @if($classrooms->isNotEmpty())
                    <div class="owner-classroom-list">
                        @foreach($classrooms as $classroom)
                            @php
                                $occupancy = $classroom->occupancy_percent === null
                                    ? null
                                    : min(100, max(0, (float) $classroom->occupancy_percent));
                                $occupancyTone = $occupancy !== null && $occupancy >= 90
                                    ? 'is-critical'
                                    : (($occupancy !== null && $occupancy >= 75) ? 'is-warning' : 'is-healthy');
                                $statusTone = match ($classroom->execution_status) {
                                    'running' => 'is-running',
                                    'scheduled' => 'is-scheduled',
                                    'finished' => 'is-finished',
                                    default => 'is-active',
                                };
                            @endphp
                            <article class="owner-classroom-item">
                                <div class="owner-classroom-heading">
                                    <div class="owner-classroom-copy">
                                        <h3>{{ $classroom->title }}</h3>
                                        <p>
                                            {{ $classroom->course?->title ?? 'بدون دوره' }}
                                            <span aria-hidden="true">·</span>
                                            {{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}
                                        </p>
                                    </div>
                                    <div class="owner-classroom-actions">
                                        <span class="owner-classroom-status {{ $statusTone }}">
                                            {{ $executionLabels[$classroom->execution_status] ?? 'فعال' }}
                                        </span>
                                        <a href="{{ route('owner.classrooms.show', [$classroom->academy, $classroom]) }}" class="owner-classroom-details">جزئیات</a>
                                    </div>
                                </div>

                                <div class="owner-classroom-stats">
                                    <div class="owner-classroom-stat">
                                        <span>دانش‌آموز فعال</span>
                                        <strong>{{ \App\Support\PersianUi::digits($classroom->active_students_count) }}</strong>
                                    </div>
                                    <div class="owner-classroom-stat">
                                        <span>ظرفیت کل</span>
                                        <strong>{{ $classroom->capacity === null ? 'تعریف نشده' : \App\Support\PersianUi::digits($classroom->capacity) }}</strong>
                                    </div>
                                    <div class="owner-classroom-stat">
                                        <span>ظرفیت خالی</span>
                                        <strong>{{ $classroom->capacity_remaining === null ? '—' : \App\Support\PersianUi::digits($classroom->capacity_remaining) }}</strong>
                                    </div>
                                    @if($canViewAttendance)
                                        <div class="owner-classroom-stat">
                                            <span>حضور امروز</span>
                                            <strong>{{ $classroom->today_attendance_rate === null ? 'ثبت نشده' : \App\Support\PersianUi::digits($classroom->today_attendance_rate) . '٪' }}</strong>
                                        </div>
                                    @endif
                                </div>

                                @if($occupancy !== null)
                                    <div class="owner-capacity-chart">
                                        <div class="owner-capacity-labels">
                                            <span>تکمیل ظرفیت</span>
                                            <strong>{{ \App\Support\PersianUi::digits($occupancy) }}٪</strong>
                                        </div>
                                        <div class="owner-capacity-track" role="img" aria-label="تکمیل ظرفیت کلاس {{ $classroom->title }}: {{ \App\Support\PersianUi::digits($occupancy) }} درصد">
                                            <span class="{{ $occupancyTone }}" style="width: {{ $occupancy }}%"></span>
                                        </div>
                                    </div>
                                @else
                                    <p class="owner-capacity-unset">برای این کلاس هنوز ظرفیتی تعریف نشده است؛ درصد پرشدگی محاسبه نمی‌شود.</p>
                                @endif

                                @if($classroom->live_now)
                                    <div class="owner-classroom-live">
                                        <span aria-hidden="true"></span>
                                        <strong>در حال اجرا</strong>
                                        <span>{{ $classroom->live_now->title }}</span>
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="owner-dashboard-empty" role="status">
                        <span class="owner-dashboard-empty-mark" aria-hidden="true">ک</span>
                        <div>
                            <strong>هنوز کلاس فعالی ثبت نشده است.</strong>
                            <p>پس از ثبت کلاس، ظرفیت و وضعیت آن در همین بخش نمایش داده می‌شود.</p>
                        </div>
                    </div>
                @endif
            </section>
        @endif

        @if($canViewReports || $canViewMeetings)
        {{-- =========================================================
            TEACHERS + UPCOMING LIVE CLASSES
        ========================================================== --}}
        <div class="owner-grid">

@if($canViewReports)
            {{-- Teachers --}}
            <section class="dashboard-panel owner-panel">

                <div class="owner-panel-head">
                    <div>
                        <h2>برآیند مدرس‌ها</h2>

                        <p>
                            دوره، دانش‌آموز، پیشرفت و کارهای معوق
                        </p>
                    </div>

                    <a
                        href="{{ route('owner.reports.index') }}"
                        class="owner-link"
                    >
                        جزئیات ←
                    </a>
                </div>

                <div class="owner-teacher-list">

                    @forelse($teacherReports as $teacher)
                        <article class="owner-teacher-row">

                            <div class="owner-avatar">
                                {{ mb_substr($teacher->name, 0, 1) }}
                            </div>

                            <div class="min-w-0">
                                <div class="owner-row-title">
                                    {{ $teacher->name }}
                                </div>

                                <div class="owner-row-meta">
                                    {{ \App\Support\PersianUi::digits($teacher->course_count) }} دوره
                                    ·
                                    {{ \App\Support\PersianUi::digits($teacher->student_count) }} دانش‌آموز
                                    ·
                                    {{ \App\Support\PersianUi::digits($teacher->pending_reviews) }} بررسی معوق
                                </div>

                                <div class="owner-progress">
                                    <span
                                        style="width: {{ min(100, max(0, (float) $teacher->progress_average)) }}%"
                                    ></span>
                                </div>
                            </div>

                            <div class="owner-teacher-metric">
                                <strong>
                                    {{ \App\Support\PersianUi::digits($teacher->progress_average) }}٪
                                </strong>

                                <small>
                                    پیشرفت میانگین
                                </small>
                            </div>

                        </article>
                    @empty
                        <div class="owner-empty">
                            هنوز مدرس فعالی ثبت نشده است.
                        </div>
                    @endforelse

                </div>
            </section>


@endif

@if($canViewMeetings)
            {{-- Upcoming live classes --}}
            <section class="dashboard-panel owner-panel">

                <div class="owner-panel-head">
                    <div>
                        <h2>جلسات آنلاین آینده</h2>

                        <p>
                            هفت روز آینده
                        </p>
                    </div>
                </div>

                <div class="owner-live-list">

                    @forelse($upcomingLiveClasses as $item)
                        <article class="owner-live-row">

                            <div class="owner-pill shrink-0">
                                {{ \App\Support\PersianUi::date(\Illuminate\Support\Carbon::parse($item->scheduled_at)) }}
                                ·
                                {{ \App\Support\PersianUi::time(\Illuminate\Support\Carbon::parse($item->scheduled_at)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="owner-row-title truncate">
                                    {{ $item->title }}
                                </div>

                                <div class="owner-row-meta truncate">
                                    {{ $item->course?->title }}
                                    ·
                                    {{ $item->teacher?->name }}
                                </div>
                            </div>

                            <span class="owner-pill success shrink-0">
                                {{ $item->classroom?->title ?? 'آنلاین' }}
                            </span>

                        </article>
                    @empty
                        <div class="owner-empty">
                            جلسه آنلاینی در هفت روز آینده ثبت نشده است.
                        </div>
                    @endforelse

                </div>
            </section>
@endif


        </div>


@endif
@if($canViewCourses)
        {{-- =========================================================
            RECENT COURSES
        ========================================================== --}}
        <section class="dashboard-panel owner-panel">

            <div class="owner-panel-head">
                <div>
                    <h2>آخرین دوره‌ها</h2>

                    <p>
                        انتشار و دانش‌آموز فعال
                    </p>
                </div>

                <a
                    href="{{ route('owner.courses.index') }}"
                    class="owner-link"
                >
                    همه دوره‌ها ←
                </a>
            </div>

            <div class="owner-course-list">

                @forelse($recentCourses as $course)
                    <article class="owner-course-row">

                        <div class="min-w-0">
                            <div class="owner-row-title truncate">
                                {{ $course->title }}
                            </div>

                            <div class="owner-row-meta truncate">
                                {{ $course->academy?->name ?? 'بدون آموزشگاه' }}
                                ·
                                {{ \App\Support\PersianUi::digits($course->active_students_count) }} دانش‌آموز
                            </div>
                        </div>

                        <span class="owner-pill {{ $course->status === 'published' ? 'success' : '' }}">
                            {{ $course->status === 'published' ? 'منتشرشده' : 'پیش‌نویس' }}
                        </span>

                        <a
                            href="{{ route('owner.courses.show', $course) }}"
                            class="owner-pill shrink-0"
                        >
                            بازبینی
                        </a>

                    </article>
                @empty
                    <div class="owner-empty">
                        دوره‌ای هنوز ثبت نشده است.
                    </div>
                @endforelse

            </div>
        </section>
@endif


    </div>
@endsection
