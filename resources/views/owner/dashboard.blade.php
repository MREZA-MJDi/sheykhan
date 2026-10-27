@extends('layouts.owner')

@section('title', 'داشبورد آکادمی شیخان')
@section('header-title', 'داشبورد')
@section('content')
<div class="admin-dashboard" data-admin-dashboard>
@php
    $ownerUser = auth()->user();
    $period = in_array($period ?? 'month', ['today', 'week', 'month'], true) ? ($period ?? 'month') : 'month';
    $periodCaption = $periodCaption ?? 'این ماه';
    $routeAcademyParam = request()->route('academy');
    $currentRouteAcademyId = $routeAcademyParam instanceof \App\Models\Academy
        ? (int) $routeAcademyParam->getKey()
        : (int) ($routeAcademyParam ?? 0);
    $firstAcademy = $academies->first();
    $hasAcademy = (bool) $firstAcademy;
    $hasMultipleAcademies = $academies->count() > 1;
    $canViewAcademy = (bool) $ownerUser?->hasPermission('academy.view');
    $canViewPeople = $canViewAcademy && $hasAcademy;
    $canViewClassrooms = (bool) $ownerUser?->hasPermission('classrooms.view') && $hasAcademy;
    $canViewCourses = (bool) $ownerUser?->hasPermission('courses.view');
    $canViewAttendance = (bool) $ownerUser?->hasPermission('attendance.view') && $hasAcademy;
    $canViewFinance = (bool) $ownerUser?->hasPermission('finance.view');
    $canViewReports = (bool) $ownerUser?->hasPermission('reports.view');
    $canViewAssignments = (bool) $ownerUser?->hasPermission('courses.view') && $hasAcademy;
    $canViewMeetings = (bool) $ownerUser?->hasPermission('live_classes.view') && $hasAcademy;
    $canViewParents = (bool) $ownerUser?->hasPermission('parents.view') && $hasAcademy;
    $canViewExams = (bool) $ownerUser?->hasPermission('exams.view') && $hasAcademy;
    $canViewResources = (bool) $ownerUser?->hasPermission('resources.view') && $hasAcademy;
    $canViewProducts = (bool) $ownerUser?->hasPermission('products.view') && $hasAcademy;
    $canViewOrders = (bool) $ownerUser?->hasPermission('orders.view') && $hasAcademy;
    $canViewTeacherPayments = (bool) $ownerUser?->hasPermission('teacher_payments.view') && $hasAcademy;
    $canViewLegal = (bool) $ownerUser?->hasPermission('legal.manage');
    $metrics = $metrics ?? [];
    $capacity = $capacity ?? [];
    $teacherReports = $teacherReports ?? collect();
    $upcomingLiveClasses = $upcomingLiveClasses ?? collect();
    $recentCourses = $recentCourses ?? collect();
    $recentStudentLogins = $recentStudentLogins ?? collect();

    $students = max(0, (int) data_get($metrics, 'students', 0));
    $teachers = max(0, (int) data_get($metrics, 'teachers', 0));
    $classroomCount = max(0, (int) data_get($metrics, 'classrooms', 0));
    $courseCount = max(0, (int) data_get($metrics, 'courses', 0));
    $publishedCourseCount = max(0, (int) data_get($metrics, 'published', 0));
    $liveNowCount = max(0, (int) data_get($metrics, 'liveNow', 0));
    $pendingReviews = $canViewAssignments ? max(0, (int) data_get($metrics, 'pendingReviews', 0)) : 0;
    $capacityAlerts = $canViewClassrooms ? max(0, (int) data_get($metrics, 'capacityAlerts', 0)) : 0;
    $classroomsWithoutTeacher = $canViewClassrooms ? max(0, (int) data_get($metrics, 'classroomsWithoutTeacher', 0)) : 0;
    $attentionCount = $pendingReviews + $capacityAlerts + $classroomsWithoutTeacher;
    $attendanceRate = $canViewAttendance ? data_get($metrics, 'attendanceRate', data_get($metrics, 'todayAttendanceRate')) : null;
    $sales = $canViewFinance ? (float) data_get($metrics, 'sales', 0) : 0;
    $capacityTotal = max(0, (int) data_get($capacity, 'total', 0));
    $capacityUsed = max(0, (int) data_get($capacity, 'used', 0));
    $capacityRemaining = max(0, (int) data_get($capacity, 'remaining', 0));
    $capacityRate = data_get($capacity, 'rate');
    $capacityRate = $capacityRate === null ? null : min(100, max(0, (float) $capacityRate));
    $publishedRate = $courseCount > 0 ? min(100, (int) round(($publishedCourseCount / $courseCount) * 100)) : null;
    $nextLiveClass = $upcomingLiveClasses->first();
    $nextLiveTarget = $nextLiveClass?->scheduled_at?->timestamp;

