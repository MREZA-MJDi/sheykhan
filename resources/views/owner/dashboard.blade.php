@extends('layouts.owner')

@section('title', 'داشبورد آکادمی شیخان')
@section('header-title', 'داشبورد')
@section('content')
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
    $trends = $trends ?? [];
    $todayLiveClasses = $todayLiveClasses ?? collect();
    $weeklyLabels = data_get($trends, 'weekly.labels', []);
    $weeklyStudents = data_get($trends, 'weekly.students', []);
    $weeklyAttendance = data_get($trends, 'weekly.attendance', []);
    $monthlySalesLabels = data_get($trends, 'monthlySales.labels', []);
    $monthlySalesValues = data_get($trends, 'monthlySales.values', []);

    $sparklinePoints = static function ($values): string {
        $values = collect($values ?? [])->filter(fn ($value) => is_numeric($value))->values();
        if ($values->count() < 2) {
            return '';
        }

        $minimum = (float) $values->min();
        $maximum = (float) $values->max();
        $spread = $maximum - $minimum;
        $lastIndex = max(1, $values->count() - 1);

        return $values->map(function ($value, $index) use ($minimum, $spread, $lastIndex): string {
            $x = number_format(($index / $lastIndex) * 160, 1, '.', '');
            $y = number_format($spread > 0 ? 28 - (((float) $value - $minimum) / $spread * 22) : 17, 1, '.', '');

            return $x . ',' . $y;
        })->implode(' ');
    };

    $chartSegments = static function ($values, int $width = 520, int $height = 130): array {
        $values = array_values(is_array($values) ? $values : []);
        $numeric = array_values(array_filter($values, static fn ($value) => is_numeric($value)));
        if (count($values) < 2 || count($numeric) < 2) {
            return [];
        }

        $minimum = (float) min($numeric);
        $maximum = (float) max($numeric);
        $spread = $maximum - $minimum;
        $lastIndex = max(1, count($values) - 1);
        $segments = [];
        $segment = [];

        foreach ($values as $index => $value) {
            if (!is_numeric($value)) {
                if (count($segment) > 1) {
                    $segments[] = implode(' ', $segment);
                }
                $segment = [];
                continue;
            }

            $x = number_format(($index / $lastIndex) * $width, 1, '.', '');
            $normalized = $spread > 0 ? (((float) $value - $minimum) / $spread) : 0.5;
            $y = number_format($height - 12 - ($normalized * ($height - 24)), 1, '.', '');
            $segment[] = $x . ',' . $y;
        }

        if (count($segment) > 1) {
            $segments[] = implode(' ', $segment);
        }

        return $segments;
    };

    $weeklyStudentSegments = $chartSegments($weeklyStudents);
    $weeklyAttendanceSegments = $chartSegments($weeklyAttendance);
    $monthlySalesMaximum = max(1, ...array_map(static fn ($value) => abs((float) $value), is_array($monthlySalesValues) ? $monthlySalesValues : []));
    $monthlySalesBars = collect($monthlySalesValues)->map(function ($value, $index) use ($monthlySalesLabels, $monthlySalesMaximum): array {
        $value = (float) $value;

        return [
            'label' => $monthlySalesLabels[$index] ?? '',
            'value' => $value,
            'height' => max(8, (int) round(abs($value) / $monthlySalesMaximum * 138)),
            'color' => $value < 0 ? '#ff1f6b' : '#22d3ee',
        ];
    })->all();

    $studentSparkline = $sparklinePoints(data_get($trends, 'students', []));
    $attendanceSparkline = $sparklinePoints(data_get($trends, 'attendance', []));
    $salesSparkline = $sparklinePoints(data_get($trends, 'sales', []));
    $attentionSparkline = $sparklinePoints(data_get($trends, 'pendingReviews', []));

    $attentionRoute = $firstAcademy
        ? ($pendingReviews > 0 && $canViewAssignments
            ? route('owner.assignments.index', $firstAcademy)
            : ($canViewClassrooms
                ? route('owner.classrooms.index', $firstAcademy)
                : route('owner.assignments.index', $firstAcademy)))
        : route('owner.dashboard');

