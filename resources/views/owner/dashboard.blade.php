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
        $canViewMessages = (bool) $ownerUser?->hasPermission('parents.view') && (bool) $firstAcademy;
        $canManageContent = (bool) $ownerUser?->hasPermission('content.manage') && (bool) $firstAcademy;
        $canViewProducts = (bool) $ownerUser?->hasPermission('products.view') && (bool) $firstAcademy;
        $canViewOrders = (bool) $ownerUser?->hasPermission('orders.view') && (bool) $firstAcademy;
        $canViewLegalDocuments = (bool) $ownerUser?->hasPermission('legal.manage');
        $canViewExams = (bool) $ownerUser?->hasPermission('exams.view') && (bool) $firstAcademy;
        $canViewTeacherPayments = (bool) $ownerUser?->hasPermission('teacher_payments.view') && (bool) $firstAcademy;
        $canViewRecordings = (bool) $ownerUser?->hasPermission('live_classes.view') && (bool) $firstAcademy;
        $canChooseAcademy = $canViewAcademy || $canViewClassrooms || $canViewAttendance
            || $canViewMeetings || $canViewAssignments || $canViewMessages || $canManageContent
            || $canViewProducts || $canViewOrders || $canViewExams || $canViewTeacherPayments
            || $canViewRecordings;
        $hasMultipleAcademies = $academies->count() > 1;
        $ownerAcademyHref = fn (string $routeName): string => $hasMultipleAcademies
            ? '#owner-academies'
            : route($routeName, $firstAcademy);

        // Suppress numbers for workflows the account cannot open.
        $visiblePendingReviews = $canViewAssignments ? max(0, (int) ($metrics['pendingReviews'] ?? 0)) : 0;
        $visibleCapacityAlerts = $canViewClassrooms ? max(0, (int) ($metrics['capacityAlerts'] ?? 0)) : 0;
        $visibleClassroomsWithoutTeacher = $canViewClassrooms ? max(0, (int) ($metrics['classroomsWithoutTeacher'] ?? 0)) : 0;
        $attentionCount = $visiblePendingReviews + $visibleCapacityAlerts + $visibleClassroomsWithoutTeacher;
        $sales = $canViewFinance ? (float) ($metrics['sales'] ?? 0) : 0;
        $attendanceRate = $canViewAttendance ? ($metrics['todayAttendanceRate'] ?? null) : null;

        $focusRoute = null;
        if ($visibleClassroomsWithoutTeacher > 0 && $firstAcademy) {
            $focusRoute = $ownerAcademyHref('owner.classrooms.index');
        } elseif ($visibleCapacityAlerts > 0 && $firstAcademy) {
            $focusRoute = $ownerAcademyHref('owner.classrooms.index');
        } elseif ($visiblePendingReviews > 0 && $firstAcademy) {
            $focusRoute = $ownerAcademyHref('owner.assignments.index');
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

                <div class="owner-hero-actions mt-6">
                    @if($canViewClassrooms)
                        <a href="{{ $ownerAcademyHref('owner.classrooms.index') }}" class="owner-btn">مدیریت کلاس‌ها</a>
                    @endif
                    @if($canViewAcademy && $firstAcademy)
                        <a href="{{ $ownerAcademyHref('owner.people.index') }}" class="owner-btn ghost">مدیریت اعضا</a>
                    @endif
                    @if($canManageContent)
                        <a href="{{ $ownerAcademyHref('owner.resources.index') }}" class="owner-btn ghost">جزوه‌ها و فایل‌ها</a>
                        <a href="{{ $ownerAcademyHref('owner.showcase.index') }}" class="owner-btn ghost">افتخارآفرینان و رضایتمندی</a>
                    @endif
                    @if($canViewProducts)
                        <a href="{{ $ownerAcademyHref('owner.products.index') }}" class="owner-btn ghost">کتابخانه، جزوه و آزمون</a>
                    @endif
                    @if($canViewOrders)
                        <a href="{{ $ownerAcademyHref('owner.orders.index') }}" class="owner-btn ghost">سفارش‌ها و رسیدها</a>
                    @endif
                    @if($canViewLegalDocuments)
                        <a href="{{ route('owner.legal-documents.index') }}" class="owner-btn ghost">اسناد حقوقی و کپی‌رایت</a>
                    @endif
                    @if($canViewReports)
                        <a href="{{ route('owner.reports.index') }}" class="owner-btn ghost">گزارش‌ها</a>
                    @endif
                </div>
            </div>

            @if($canViewFinance)
                <a class="owner-hero-art owner-hero-finance-link" href="{{ route('owner.finance.index') }}" aria-label="مشاهده دفتر مالی">
                    <div class="owner-orb one"></div>
                    <div class="owner-orb two"></div>
                    <div class="owner-hero-stat">
                        <small>درآمد خالص ثبت‌شده</small>
                        <strong>{{ \App\Support\PersianUi::number($sales) }}</strong>
                        <span>تومان · از دفتر پرداخت‌های تکمیل‌شده</span>
                        <span class="owner-finance-link-label">مشاهده دفتر مالی ←</span>
                    </div>
                </a>
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

        {{-- Permission-aware shortcuts: each link follows its destination GET permission. --}}
        @if($canViewAcademy || $canViewAttendance || $canViewMeetings || $canViewAssignments || $canManageContent || $canViewProducts || $canViewMessages || $canViewExams || $canViewRecordings || $canViewTeacherPayments || $canViewReports || $canViewOrders || $canViewLegalDocuments)
            <section aria-labelledby="owner-quick-actions-title" class="owner-quick-actions">
                <h2 id="owner-quick-actions-title" class="sr-only">دسترسی سریع کارتابل</h2>
                @if($canOpenPeople)
                    <a href="{{ $ownerAcademyHref('owner.people.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>ثبت‌نام و مدیریت اعضا</strong><small>دانش‌آموز، ولی و مدرس</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewAttendance)
                    <a href="{{ $ownerAcademyHref('owner.attendance.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>سوابق حضور و غیاب</strong><small>جست‌وجو در کلاس‌های آموزشگاه</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewMeetings)
                    <a href="{{ $ownerAcademyHref('owner.meetings.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>هماهنگی با اولیا</strong><small>جلسات و اطلاع‌رسانی</small></span><span aria-hidden="true">←</span>
                    </a>
                    <a href="{{ $ownerAcademyHref('owner.schedules.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>لینک کلاس‌های آنلاین</strong><small>زمان‌بندی و لینک ورود هر کلاس</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewRecordings)
                    <a href="{{ $ownerAcademyHref('owner.recordings.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>آرشیو ضبط کلاس‌ها</strong><small>انتشار و دسترسی فایل‌ها</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewAssignments)
                    <a href="{{ $ownerAcademyHref('owner.assignments.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>تکالیف و تصحیح</strong><small>بررسی پاسخ‌های ارسال‌شده</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canManageContent)
                    <a href="{{ $ownerAcademyHref('owner.resources.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>ویدئو و منابع آموزشی</strong><small>فایل، جزوه و محتوای دوره</small></span><span aria-hidden="true">←</span>
                    </a>
                    <a href="{{ $ownerAcademyHref('owner.showcase.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>افتخارآفرینان و رضایتمندی</strong><small>مدیریت محتوای نمایشی آموزشگاه</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewProducts)
                    <a href="{{ $ownerAcademyHref('owner.products.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>محصولات و کتاب‌ها</strong><small>مدیریت منابع قابل فروش</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewOrders)
                    <a href="{{ $ownerAcademyHref('owner.orders.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>سفارش‌ها و رسیدها</strong><small>بررسی سفارش و مدارک پرداخت</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewMessages)
                    <a href="{{ $ownerAcademyHref('owner.messages.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>پیام‌های اولیا</strong><small>پیگیری و پاسخ‌گویی</small></span><span aria-hidden="true">←</span>
                    </a>
                    <a href="{{ $ownerAcademyHref('owner.counseling.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>جلسات مشاوره</strong><small>بررسی درخواست‌ها</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewExams)
                    <a href="{{ $ownerAcademyHref('owner.exams.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>آزمون‌ها و نتایج</strong><small>انتشار و تلاش‌های دانش‌آموزان</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewTeacherPayments)
                    <a href="{{ $ownerAcademyHref('owner.teacher-payments.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>حقوق مدرس‌ها</strong><small>ثبت سند و وضعیت پرداخت</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewReports)
                    <a href="{{ route('owner.reports.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>گزارش‌های آموزشی</strong><small>عملکرد دوره، کلاس، مدرس و تکلیف</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
                @if($canViewLegalDocuments)
                    <a href="{{ route('owner.legal-documents.index') }}" class="dashboard-panel owner-quick-action">
                        <span><strong>اسناد حقوقی و کپی‌رایت</strong><small>مستندات و ثبت اسناد</small></span><span aria-hidden="true">←</span>
                    </a>
                @endif
            </section>
        @endif

        {{-- The KPI band links to the matching accessible workspace. --}}
        <section class="owner-stats" aria-label="شاخص‌های کارتابل">
            @if($canViewClassrooms)
                <a class="owner-stat-card is-actionable" href="{{ $ownerAcademyHref('owner.classrooms.index') }}" aria-label="کلاس‌های فعال">
                    <span class="owner-stat-label">کلاس‌های فعال <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['classrooms'] ?? 0) }}</strong>
                    <small>{{ \App\Support\PersianUi::digits($metrics['liveNow'] ?? 0) }} کلاس آنلاین در حال اجرا</small>
                </a>
            @endif
            @if($canOpenPeople)
                <a class="owner-stat-card is-actionable" href="{{ $ownerAcademyHref('owner.people.index') }}" aria-label="دانش‌آموزان فعال">
                    <span class="owner-stat-label">دانش‌آموز فعال <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['students'] ?? 0) }}</strong>
                    <small>فهرست اعضای آموزشگاه</small>
                </a>
                <a class="owner-stat-card is-actionable" href="{{ $ownerAcademyHref('owner.people.index') }}" aria-label="مدرس‌های فعال">
                    <span class="owner-stat-label">مدرس فعال <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['teachers'] ?? 0) }}</strong>
                    <small>مدرس‌های آموزشگاه</small>
                </a>
            @endif
            @if($canViewCourses)
                <a class="owner-stat-card is-actionable" href="{{ route('owner.courses.index') }}" aria-label="دوره‌های منتشرشده">
                    <span class="owner-stat-label">دوره‌های منتشرشده <i aria-hidden="true">↗</i></span>
                    <strong>{{ \App\Support\PersianUi::digits($metrics['published'] ?? 0) }}</strong>
                    <small>از {{ \App\Support\PersianUi::digits($metrics['courses'] ?? 0) }} دوره ثبت‌شده</small>
                </a>
            @endif
            @if($canViewAttendance)
                <a class="owner-stat-card is-actionable" href="{{ $ownerAcademyHref('owner.attendance.index') }}" aria-label="حضور امروز">
                    <span class="owner-stat-label">حضور امروز <i aria-hidden="true">↗</i></span>
                    <strong>{{ $attendanceRate === null ? '—' : \App\Support\PersianUi::digits($attendanceRate) . '٪' }}</strong>
                    <small>{{ $attendanceRate === null ? 'رکورد حضور امروز ثبت نشده' : 'بر اساس رکوردهای امروز' }}</small>
                </a>
            @endif
            @if($canViewClassrooms || $canViewAssignments || $canViewReports)
                @if($focusRoute)
                    <a class="owner-stat-card is-actionable" href="{{ $focusRoute }}" aria-label="موارد نیازمند توجه">
                @else
                    <article class="owner-stat-card">
                @endif
                    <span class="owner-stat-label">نیازمند توجه @if($focusRoute)<i aria-hidden="true">↗</i>@endif</span>
                    <strong>{{ \App\Support\PersianUi::digits($attentionCount) }}</strong>
                    <small>
                        {{ \App\Support\PersianUi::digits($visiblePendingReviews) }} بررسی ·
                        {{ \App\Support\PersianUi::digits($visibleCapacityAlerts) }} کلاس نزدیک ظرفیت ·
                        {{ \App\Support\PersianUi::digits($visibleClassroomsWithoutTeacher) }} کلاس بدون مدرس
                    </small>
                @if($focusRoute)
                    </a>
                @else
                    </article>
                @endif
            @endif
            @if(!$canViewClassrooms && !$canOpenPeople && !$canViewCourses && !$canViewAttendance && !$canViewReports && !$canViewAssignments)
                <div class="dashboard-panel owner-empty" role="status">برای نمایش شاخص‌ها، دسترسی مشاهده به بخش‌های آموزشگاه برای این حساب فعال نیست.</div>
            @endif
        </section>

        @if($canChooseAcademy)
        {{-- =========================================================
            ACADEMIES
        ========================================================== --}}
        <section id="owner-academies">
            <div class="owner-panel-head mb-4">
                <div>
                    <h2>آموزشگاه‌های شما</h2>
                    <p>
                        وضعیت و دسترسی سریع به آموزشگاه‌های فعال
                    </p>
                </div>
            </div>

            @if($academies->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($academies as $academy)
                        <article class="dashboard-panel overflow-hidden p-5">

                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <span class="owner-kicker">
                                        آموزشگاه
                                    </span>

                                    <h3 class="mt-2 truncate text-lg font-black text-slate-950">
                                        {{ $academy->name }}
                                    </h3>

                                    <p class="mt-1 text-xs font-semibold text-slate-500">
                                        {{ $academy->city ?: 'شهر ثبت نشده' }}
                                        <span class="mx-1">·</span>
                                        فعال
                                    </p>
                                </div>

                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--panel-primary-soft)] text-sm font-black text-[var(--panel-primary)]">
                                    {{ mb_substr($academy->name, 0, 1) }}
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-4">
                                @if($canViewAcademy)
                                    <a href="{{ route('owner.people.index', $academy) }}" class="owner-pill justify-center">اعضا</a>
                                @endif
                                @if($canViewClassrooms)
                                    <a href="{{ route('owner.classrooms.index', $academy) }}" class="owner-pill justify-center">کلاس‌ها</a>
                                @endif
                                @if($canViewAttendance)
                                    <a href="{{ route('owner.attendance.index', $academy) }}" class="owner-pill justify-center">حضور و غیاب</a>
                                @endif
                                @if($canViewAssignments)
                                    <a href="{{ route('owner.assignments.index', $academy) }}" class="owner-pill justify-center">تکالیف و تصحیح</a>
                                @endif
                                @if($canViewMeetings)
                                    <a href="{{ route('owner.meetings.index', $academy) }}" class="owner-pill justify-center">هماهنگی جلسات</a>
                                    <a href="{{ route('owner.schedules.index', $academy) }}" class="owner-pill justify-center">لینک کلاس آنلاین</a>
                                    <a href="{{ route('owner.recordings.index', $academy) }}" class="owner-pill justify-center">آرشیو ضبط کلاس</a>
                                @endif
                                @if($canViewMessages)
                                    <a href="{{ route('owner.messages.index', $academy) }}" class="owner-pill justify-center">پیام‌های اولیا</a>
                                    <a href="{{ route('owner.communications.index', $academy) }}" class="owner-pill justify-center">اطلاعیه و پیام</a>
                                    <a href="{{ route('owner.counseling.index', $academy) }}" class="owner-pill justify-center">جلسات مشاوره</a>
                                @endif
                                @if($canViewExams)
                                    <a href="{{ route('owner.exams.index', $academy) }}" class="owner-pill justify-center">آزمون‌ها و نتایج</a>
                                @endif
                                @if($canViewTeacherPayments)
                                    <a href="{{ route('owner.teacher-payments.index', $academy) }}" class="owner-pill justify-center">حقوق مدرس‌ها</a>
                                @endif
                                @if($canManageContent)
                                    <a href="{{ route('owner.resources.index', $academy) }}" class="owner-pill justify-center">ویدئو و منابع</a>
                                    <a href="{{ route('owner.showcase.index', $academy) }}" class="owner-pill justify-center">افتخارآفرینان</a>
                                @endif
                                @if($canViewProducts)
                                    <a href="{{ route('owner.products.index', $academy) }}" class="owner-pill justify-center">محصولات</a>
                                @endif
                                @if($canViewOrders)
                                    <a href="{{ route('owner.orders.index', $academy) }}" class="owner-pill justify-center">سفارش‌ها</a>
                                @endif
                                @if($canViewAcademy)
                                    <a href="{{ route('owner.academy.edit', $academy) }}" class="owner-pill success justify-center">تنظیمات</a>
                                    <a href="{{ route('owner.academy.banners.edit', $academy) }}" class="owner-pill banner justify-center">بنرها</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="dashboard-panel owner-empty p-8 text-center">
                    برای این حساب هنوز آموزشگاه فعالی ثبت نشده است.
                </div>
            @endif
        </section>
        @endif




        {{-- =========================================================
            TODAY'S CLASSROOMS
        ========================================================== --}}
        @if($canViewClassrooms && $classrooms->isNotEmpty())
            <section class="dashboard-panel owner-panel">

                <div class="owner-panel-head">
                    <div>
                        <h2>کلاس‌های امروز</h2>

                        <p>
                            تصویر اجرایی کلاس‌ها از داده‌های واقعی سیستم
                        </p>
                    </div>

                    @if($firstAcademy)
                        <a
                            href="{{ $ownerAcademyHref('owner.classrooms.index') }}"
                            class="owner-link"
                        >
                            همه کلاس‌ها ←
                        </a>
                    @endif
                </div>

                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    @foreach($classrooms as $classroom)

                        <article class="rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:border-slate-200 hover:bg-white">

                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">

                                        <h3 class="truncate text-sm font-black text-slate-950">
                                            {{ $classroom->title }}
                                        </h3>

                                        <span class="rounded-full bg-white px-2.5 py-1 text-[9px] font-black text-slate-600">
                                            {{ $executionLabels[$classroom->execution_status] ?? 'فعال' }}
                                        </span>

                                    </div>

                                    <p class="mt-1 truncate text-[10px] text-slate-500">
                                        {{ $classroom->course?->title }}
                                        <span class="mx-1">·</span>
                                        {{ $classroom->teachers->pluck('name')->join('، ') ?: 'بدون مدرس' }}
                                    </p>
                                </div>

                                <a
                                    href="{{ route('owner.classrooms.show', [$classroom->academy, $classroom]) }}"
                                    class="owner-pill shrink-0"
                                >
                                    جزئیات
                                </a>

                            </div>


                            {{-- Classroom metrics --}}
                            <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ \App\Support\PersianUi::digits($classroom->active_students_count) }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        دانش‌آموز
                                    </span>
                                </div>

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ $classroom->capacity === null ? '∞' : \App\Support\PersianUi::digits($classroom->capacity) }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        ظرفیت
                                    </span>
                                </div>

                                <div class="rounded-xl bg-white p-3 text-center">
                                    <strong class="block text-sm font-black text-slate-950">
                                        {{ $classroom->capacity_remaining === null ? '∞' : \App\Support\PersianUi::digits($classroom->capacity_remaining) }}
                                    </strong>

                                    <span class="text-[9px] text-slate-400">
                                        خالی
                                    </span>
                                </div>

                                @if($canViewAttendance)
                                    <div class="rounded-xl bg-white p-3 text-center">
                                        <strong class="block text-sm font-black text-slate-950">
                                            {{ $classroom->today_attendance_rate === null ? '—' : \App\Support\PersianUi::digits($classroom->today_attendance_rate) . '٪' }}
                                        </strong>
                                        <span class="text-[9px] text-slate-400">حضور امروز</span>
                                    </div>
                                @endif

                            </div>


                            {{-- Occupancy --}}
                            @if($classroom->occupancy_percent !== null)
                                <div class="mt-4">

                                    <div class="mb-2 flex items-center justify-between text-[9px] text-slate-500">
                                        <span>پرشدگی</span>

                                        <strong>
                                            {{ \App\Support\PersianUi::digits($classroom->occupancy_percent) }}٪
                                        </strong>
                                    </div>

                                    <div class="h-1.5 overflow-hidden rounded-full bg-white">
                                        <span
                                            class="block h-full rounded-full bg-[var(--panel-primary)] transition-all duration-500"
                                            style="width: {{ min(100, max(0, $classroom->occupancy_percent)) }}%"
                                        ></span>
                                    </div>

                                </div>
                            @endif


                            {{-- Live class --}}
                            @if($classroom->live_now)
                                <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5">
                                    <div class="flex items-center justify-between gap-3">

                                        <span class="flex shrink-0 items-center gap-2 text-[9px] font-black text-emerald-700">
                                            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                                            در حال اجرا
                                        </span>

                                        <span class="truncate text-[10px] font-bold text-emerald-900">
                                            {{ $classroom->live_now->title }}
                                        </span>

                                    </div>
                                </div>
                            @endif

                        </article>
                    @endforeach
                </div>

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




        {{-- Footer shortcuts follow the same destination permissions as the sidebar. --}}
        @if($canViewClassrooms || $canViewAcademy || $canViewReports)
            <section class="owner-footer-actions" aria-label="دسترسی‌های پایانی">
                @if($canViewClassrooms)
                    <a href="{{ $ownerAcademyHref('owner.classrooms.index') }}" class="dashboard-panel owner-footer-action">
                        <span>عملیات</span><strong>مدیریت کلاس‌ها <i aria-hidden="true">←</i></strong>
                    </a>
                @endif
                @if($canOpenPeople)
                    <a href="{{ $ownerAcademyHref('owner.people.index') }}" class="dashboard-panel owner-footer-action">
                        <span>اعضای آموزشگاه</span><strong>مدیریت دانش‌آموز و مدرس <i aria-hidden="true">←</i></strong>
                    </a>
                @endif
                @if($canViewReports)
                    <a href="{{ route('owner.reports.index') }}" class="dashboard-panel owner-footer-action">
                        <span>تحلیل</span><strong>گزارش‌های آموزشگاه <i aria-hidden="true">←</i></strong>
                    </a>
                @endif
            </section>
        @endif

    </div>
@endsection
