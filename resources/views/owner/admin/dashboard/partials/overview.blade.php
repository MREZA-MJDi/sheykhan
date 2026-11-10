<section class="ph c12">
            <div>
                <span class="chip">کارتابل مدیر آموزشگاه</span>
                <h1>{{ $firstAcademy?->name ?? 'آکادمی شیخان' }}</h1>
                <p class="sub">کارهای امروز، وضعیت پرداخت‌ها و حضور دانش‌آموزها را یک‌جا ببینید.</p>
            </div>
            <div class="act">
                <a class="mb" href="{{ route('account.guide', ['role' => 'owner']) }}">راهنمای استفاده از پنل ←</a>
                @if($canViewPeople && $firstAcademy)
                    <a class="go sm" href="{{ route('owner.people.index', $firstAcademy) }}"><svg class="ic"><use href="#i-users"/></svg> مدیریت اعضا</a>
                @endif
                @if($canViewClassrooms && $firstAcademy)
                    <a class="mb" href="{{ route('owner.classrooms.index', $firstAcademy) }}">مدیریت کلاس‌ها ←</a>
                @endif
            </div>
        </section>

        @if(($canViewFinance && $transactionCount === 0) || ($canViewAttendance && $attendanceTotal === 0))
            <section class="card c12 owner-dashboard-start" aria-labelledby="owner-start-title">
                <div class="card-title">
                    <div><h2 id="owner-start-title">چک‌لیست شروع</h2><p>بعد از ثبت اولین اطلاعات، هر مورد از این فهرست کنار می‌رود.</p></div>
                </div>
                <div class="owner-dashboard-start-actions">
                    @if($canViewFinance && $transactionCount === 0)<a class="mb sm" href="{{ route('owner.finance.index') }}">مشاهده وضعیت پرداخت‌ها</a>@endif
                    @if($canViewAttendance && $attendanceTotal === 0 && $firstAcademy)<a class="mb sm" href="{{ route('owner.attendance.index', $firstAcademy) }}">ثبت اولین حضور و غیاب</a>@endif
                </div>
            </section>
        @endif

        {{-- سه شاخص عملیاتی؛ بدون نمودار تزئینی و توضیح فنی. --}}
        @if($canViewAttendance && $firstAcademy)
            <a class="card kpi c4 kpi-link owner-dashboard-kpi" href="{{ route('owner.attendance.index', $firstAcademy) }}" aria-label="مشاهده غیبت‌ها؛ {{ \App\Support\PersianUi::digits($absentCount) }} مورد">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-checksq"/></svg>غیبت‌ها · {{ $periodCaption }}</div>
                <b>{{ \App\Support\PersianUi::digits($absentCount) }} <small>مورد</small></b>
                <div class="metric-caption">{{ $attendanceTotal === 0 ? 'هنوز حضور و غیابی ثبت نشده است.' : ($absentCount === 0 ? 'در این بازه غیبتی ثبت نشده است.' : 'دانش‌آموزانی که در این بازه غایب بوده‌اند.') }}</div>
                <div class="metric-foot"><span>غیبت امروز: {{ \App\Support\PersianUi::digits($todayAbsentCount) }}</span><span class="table-link" aria-hidden="true">مشاهده ←</span></div>
            </a>
        @endif

        @if($canViewFinance)
            <a class="card kpi c4 kpi-link owner-dashboard-kpi" href="{{ route('owner.finance.index') }}" aria-label="مشاهده پرداخت‌ها {{ $periodCaption }}">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-wallet"/></svg>پرداخت‌ها · {{ $periodCaption }}</div>
                @if($transactionCount > 0)
                    <b>{{ \App\Support\PersianUi::number($sales) }} <small>تومان</small></b>
                    <div class="metric-caption">مبلغ دریافتی پس از کسر بازپرداخت‌ها</div>
                @else
                    <b class="owner-kpi-empty-value">ثبت نشده</b>
                    <div class="metric-caption">هنوز پرداختی در این بازه ثبت نشده است.</div>
                @endif
                <div class="metric-foot"><span>وضعیت پرداخت‌ها</span><span class="table-link" aria-hidden="true">مشاهده ←</span></div>
            </a>
        @endif

        @if($canViewAssignments || $canViewClassrooms)
            <a class="card kpi c4 kpi-link owner-dashboard-kpi" href="{{ $attentionRoute }}" aria-label="موارد نیازمند پیگیری؛ {{ \App\Support\PersianUi::digits($attentionCount) }} مورد">
                <div class="kpi-head"><svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg>نیازمند پیگیری</div>
                <b>{{ \App\Support\PersianUi::digits($attentionCount) }} <small>مورد</small></b>
                <div class="metric-caption">{{ $attentionCount === 0 ? 'کار عقب‌مانده‌ای برای پیگیری ندارید.' : 'تکالیف بررسی‌نشده و هشدارهای کلاس.' }}</div>
                <div class="metric-foot"><span>{{ \App\Support\PersianUi::digits($pendingReviews) }} تکلیف · {{ \App\Support\PersianUi::digits($capacityAlerts + $classroomsWithoutTeacher) }} هشدار کلاس</span><span class="table-link" aria-hidden="true">بررسی ←</span></div>
            </a>
        @endif

        @if($canViewMeetings)
            <section class="card c8" aria-labelledby="today-schedule-title" style="--c:#0e9f8e">
                <div class="card-title">
                    <div>
                        <h2 id="today-schedule-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-calendar"/></svg></span>برنامه امروز</h2>
                        <p>جلسه‌های زمان‌بندی‌شده امروز از داده‌های واقعی کلاس‌ها.</p>
                    </div>
                    @if($firstAcademy)<a class="table-link" href="{{ route('owner.schedules.index', $firstAcademy) }}">برنامه کامل ←</a>@endif
                </div>
                @forelse($todayLiveClasses as $item)
                    <a class="tl {{ $item->status === 'live' ? 'live' : '' }}" href="{{ $item->classroom?->academy_id ? route('owner.schedules.index', ['academy' => $item->classroom->academy_id]) : route('owner.dashboard') }}" style="--c:{{ $item->status === 'live' ? '#d64550' : '#0e9f8e' }}">
                        <b>{{ \App\Support\PersianUi::time($item->scheduled_at) }}</b>
                        <span class="gx"><b>{{ $item->title }}</b><small>{{ $item->course?->title ?? 'بدون دوره مرتبط' }} · {{ $item->teacher?->name ?? 'مدرس تعیین نشده' }}</small></span>
                        @if($item->status === 'live')
                            <span class="pl" style="--c:#d64550">در حال اجرا</span>
                        @else
                            <span class="pl" style="--c:#0e9f8e">زمان‌بندی‌شده</span>
                        @endif
                    </a>
                @empty
                    <div class="card-empty">برای امروز جلسه آنلاین زمان‌بندی‌شده‌ای ثبت نشده است.</div>
                @endforelse
            </section>
        @endif

        @if($canViewAttendance && $firstAcademy)
            <section class="card c4 owner-dashboard-absences" aria-labelledby="absent-students-title">
                <div class="card-title">
                    <div><h2 id="absent-students-title">چه کسانی غایب بودند؟</h2><p>آخرین غیبت‌های ثبت‌شده در {{ $periodCaption }}.</p></div>
                    <a class="table-link" href="{{ route('owner.attendance.index', $firstAcademy) }}">همه موارد ←</a>
                </div>
                @forelse($absentStudents as $absent)
                    <div class="owner-dashboard-absent-row">
                        <strong>{{ $absent->student_name }}</strong>
                        <span>{{ $absent->classroom_title }} · {{ \App\Support\PersianUi::date($absent->attendance_date) }}</span>
                    </div>
                @empty
                    <div class="card-empty">{{ $attendanceTotal === 0 ? 'هنوز حضور و غیابی ثبت نشده است.' : 'در این بازه غیبتی ثبت نشده است.' }}</div>
                @endforelse
            </section>
        @endif

        @if($canViewClassrooms || $canViewAssignments)
            <section class="card {{ $canViewMeetings ? 'c4' : 'c12' }}" aria-labelledby="attention-title" style="--c:#ff9a1f">
                <div class="card-title">
                    <div>
                        <h2 id="attention-title"><span class="section-icon"><svg class="ic" aria-hidden="true"><use href="#i-bell"/></svg></span>نیازمند توجه</h2>
                        <p>مواردی که برای مدیریت آموزشگاه نیاز به پیگیری دارند.</p>
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
                        <a class="at" href="{{ route('owner.classrooms.index', $firstAcademy) }}" style="--c:#d64550">
                            <span class="ct">{{ \App\Support\PersianUi::digits($capacityAlerts) }}</span>
                            <span class="gx"><b>کلاس نزدیک ظرفیت</b><small>کلاس‌هایی با ظرفیت ۹۰٪ یا بیشتر</small></span>
                            <svg class="ic" aria-hidden="true"><use href="#i-arr"/></svg>
                        </a>
                    @endif
                    @if($pendingReviews > 0 && $firstAcademy && $canViewAssignments)
                        <a class="at" href="{{ route('owner.assignments.index', $firstAcademy) }}" style="--c:#0e9f8e">
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