@endphp
        <section class="ad-dashboard__hero ad-dashboard__span-full">
            <div>
                <span class="ad-dashboard__eyebrow">کارتابل مدیر آموزشگاه</span>
                <h1>{{ $firstAcademy?->name ?? 'آکادمی شیخان' }}</h1>
                <p class="ad-dashboard__intro">نمای زنده و مبتنی بر دیتابیس از عملیات آموزشی و موارد نیازمند پیگیری.</p>
            </div>
            <div class="ad-dashboard__actions">
                @if($canViewPeople && $firstAcademy)
                    <a class="ad-dashboard__button ad-dashboard__button--small" href="{{ route('owner.people.index', $firstAcademy) }}"><svg class="ic"><use href="#i-users"/></svg> مدیریت اعضا</a>
                @endif
                @if($canViewClassrooms && $firstAcademy)
                    <a class="ad-dashboard__button ad-dashboard__button--secondary" href="{{ route('owner.classrooms.index', $firstAcademy) }}">مدیریت کلاس‌ها ←</a>
                @endif
            </div>
        </section>

        @if($canViewPeople)
        <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#22d3ee" aria-label="دانش‌آموزان فعال">
            <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-users"/></svg>دانش‌آموزان فعال</div>
            <b>{{ \App\Support\PersianUi::digits($students) }}</b>
            <div class="ad-dashboard__caption">عضویت فعال و حساب فعال در آموزشگاه‌های شما</div>
            @if($canViewPeople && $firstAcademy)<div class="ad-dashboard__metric-foot"><span>مشاهده اعضا</span><a class="ad-dashboard__link" href="{{ route('owner.people.index', $firstAcademy) }}">جزئیات ←</a></div>@endif
        </section>

        <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#8b3dff" aria-label="مدرس‌های فعال">
            <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-users"/></svg>مدرس‌های فعال</div>
            <b>{{ \App\Support\PersianUi::digits($teachers) }}</b>
            <div class="ad-dashboard__caption">مدرس‌های فعال عضو آموزشگاه</div>
            @if($canViewPeople && $firstAcademy)<div class="ad-dashboard__metric-foot"><span>مدیریت مدرس‌ها</span><a class="ad-dashboard__link" href="{{ route('owner.people.index', $firstAcademy) }}">جزئیات ←</a></div>@endif
        </section>
        @endif

        @if($canViewClassrooms)
            <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#ff1f6b" aria-label="کلاس‌های فعال">
                <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-calendar"/></svg>کلاس‌های فعال</div>
                <b>{{ \App\Support\PersianUi::digits($classroomCount) }}</b>
                <div class="ad-dashboard__caption">{{ \App\Support\PersianUi::digits($liveNowCount) }} جلسه آنلاین همین حالا در حال اجرا</div>
                @if($firstAcademy)<div class="ad-dashboard__metric-foot"><span>فهرست کلاس‌ها</span><a class="ad-dashboard__link" href="{{ route('owner.classrooms.index', $firstAcademy) }}">جزئیات ←</a></div>@endif
            </section>
        @endif

        @if($canViewCourses)
            <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#2ee6a6" aria-label="دوره‌های منتشرشده">
                <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-book"/></svg>دوره‌های منتشرشده</div>
                <b>{{ \App\Support\PersianUi::digits($publishedCourseCount) }}</b>
                <div class="ad-dashboard__caption">از {{ \App\Support\PersianUi::digits($courseCount) }} دوره ثبت‌شده</div>
                @if($publishedRate !== null)<div class="ad-dashboard__metric-foot"><span>سهم انتشار</span><strong>{{ \App\Support\PersianUi::digits($publishedRate) }}٪</strong></div>@endif
                <div class="ad-dashboard__track"><span class="ad-dashboard__fill" style="--c:#2ee6a6;width:{{ $publishedRate ?? 0 }}%"></span></div>
                <div class="ad-dashboard__metric-foot"><span>مدیریت دوره‌ها</span><a class="ad-dashboard__link" href="{{ route('owner.courses.index') }}">جزئیات ←</a></div>
            </section>
        @endif

        @if($canViewAttendance)
            <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#22d3ee" aria-label="نرخ حضور {{ $periodCaption }}">
                <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-checksq"/></svg>حضور {{ $periodCaption }}</div>
                <b>{{ $attendanceRate === null ? '—' : \App\Support\PersianUi::digits($attendanceRate) }}{{ $attendanceRate === null ? '' : '٪' }}</b>
                <div class="ad-dashboard__caption">{{ $attendanceRate === null ? ('در ' . $periodCaption . ' رکورد حضور و غیاب ثبت نشده') : ('درصد حضور و تأخیر از کل رکوردهای معتبر ' . $periodCaption) }}</div>
                @if($attendanceRate !== null)<div class="ad-dashboard__track"><span class="ad-dashboard__fill" style="--c:#22d3ee;width:{{ min(100,max(0,(float)$attendanceRate)) }}%"></span></div>@endif
                @if($firstAcademy)<div class="ad-dashboard__metric-foot"><span>حضور و غیاب</span><a class="ad-dashboard__link" href="{{ route('owner.attendance.index', $firstAcademy) }}">جزئیات ←</a></div>@endif
            </section>
        @endif

        @if($canViewFinance)
            <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#ff9a1f" aria-label="خالص تراکنش‌های تکمیل‌شده در {{ $periodCaption }}">
                <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-wallet"/></svg>خالص تراکنش‌های تکمیل‌شده در {{ $periodCaption }}</div>
                <b>{{ \App\Support\PersianUi::number($sales) }}</b>
                <div class="ad-dashboard__caption">پرداخت‌های ثبت‌شده منهای بازپرداخت‌های تکمیل‌شده</div>
                <div class="ad-dashboard__metric-foot"><span>مبالغ بر حسب تومان؛ تراکنش‌های ریالی وارد این جمع نشده‌اند</span><a class="ad-dashboard__link" href="{{ route('owner.finance.index') }}">جزئیات ←</a></div>
            </section>
        @endif

        @if($canViewAssignments || $canViewClassrooms)
            <section class="ad-dashboard__panel ad-dashboard__metric-card ad-dashboard__span-quarter" style="--c:#ff1f6b" aria-label="موارد نیازمند توجه">
                <div class="ad-dashboard__metric-label"><svg class="ic"><use href="#i-bell"/></svg>نیازمند پیگیری</div>
                <b>{{ \App\Support\PersianUi::digits($attentionCount) }}</b>
                <div class="ad-dashboard__caption">جمع سه هشدار مستقل؛ هر مورد با کوئری مربوط خودش محاسبه شده است.</div>
                <div class="ad-dashboard__metric-foot"><span>{{ \App\Support\PersianUi::digits($pendingReviews) }} بررسی معوق</span><span>{{ \App\Support\PersianUi::digits($capacityAlerts + $classroomsWithoutTeacher) }} هشدار کلاس</span></div>
                @if($pendingReviews > 0 && $firstAcademy && $canViewAssignments)<a class="ad-dashboard__button ad-dashboard__button--small" href="{{ route('owner.assignments.index', $firstAcademy) }}">بررسی تکالیف ←</a>
                @elseif(($capacityAlerts + $classroomsWithoutTeacher)>0 && $firstAcademy && $canViewClassrooms)<a class="ad-dashboard__button ad-dashboard__button--small" href="{{ route('owner.classrooms.index', $firstAcademy) }}">بررسی کلاس‌ها ←</a>
                @else<span class="ad-dashboard__pill" style="--c:#2ee6a6">بدون مورد فوری</span>@endif
            </section>
        @endif

        @if($canViewClassrooms)
            <section class="ad-dashboard__panel ad-dashboard__span-half" style="--c:#22d3ee" aria-labelledby="capacity-title">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="capacity-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-chart"/></svg></span>وضعیت واقعی ظرفیت کلاس‌ها</h2><p>تجمیع کلاس‌های فعال با ظرفیت تعریف‌شده و دانش‌آموزان فعال ثبت‌شده در هر کلاس.</p></div>
                    @if($capacityRate !== null)<span class="ad-dashboard__pill" style="--c:#22d3ee">{{ \App\Support\PersianUi::digits($capacityRate) }}٪</span>@endif
                </div>
                <div class="ad-dashboard__metric-bars">
                    <div>
                        <div class="ad-dashboard__bar-label"><span>ظرفیت استفاده‌شده</span><strong>{{ \App\Support\PersianUi::digits($capacityUsed) }} از {{ \App\Support\PersianUi::digits($capacityTotal) }}</strong></div>
                        <div class="ad-dashboard__track"><span class="ad-dashboard__fill" style="--c:#22d3ee;width:{{ $capacityRate ?? 0 }}%"></span></div>
                    </div>
                    <div>
                        <div class="ad-dashboard__bar-label"><span>ظرفیت خالی</span><strong>{{ \App\Support\PersianUi::digits($capacityRemaining) }}</strong></div>
                        <div class="ad-dashboard__track"><span class="ad-dashboard__fill" style="--c:#8b3dff;width:{{ $capacityTotal > 0 ? min(100,($capacityRemaining/$capacityTotal)*100) : 0 }}%"></span></div>
                    </div>
                </div>
                <div class="ad-dashboard__meta-row"><small>{{ \App\Support\PersianUi::digits((int)data_get($capacity,'configured_classrooms',0)) }} کلاس دارای ظرفیت</small>@if($firstAcademy)<a class="ad-dashboard__link" href="{{ route('owner.classrooms.index', $firstAcademy) }}">مدیریت کلاس‌ها ←</a>@endif</div>
                @if($capacityTotal === 0)<div class="ad-dashboard__empty">برای محاسبه نرخ ظرفیت، ظرفیت حداقل یک کلاس فعال باید در دیتابیس تعیین شده باشد.</div>@endif
            </section>
        @endif

        @if($canViewCourses)
            <section class="ad-dashboard__panel ad-dashboard__span-half" style="--c:#8b3dff" aria-labelledby="course-publish-title">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="course-publish-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-book"/></svg></span>وضعیت انتشار دوره‌ها</h2><p>نسبت دوره‌های منتشرشده به کل دوره‌های ثبت‌شده در آموزشگاه‌های مجاز این حساب.</p></div>
                    <a class="ad-dashboard__link" href="{{ route('owner.courses.index') }}">همه دوره‌ها ←</a>
                </div>
                <div class="ad-dashboard__donut-row">
                    <svg viewBox="0 0 120 120" role="img" aria-label="سهم دوره‌های منتشرشده">
                        <circle cx="60" cy="60" r="45" fill="none" stroke="#0a0e27" stroke-width="13"/>
                        @if($publishedRate !== null)<circle cx="60" cy="60" r="45" fill="none" stroke="#8b3dff" stroke-width="13" stroke-linecap="round" stroke-dasharray="{{ 2 * pi() * 45 * $publishedRate / 100 }} {{ 2 * pi() * 45 * (1 - $publishedRate / 100) }}" transform="rotate(-90 60 60)"/>@endif
                        <text x="60" y="57" text-anchor="middle" fill="#e8ecff" font-size="18" font-weight="800">{{ $publishedRate === null ? '—' : \App\Support\PersianUi::digits($publishedRate) . '٪' }}</text>
                        <text x="60" y="75" text-anchor="middle" fill="#8d96c9" font-size="9">انتشار</text>
                    </svg>
                    <div class="ad-dashboard__grow">
                        <b>{{ \App\Support\PersianUi::digits($publishedCourseCount) }} دوره منتشرشده</b>
                        <small>از {{ \App\Support\PersianUi::digits($courseCount) }} رکورد دوره</small>
                        <div class="ad-dashboard__muted-note">پیش‌نویس یا سایر وضعیت‌ها: {{ \App\Support\PersianUi::digits(max(0,$courseCount-$publishedCourseCount)) }}</div>
                    </div>
                </div>
                @if($courseCount === 0)<div class="ad-dashboard__empty">هنوز دوره‌ای در دیتابیس ثبت نشده است.</div>@endif
            </section>
        @endif

        @if($canViewPeople)
            <section class="ad-dashboard__panel ad-dashboard__span-full" aria-labelledby="login-title" style="--c:#22d3ee">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="login-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-users"/></svg></span>ورود اخیر دانش‌آموزان</h2><p>آخرین رویدادهای واقعی ورود برای اعضای فعال همین آکادمی‌ها؛ این فهرست از cache شاخص‌ها خوانده نمی‌شود.</p></div>
                    @if($firstAcademy)<a class="ad-dashboard__link" href="{{ route('owner.people.index', $firstAcademy) }}">مدیریت اعضا ←</a>@endif
                </div>
                @forelse($recentStudentLogins as $login)
                    <a class="ad-dashboard__row" href="{{ $firstAcademy ? route('owner.people.index', $firstAcademy) : route('owner.dashboard') }}" style="--c:#22d3ee">
                        <i class="ad-dashboard__tile"><svg class="ic"><use href="#i-users"/></svg></i>
                        <span class="ad-dashboard__grow"><b>{{ $login->subject?->name ?? 'حساب حذف‌شده' }}</b><small>{{ $login->subject?->email ?? 'بدون ایمیل' }}</small></span>
                        <span class="ad-dashboard__row-value">{{ \App\Support\PersianUi::dateTime($login->created_at) }}</span>
                        <svg class="ic"><use href="#i-arr"/></svg>
                    </a>
                @empty
                    <div class="ad-dashboard__empty">در گزارش فعالیت‌ها، ورود ثبت‌شده‌ای برای دانش‌آموزان فعال این آموزشگاه‌ها یافت نشد.</div>
                @endforelse
            </section>
        @endif

        @if($canViewClassrooms)
            <section class="ad-dashboard__panel ad-dashboard__span-full" aria-labelledby="attention-title" style="--c:#ff9a1f">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="attention-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-bell"/></svg></span>هشدارهای قابل اقدام</h2><p>هر ردیف فقط وقتی نمایش داده می‌شود که شرط متناظر در دیتابیس برقرار باشد.</p></div>
                </div>
                <div class="ad-dashboard__alert-grid">
                    @if($classroomsWithoutTeacher > 0)
                        <a class="ad-dashboard__alert-card" href="{{ $firstAcademy ? route('owner.classrooms.index', $firstAcademy) : route('owner.dashboard') }}" style="--c:#ff9a1f">
                            <div class="ad-dashboard__alert-icon" style="background:linear-gradient(135deg,rgba(255,154,31,.35),rgba(255,31,107,.1));display:grid;place-items:center"><svg class="ic" style="width:30px;height:30px"><use href="#i-users"/></svg></div>
                            <b>{{ \App\Support\PersianUi::digits($classroomsWithoutTeacher) }} کلاس بدون مدرس</b>
                            <small>کلاس فعال بدون مدرس عضو و فعال در همان آکادمی.</small>
                            <div class="ad-dashboard__meta-row"><span class="ad-dashboard__pill" style="--c:#ff9a1f">نیازمند پیگیری</span><span class="ad-dashboard__link">بازبینی ←</span></div>
                        </a>
                    @endif
                    @if($capacityAlerts > 0)
                        <a class="ad-dashboard__alert-card" href="{{ $firstAcademy ? route('owner.classrooms.index', $firstAcademy) : route('owner.dashboard') }}" style="--c:#ff1f6b">
                            <div class="ad-dashboard__alert-icon" style="background:linear-gradient(135deg,rgba(255,31,107,.3),rgba(139,61,255,.12));display:grid;place-items:center"><svg class="ic" style="width:30px;height:30px"><use href="#i-calendar"/></svg></div>
                            <b>{{ \App\Support\PersianUi::digits($capacityAlerts) }} کلاس نزدیک ظرفیت</b>
                            <small>کلاس فعال با ظرفیت مثبت که ۹۰٪ یا بیشتر پر شده است.</small>
                            <div class="ad-dashboard__meta-row"><span class="ad-dashboard__pill" style="--c:#ff1f6b">ظرفیت بالا</span><span class="ad-dashboard__link">بازبینی ←</span></div>
                        </a>
                    @endif
                    @if($pendingReviews > 0 && $canViewAssignments)
                        <a class="ad-dashboard__alert-card" href="{{ $firstAcademy ? route('owner.assignments.index', $firstAcademy) : route('owner.dashboard') }}" style="--c:#22d3ee">
                            <div class="ad-dashboard__alert-icon" style="background:linear-gradient(135deg,rgba(34,211,238,.28),rgba(139,61,255,.12));display:grid;place-items:center"><svg class="ic" style="width:30px;height:30px"><use href="#i-doc"/></svg></div>
                            <b>{{ \App\Support\PersianUi::digits($pendingReviews) }} تکلیف بررسی‌نشده</b>
                            <small>ارسال‌هایی که هنوز زمان تصحیح آن‌ها در دیتابیس ثبت نشده است.</small>
                            <div class="ad-dashboard__meta-row"><span class="ad-dashboard__pill" style="--c:#22d3ee">در انتظار بررسی</span><span class="ad-dashboard__link">بازبینی ←</span></div>
                        </a>
                    @endif
                    @if($classroomsWithoutTeacher === 0 && $capacityAlerts === 0 && $pendingReviews === 0)
                        <div class="ad-dashboard__empty" style="grid-column:1/-1">در داده‌های قابل‌مشاهده‌ی این کارتابل، هشدار بازی وجود ندارد.</div>
                    @endif
                </div>
            </section>
        @endif

        @if($canViewReports)
            <section class="ad-dashboard__panel ad-dashboard__span-full" aria-labelledby="teacher-report-title" style="--c:#8b3dff">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="teacher-report-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-chart"/></svg></span>عملکرد مدرس‌ها</h2><p>حداکثر ۸ مدرس بر اساس تعداد دانش‌آموزان فعال؛ میانگین پیشرفت و بررسی‌های معوق از محاسبات بک‌اند.</p></div>
                    <a class="ad-dashboard__link" href="{{ route('owner.reports.index') }}">گزارش کامل ←</a>
                </div>
                <div class="ad-dashboard__table-scroll">
                    <table>
                        <thead><tr><th>مدرس</th><th>دوره</th><th>دانش‌آموز فعال</th><th>پیشرفت میانگین</th><th>بررسی معوق</th>@if($canViewFinance)<th>خالص تراکنش (تومان)</th>@endif</tr></thead>
                        <tbody>
                        @forelse($teacherReports as $teacher)
                            <tr>
                                <td><span class="ad-dashboard__name-cell"><i class="ad-dashboard__avatar">{{ mb_substr((string)$teacher->name,0,1) }}</i><span>{{ $teacher->name }}</span></span></td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->course_count) }}</td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->student_count) }}</td>
                                <td>
                                    <span class="ad-dashboard__row-value">{{ \App\Support\PersianUi::digits(round((float)$teacher->progress_average,1)) }}٪</span>
                                    <div class="ad-dashboard__track"><span class="ad-dashboard__fill" style="--c:#8b3dff;width:{{ min(100,max(0,(float)$teacher->progress_average)) }}%"></span></div>
                                </td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->pending_reviews) }}</td>
                                @if($canViewFinance)<td>{{ \App\Support\PersianUi::number($teacher->sales) }}</td>@endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canViewFinance ? 6 : 5 }}"><div class="ad-dashboard__empty">هنوز داده‌ای برای گزارش مدرس‌ها موجود نیست.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if($canViewMeetings)
            <section class="ad-dashboard__panel ad-dashboard__span-half" aria-labelledby="live-title" style="--c:#ff1f6b">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="live-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-video"/></svg></span>کلاس‌های آنلاین آینده</h2><p>جلسه‌های زمان‌بندی‌شده در هفت روز آینده؛ مرتب‌شده بر اساس زمان شروع.</p></div>
                    @if($firstAcademy)<a class="ad-dashboard__link" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه کامل ←</a>@endif
                </div>
                @forelse($upcomingLiveClasses as $item)
                    <a class="ad-dashboard__row" href="{{ $item->classroom?->academy_id ? route('owner.schedules.index', ['academy' => $item->classroom->academy_id]) : ($firstAcademy ? route('owner.schedules.index', $firstAcademy) : route('owner.dashboard')) }}" style="--c:{{ $loop->first ? '#ff1f6b' : '#8b3dff' }}">
                        <span class="ad-dashboard__count">{{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                        <span class="ad-dashboard__grow"><b>{{ $item->title }}</b><small>{{ $item->course?->title ?? 'بدون دوره مرتبط' }} · {{ $item->teacher?->name ?? 'مدرس تعیین نشده' }}</small><small>{{ $item->classroom?->title ?? 'کلاس آنلاین' }}</small></span>
                        <span class="ad-dashboard__row-value">{{ \App\Support\PersianUi::dateTime($item->scheduled_at) }}</span>
                    </a>
                @empty
                    <div class="ad-dashboard__empty">در هفت روز آینده جلسه آنلاین زمان‌بندی‌شده‌ای ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if($canViewCourses)
            <section class="ad-dashboard__panel ad-dashboard__span-half" aria-labelledby="recent-courses-title" style="--c:#22d3ee">
                <div class="ad-dashboard__section-head">
                    <div><h2 id="recent-courses-title"><span class="ad-dashboard__section-icon"><svg class="ic"><use href="#i-book"/></svg></span>آخرین دوره‌ها</h2><p>آخرین شش رکورد دوره از دیتابیس؛ برچسب وضعیت از خود رکورد خوانده می‌شود.</p></div>
                    <a class="ad-dashboard__link" href="{{ route('owner.courses.index') }}">همه دوره‌ها ←</a>
                </div>
                @forelse($recentCourses as $course)
                    <div class="ad-dashboard__row" style="--c:{{ $course->status === 'published' ? '#2ee6a6' : '#ff9a1f' }}">
                        <span class="ad-dashboard__tile"><svg class="ic"><use href="#i-book"/></svg></span>
                        <span class="ad-dashboard__grow"><b>{{ $course->title }}</b><small>{{ $course->academy?->name ?? 'بدون آموزشگاه' }} · {{ \App\Support\PersianUi::digits($course->active_students_count) }} دانش‌آموز فعال</small></span>
                        <span class="ad-dashboard__pill" style="--c:{{ $course->status === 'published' ? '#2ee6a6' : '#ff9a1f' }}">{{ $course->status === 'published' ? 'منتشرشده' : ($course->status === 'draft' ? 'پیش‌نویس' : $course->status) }}</span>
                        <a class="ad-dashboard__link" href="{{ route('owner.courses.show', $course) }}">بازبینی</a>
                    </div>
                @empty
                    <div class="ad-dashboard__empty">دوره‌ای در دیتابیس ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if(!$canViewPeople && !$canViewClassrooms && !$canViewCourses && !$canViewAttendance && !$canViewFinance && !$canViewReports && !$canViewMeetings)
            <section class="ad-dashboard__panel ad-dashboard__span-full"><div class="ad-dashboard__empty">برای نمایش شاخص‌ها، دسترسی مشاهده به بخش‌های مجاز این کارتابل برای این حساب فعال نیست.</div></section>
        @endif
</div>
@endsection