@endphp


        <section class="ph c12">
            <div>
                <span class="chip">کارتابل مدیر آموزشگاه</span>
                <h1>{{ $firstAcademy?->name ?? 'آکادمی شیخان' }}</h1>
                <p class="sub">نمای زنده و مبتنی بر دیتابیس از عملیات آموزشی و موارد نیازمند پیگیری.</p>
            </div>
            <div class="act">
                @if($canViewPeople && $firstAcademy)
                    <a class="go sm" href="{{ route('owner.people.index', $firstAcademy) }}"><svg class="ic"><use href="#i-users"/></svg> مدیریت اعضا</a>
                @endif
                @if($canViewClassrooms && $firstAcademy)
                    <a class="mb" href="{{ route('owner.classrooms.index', $firstAcademy) }}">مدیریت کلاس‌ها ←</a>
                @endif
            </div>
        </section>

        {{-- KPI band: one whole card is one destination, as in dashboard.zip. --}}
        @if($canViewPeople)
            <a class="card kpi c3 kpi-link" href="{{ route('owner.people.index', $firstAcademy) }}" style="--c:#22d3ee" aria-label="مشاهده اعضا؛ {{ \App\Support\PersianUi::digits($students) }} دانش‌آموز فعال">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-users"/></svg>دانش‌آموزان فعال</div>
                <b>{{ \App\Support\PersianUi::digits($students) }} <small>نفر</small></b>
                <div class="dl">عضویت‌های جدید در ۷ روز اخیر</div>
                @if($studentSparkline !== '')
                    <svg class="sp" viewBox="0 0 160 32" preserveAspectRatio="none" aria-hidden="true"><polyline points="{{ $studentSparkline }}"/></svg>
                @endif
                <div class="metric-caption">حساب‌های فعال در آموزشگاه‌های شما</div>
                <div class="metric-foot"><span>مدیریت اعضا</span><span class="table-link" aria-hidden="true">مشاهده ←</span></div>
            </a>
        @endif

        @if($canViewAttendance && $firstAcademy)
            <a class="card kpi c3 kpi-link" href="{{ route('owner.attendance.index', $firstAcademy) }}" style="--c:#2ee6a6" aria-label="مشاهده حضور و غیاب؛ نرخ حضور {{ $periodCaption }}">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-checksq"/></svg>حضور {{ $periodCaption }}</div>
                <b>{{ $attendanceRate === null ? '—' : \App\Support\PersianUi::digits($attendanceRate) }}{{ $attendanceRate === null ? '' : '٪' }}</b>
                <div class="dl">نرخ حضور روزانه در ۷ روز اخیر</div>
                @if($attendanceSparkline !== '')
                    <svg class="sp" viewBox="0 0 160 32" preserveAspectRatio="none" aria-hidden="true"><polyline points="{{ $attendanceSparkline }}"/></svg>
                @endif
                <div class="metric-caption">{{ $attendanceRate === null ? ('در ' . $periodCaption . ' رکورد حضور و غیاب ثبت نشده') : ('حضور و تأخیر از رکوردهای معتبر ' . $periodCaption) }}</div>
                <div class="metric-foot"><span>حضور و غیاب</span><span class="table-link" aria-hidden="true">مشاهده ←</span></div>
            </a>
        @endif

        @if($canViewFinance)
            <a class="card kpi c3 kpi-link" href="{{ route('owner.finance.index') }}" style="--c:#ff9a1f" aria-label="مشاهده مالی؛ خالص تراکنش‌های تکمیل‌شده {{ $periodCaption }}">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-wallet"/></svg>خالص تراکنش‌ها · {{ $periodCaption }}</div>
                <b>{{ \App\Support\PersianUi::number($sales) }} <small>تومان</small></b>
                <div class="dl">خالص روزانه در ۷ روز اخیر</div>
                @if($salesSparkline !== '')
                    <svg class="sp" viewBox="0 0 160 32" preserveAspectRatio="none" aria-hidden="true"><polyline points="{{ $salesSparkline }}"/></svg>
                @endif
                <div class="metric-caption">پرداخت‌های تکمیل‌شده منهای بازپرداخت‌ها</div>
                <div class="metric-foot"><span>مبالغ بر حسب تومان</span><span class="table-link" aria-hidden="true">مشاهده ←</span></div>
            </a>
        @endif

        @if($canViewAssignments || $canViewClassrooms)
            <a class="card kpi c3 kpi-link" href="{{ $attentionRoute }}" style="--c:#ff1f6b" aria-label="مشاهده موارد نیازمند پیگیری؛ {{ \App\Support\PersianUi::digits($attentionCount) }} مورد">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg>نیازمند پیگیری</div>
                <b>{{ \App\Support\PersianUi::digits($attentionCount) }} <small>مورد</small></b>
                <div class="dl">ارسال‌های بررسی‌نشده در ۷ روز اخیر</div>
                @if($attentionSparkline !== '')
                    <svg class="sp" viewBox="0 0 160 32" preserveAspectRatio="none" aria-hidden="true"><polyline points="{{ $attentionSparkline }}"/></svg>
                @endif
                <div class="metric-caption">تکالیف معوق و هشدارهای کلاس؛ فقط موارد واقعی دیتابیس</div>
                <div class="metric-foot"><span>{{ \App\Support\PersianUi::digits($pendingReviews) }} بررسی معوق · {{ \App\Support\PersianUi::digits($capacityAlerts + $classroomsWithoutTeacher) }} هشدار کلاس</span><span class="table-link" aria-hidden="true">بررسی ←</span></div>
            </a>
        @endif

        @if($canViewMeetings)
            <section class="card c8" aria-labelledby="today-schedule-title" style="--c:#22d3ee">
                <div class="card-title">
                    <div>
                        <h2 id="today-schedule-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-calendar"/></svg></span>برنامه امروز</h2>
                        <p>جلسه‌های زمان‌بندی‌شده امروز از داده‌های واقعی کلاس‌ها.</p>
                    </div>
                    @if($firstAcademy)<a class="table-link" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه کامل ←</a>@endif
                </div>
                @forelse($todayLiveClasses as $item)
                    <a class="tl {{ $item->status === 'live' ? 'live' : '' }}" href="{{ $item->classroom?->academy_id ? route('owner.schedules.index', ['academy' => $item->classroom->academy_id]) : route('owner.dashboard') }}" style="--c:{{ $item->status === 'live' ? '#ff1f6b' : '#22d3ee' }}">
                        <b>{{ \App\Support\PersianUi::time($item->scheduled_at) }}</b>
                        <span class="gx"><b>{{ $item->title }}</b><small>{{ $item->course?->title ?? 'بدون دوره مرتبط' }} · {{ $item->teacher?->name ?? 'مدرس تعیین نشده' }}</small></span>
                        @if($item->status === 'live')
                            <span class="pl" style="--c:#ff1f6b">در حال اجرا</span>
                        @else
                            <span class="pl" style="--c:#22d3ee">زمان‌بندی‌شده</span>
                        @endif
                    </a>
                @empty
                    <div class="card-empty">برای امروز جلسه آنلاین زمان‌بندی‌شده‌ای ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if($canViewClassrooms || $canViewAssignments)
            <section class="card {{ $canViewMeetings ? 'c4' : 'c12' }}" aria-labelledby="attention-title" style="--c:#ff9a1f">
                <div class="card-title">
                    <div>
                        <h2 id="attention-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg></span>نیازمند توجه</h2>
                        <p>فقط هشدارهایی که بر اساس دیتابیس نیاز به اقدام دارند.</p>
                    </div>
                </div>
                <div class="owner-attention-list">
                    @if($classroomsWithoutTeacher > 0 && $firstAcademy && $canViewClassrooms)
                        <a class="at" href="{{ route('owner.classrooms.index', $firstAcademy) }}" style="--c:#ff9a1f">
                            <span class="ct">{{ \App\Support\PersianUi::digits($classroomsWithoutTeacher) }}</span>
                            <span class="gx"><b>کلاس بدون مدرس</b><small>کلاس فعال بدون مدرس فعال</small></span>
                            <svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg>
                        </a>
                    @endif
                    @if($capacityAlerts > 0 && $firstAcademy && $canViewClassrooms)
                        <a class="at" href="{{ route('owner.classrooms.index', $firstAcademy) }}" style="--c:#ff1f6b">
                            <span class="ct">{{ \App\Support\PersianUi::digits($capacityAlerts) }}</span>
                            <span class="gx"><b>کلاس نزدیک ظرفیت</b><small>کلاس‌هایی با ظرفیت ۹۰٪ یا بیشتر</small></span>
                            <svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg>
                        </a>
                    @endif
                    @if($pendingReviews > 0 && $firstAcademy && $canViewAssignments)
                        <a class="at" href="{{ route('owner.assignments.index', $firstAcademy) }}" style="--c:#22d3ee">
                            <span class="ct">{{ \App\Support\PersianUi::digits($pendingReviews) }}</span>
                            <span class="gx"><b>تکلیف بررسی‌نشده</b><small>پاسخ‌های ثبت‌شده و تصحیح‌نشده</small></span>
                            <svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg>
                        </a>
                    @endif
                    @if($attentionCount === 0)
                        <div class="card-empty">در حال حاضر مورد فوری ثبت نشده است.</div>
                    @endif
                </div>
            </section>
        @endif

        <section class="card c8 owner-trend-card" aria-labelledby="weekly-trend-title" style="--c:#ff1f6b">
            <div class="card-title">
                <div>
                    <h2 id="weekly-trend-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-chart"/></svg></span>روند ۱۳ هفته اخیر</h2>
                    <p>روند ثبت‌نام فعال و نرخ حضور از رکوردهای ذخیره‌شده؛ سری‌ها برای مقایسهٔ جهت روند، جداگانه مقیاس شده‌اند.</p>
                </div>
                @if($canViewReports)<a class="table-link" href="{{ route('owner.reports.index') }}">گزارش کامل ←</a>@endif
            </div>
            <div class="owner-chart-legend" aria-label="راهنمای نمودار">
                <span><i style="--legend:#ff1f6b"></i>ثبت‌نام دانش‌آموزان</span>
                <span><i style="--legend:#22d3ee"></i>نرخ حضور</span>
            </div>
            <svg class="ln owner-line-chart" viewBox="0 0 520 150" role="img" aria-label="نمودار روند هفتگی ثبت‌نام و نرخ حضور">
                <g class="owner-chart-grid">
                    @for($grid = 0; $grid < 6; $grid++)
                        <line x1="0" y1="{{ 8 + ($grid * 26) }}" x2="520" y2="{{ 8 + ($grid * 26) }}"/>
                    @endfor
                    @for($grid = 0; $grid < 13; $grid++)
                        <line x1="{{ $grid * (520 / 12) }}" y1="8" x2="{{ $grid * (520 / 12) }}" y2="138"/>
                    @endfor
                </g>
                @foreach($weeklyStudentSegments as $points)
                    <polyline class="owner-line-students" points="{{ $points }}"/>
                @endforeach
                @foreach($weeklyAttendanceSegments as $points)
                    <polyline class="owner-line-attendance" points="{{ $points }}"/>
                @endforeach
            </svg>
            <div class="owner-chart-labels">
                @foreach([0, 3, 6, 9, 12] as $labelIndex)
                    <span>{{ $weeklyLabels[$labelIndex] ?? '' }}</span>
                @endforeach
            </div>
            @if(array_sum(array_map('floatval', is_array($weeklyStudents) ? $weeklyStudents : [])) <= 0.0 && !count(array_filter(is_array($weeklyAttendance) ? $weeklyAttendance : [], 'is_numeric')))
                <div class="card-empty">در این بازه هنوز رکورد کافی برای رسم روند ثبت‌نام یا حضور وجود ندارد.</div>
            @endif
        </section>

        @if($canViewClassrooms)
            <section class="card c4" style="--c:#22d3ee" aria-labelledby="capacity-title">
                <div class="card-title">
                    <div><h2 id="capacity-title"><span class="section-icon"><svg class="ic"><use href="#i-chart"/></svg></span>وضعیت واقعی ظرفیت کلاس‌ها</h2><p>تجمیع کلاس‌های فعال با ظرفیت تعریف‌شده و دانش‌آموزان فعال ثبت‌شده در هر کلاس.</p></div>
                    @if($capacityRate !== null)<span class="pl" style="--c:#22d3ee">{{ \App\Support\PersianUi::digits($capacityRate) }}٪</span>@endif
                </div>
                <div class="metric-bars">
                    <div>
                        <div class="bar-label"><span>ظرفیت استفاده‌شده</span><strong>{{ \App\Support\PersianUi::digits($capacityUsed) }} از {{ \App\Support\PersianUi::digits($capacityTotal) }}</strong></div>
                        <div class="trk"><span class="fill" style="--c:#22d3ee;width:{{ $capacityRate ?? 0 }}%"></span></div>
                    </div>
                    <div>
                        <div class="bar-label"><span>ظرفیت خالی</span><strong>{{ \App\Support\PersianUi::digits($capacityRemaining) }}</strong></div>
                        <div class="trk"><span class="fill" style="--c:#8b3dff;width:{{ $capacityTotal > 0 ? min(100,($capacityRemaining/$capacityTotal)*100) : 0 }}%"></span></div>
                    </div>
                </div>
                <div class="mr"><small>{{ \App\Support\PersianUi::digits((int)data_get($capacity,'configured_classrooms',0)) }} کلاس دارای ظرفیت</small>@if($firstAcademy)<a class="table-link" href="{{ route('owner.classrooms.index', $firstAcademy) }}">مدیریت کلاس‌ها ←</a>@endif</div>
                @if($capacityTotal === 0)<div class="card-empty">برای محاسبه نرخ ظرفیت، ظرفیت حداقل یک کلاس فعال باید در دیتابیس تعیین شده باشد.</div>@endif
            </section>
        @endif

        @if($canViewCourses)
            <section class="card c4" style="--c:#8b3dff" aria-labelledby="course-publish-title">
                <div class="card-title">
                    <div><h2 id="course-publish-title"><span class="section-icon"><svg class="ic"><use href="#i-book"/></svg></span>وضعیت انتشار دوره‌ها</h2><p>نسبت دوره‌های منتشرشده به کل دوره‌های ثبت‌شده در آموزشگاه‌های مجاز این حساب.</p></div>
                    <a class="table-link" href="{{ route('owner.courses.index') }}">همه دوره‌ها ←</a>
                </div>
                <div class="dn">
                    <svg viewBox="0 0 120 120" role="img" aria-label="سهم دوره‌های منتشرشده">
                        <circle cx="60" cy="60" r="45" fill="none" stroke="#0a0e27" stroke-width="13"/>
                        @if($publishedRate !== null)<circle cx="60" cy="60" r="45" fill="none" stroke="#8b3dff" stroke-width="13" stroke-linecap="round" stroke-dasharray="{{ 2 * pi() * 45 * $publishedRate / 100 }} {{ 2 * pi() * 45 * (1 - $publishedRate / 100) }}" transform="rotate(-90 60 60)"/>@endif
                        <text x="60" y="57" text-anchor="middle" fill="#e8ecff" font-size="18" font-weight="800">{{ $publishedRate === null ? '—' : \App\Support\PersianUi::digits($publishedRate) . '٪' }}</text>
                        <text x="60" y="75" text-anchor="middle" fill="#8d96c9" font-size="9">انتشار</text>
                    </svg>
                    <div class="gx">
                        <b>{{ \App\Support\PersianUi::digits($publishedCourseCount) }} دوره منتشرشده</b>
                        <small>از {{ \App\Support\PersianUi::digits($courseCount) }} رکورد دوره</small>
                        <div class="bl">پیش‌نویس یا سایر وضعیت‌ها: {{ \App\Support\PersianUi::digits(max(0,$courseCount-$publishedCourseCount)) }}</div>
                    </div>
                </div>
                @if($courseCount === 0)<div class="card-empty">هنوز دوره‌ای در دیتابیس ثبت نشده است.</div>@endif
            </section>
        @endif

        @if($canViewFinance)
            <section class="card c4 owner-monthly-sales" aria-labelledby="monthly-sales-title" style="--c:#22d3ee">
                <div class="card-title">
                    <div>
                        <h2 id="monthly-sales-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-wallet"/></svg></span>خالص فروش چهار ماه</h2>
                        <p>پرداخت‌های تکمیل‌شده منهای بازپرداخت؛ واحد تومان.</p>
                    </div>
                    <a class="table-link" href="{{ route('owner.finance.index') }}">جزئیات مالی ←</a>
                </div>
                <div class="cys" role="img" aria-label="مقایسه خالص تراکنش‌های چهار ماه اخیر">
                    @foreach($monthlySalesBars as $bar)
                        <div class="cyc">
                            <b>{{ \App\Support\PersianUi::number($bar['value']) }}</b>
                            <div class="cy" style="--h:{{ $bar['height'] }}px;--c:{{ $bar['color'] }}"></div>
                            <small>{{ $bar['label'] }}</small>
                        </div>
                    @endforeach
                </div>
                @if(!count(array_filter($monthlySalesValues, 'is_numeric')))
                    <div class="card-empty">در بازهٔ انتخابی تراکنش تکمیل‌شده‌ای ثبت نشده است.</div>
                @endif
            </section>
        @endif

        @if($canViewPeople)
            <section class="card c4" aria-labelledby="login-title" style="--c:#22d3ee">
                <div class="card-title">
                    <div><h2 id="login-title"><span class="section-icon"><svg class="ic"><use href="#i-users"/></svg></span>ورود اخیر دانش‌آموزان</h2><p>آخرین رویدادهای واقعی ورود برای اعضای فعال همین آکادمی‌ها؛ این فهرست از cache شاخص‌ها خوانده نمی‌شود.</p></div>
                    @if($firstAcademy)<a class="table-link" href="{{ route('owner.people.index', $firstAcademy) }}">مدیریت اعضا ←</a>@endif
                </div>
                @forelse($recentStudentLogins as $login)
                    <a class="lr" href="{{ $firstAcademy ? route('owner.people.index', $firstAcademy) : route('owner.dashboard') }}" style="--c:#22d3ee">
                        <i class="tile"><svg class="ic"><use href="#i-users"/></svg></i>
                        <span class="gx"><b>{{ $login->subject?->name ?? 'حساب حذف‌شده' }}</b><small>{{ $login->subject?->email ?? 'بدون ایمیل' }}</small></span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($login->created_at) }}</span>
                        <svg class="ic"><use href="#i-arr"/></svg>
                    </a>
                @empty
                    <div class="card-empty">در گزارش فعالیت‌ها، ورود ثبت‌شده‌ای برای دانش‌آموزان فعال این آموزشگاه‌ها یافت نشد.</div>
                @endforelse
            </section>
        @endif

        @if($canViewReports)
            <section class="card c12" aria-labelledby="teacher-report-title" style="--c:#8b3dff">
                <div class="card-title">
                    <div><h2 id="teacher-report-title"><span class="section-icon"><svg class="ic"><use href="#i-chart"/></svg></span>عملکرد مدرس‌ها</h2><p>حداکثر ۸ مدرس بر اساس تعداد دانش‌آموزان فعال؛ میانگین پیشرفت و بررسی‌های معوق از محاسبات بک‌اند.</p></div>
                    <a class="table-link" href="{{ route('owner.reports.index') }}">گزارش کامل ←</a>
                </div>
                <div class="tsc">
                    <table>
                        <thead><tr><th>مدرس</th><th>دوره</th><th>دانش‌آموز فعال</th><th>پیشرفت میانگین</th><th>بررسی معوق</th>@if($canViewFinance)<th>خالص تراکنش (تومان)</th>@endif</tr></thead>
                        <tbody>
                        @forelse($teacherReports as $teacher)
                            <tr>
                                <td><span class="nm"><i class="av2">{{ mb_substr((string)$teacher->name,0,1) }}</i><span>{{ $teacher->name }}</span></span></td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->course_count) }}</td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->student_count) }}</td>
                                <td>
                                    <span class="row-value">{{ \App\Support\PersianUi::digits(round((float)$teacher->progress_average,1)) }}٪</span>
                                    <div class="trk"><span class="fill" style="--c:#8b3dff;width:{{ min(100,max(0,(float)$teacher->progress_average)) }}%"></span></div>
                                </td>
                                <td>{{ \App\Support\PersianUi::digits($teacher->pending_reviews) }}</td>
                                @if($canViewFinance)<td>{{ \App\Support\PersianUi::number($teacher->sales) }}</td>@endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canViewFinance ? 6 : 5 }}"><div class="card-empty">هنوز داده‌ای برای گزارش مدرس‌ها موجود نیست.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if($canViewMeetings)
            <section class="card c6" aria-labelledby="live-title" style="--c:#ff1f6b">
                <div class="card-title">
                    <div><h2 id="live-title"><span class="section-icon"><svg class="ic"><use href="#i-video"/></svg></span>کلاس‌های آنلاین آینده</h2><p>جلسه‌های زمان‌بندی‌شده در هفت روز آینده؛ مرتب‌شده بر اساس زمان شروع.</p></div>
                    @if($firstAcademy)<a class="table-link" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه کامل ←</a>@endif
                </div>
                @forelse($upcomingLiveClasses as $item)
                    <a class="at" href="{{ $item->classroom?->academy_id ? route('owner.schedules.index', ['academy' => $item->classroom->academy_id]) : ($firstAcademy ? route('owner.schedules.index', $firstAcademy) : route('owner.dashboard')) }}" style="--c:{{ $loop->first ? '#ff1f6b' : '#8b3dff' }}">
                        <span class="ct">{{ \App\Support\PersianUi::digits($loop->iteration) }}</span>
                        <span class="gx"><b>{{ $item->title }}</b><small>{{ $item->course?->title ?? 'بدون دوره مرتبط' }} · {{ $item->teacher?->name ?? 'مدرس تعیین نشده' }}</small><small>{{ $item->classroom?->title ?? 'کلاس آنلاین' }}</small></span>
                        <span class="row-value">{{ \App\Support\PersianUi::dateTime($item->scheduled_at) }}</span>
                    </a>
                @empty
                    <div class="card-empty">در هفت روز آینده جلسه آنلاین زمان‌بندی‌شده‌ای ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if($canViewCourses)
            <section class="card c6" aria-labelledby="recent-courses-title" style="--c:#22d3ee">
                <div class="card-title">
                    <div><h2 id="recent-courses-title"><span class="section-icon"><svg class="ic"><use href="#i-book"/></svg></span>آخرین دوره‌ها</h2><p>آخرین شش رکورد دوره از دیتابیس؛ برچسب وضعیت از خود رکورد خوانده می‌شود.</p></div>
                    <a class="table-link" href="{{ route('owner.courses.index') }}">همه دوره‌ها ←</a>
                </div>
                @forelse($recentCourses as $course)
                    <div class="at" style="--c:{{ $course->status === 'published' ? '#2ee6a6' : '#ff9a1f' }}">
                        <span class="tile"><svg class="ic"><use href="#i-book"/></svg></span>
                        <span class="gx"><b>{{ $course->title }}</b><small>{{ $course->academy?->name ?? 'بدون آموزشگاه' }} · {{ \App\Support\PersianUi::digits($course->active_students_count) }} دانش‌آموز فعال</small></span>
                        <span class="pl" style="--c:{{ $course->status === 'published' ? '#2ee6a6' : '#ff9a1f' }}">{{ $course->status === 'published' ? 'منتشرشده' : ($course->status === 'draft' ? 'پیش‌نویس' : $course->status) }}</span>
                        <a class="table-link" href="{{ route('owner.courses.show', $course) }}">بازبینی</a>
                    </div>
                @empty
                    <div class="card-empty">دوره‌ای در دیتابیس ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if(!$canViewPeople && !$canViewClassrooms && !$canViewCourses && !$canViewAttendance && !$canViewFinance && !$canViewReports && !$canViewMeetings)
            <section class="card c12"><div class="card-empty">برای نمایش شاخص‌ها، دسترسی مشاهده به بخش‌های مجاز این کارتابل برای این حساب فعال نیست.</div></section>
        @endif
    @endsection
