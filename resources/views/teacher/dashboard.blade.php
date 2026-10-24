@extends('layouts.teacher')

@section('title', 'داشبورد مدرس | شیخان')
@section('header-title', 'داشبورد مدرس')

@section('content')
    @php
        $dashboardMetrics = $metrics ?? [];
        $pendingReviews = max(0, (int) ($dashboardMetrics['pendingReviews'] ?? 0));
        $pendingAssignmentReviews = max(0, (int) ($dashboardMetrics['pendingAssignmentReviews'] ?? 0));
        $pendingExamReviews = max(0, (int) ($dashboardMetrics['pendingExamReviews'] ?? 0));
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
        $focusRoute = $pendingAssignmentReviews > 0
            ? route('teacher.assignments.index')
            : ($pendingExamReviews > 0 ? route('teacher.exams.index') : route('teacher.classrooms.index'));
        $focusLabel = $pendingAssignmentReviews > 0
            ? 'شروع تصحیح تکالیف'
            : ($pendingExamReviews > 0 ? 'بررسی آزمون‌ها' : 'مدیریت کلاس‌ها');
    @endphp

    <section
        class="td-dashboard"
        data-teacher-dashboard
        data-chart='@json([
            "week" => $weekChart,
            "month" => $monthChart,
        ])'
    >
        <header class="td-header">
            <div class="td-heading-copy">
                <span class="td-eyebrow">خلاصه وضعیت آموزشی · {{ $dashboardDate ?? \App\Support\PersianUi::date(now()) }}</span>
                <h2>سلام، {{ $teacher->name }} <span aria-hidden="true">👋</span></h2>
                <p>
                    فعالیت کلاس‌ها، بازخوردها و جلسه‌های پیش‌رو را از همین صفحه پیگیری کن.
                </p>
            </div>

            <div class="td-header-actions">
                <label class="td-search">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                    <input type="search" data-dashboard-search placeholder="جست‌وجو در این داشبورد…" aria-label="جست‌وجو در داشبورد مدرس">
                </label>

                <button type="button" class="td-theme-toggle" data-teacher-theme-toggle aria-label="تغییر تم" aria-pressed="false">
                    <svg data-theme-moon viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 14.2A8.5 8.5 0 0 1 9.8 3.5 8.7 8.7 0 1 0 20.5 14.2Z"></path></svg>
                    <svg data-theme-sun viewBox="0 0 24 24" aria-hidden="true" hidden><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"></path></svg>
                    <span data-theme-label>تم تیره</span>
                </button>
            </div>
        </header>

        <section class="td-stats" aria-label="آمار آموزشی">
            <a class="td-stat td-stat-indigo" href="{{ route('teacher.classrooms.index') }}" data-dashboard-searchable>
                <span class="td-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="8" height="7" rx="1.5"></rect><rect x="13" y="4" width="8" height="7" rx="1.5"></rect><rect x="3" y="13" width="8" height="7" rx="1.5"></rect><rect x="13" y="13" width="8" height="7" rx="1.5"></rect></svg></span>
                <span class="td-stat-label">کلاس‌های فعال</span>
                <strong>{{ \App\Support\PersianUi::digits($activeClasses) }}</strong>
                <small>کلاس‌های تحت مدیریت</small>
            </a>

            <a class="td-stat td-stat-blue" href="{{ route('teacher.courses.index') }}" data-dashboard-searchable>
                <span class="td-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"></path><path d="M4 5.5v16"></path></svg></span>
                <span class="td-stat-label">دوره‌های در حال پیگیری</span>
                <strong>{{ \App\Support\PersianUi::digits($courseCards->count()) }}</strong>
                <small>بر اساس پیشرفت ثبت‌شده</small>
            </a>

            <a class="td-stat td-stat-cyan" href="{{ route('teacher.schedule.index') }}" data-dashboard-searchable>
                <span class="td-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3.5 2"></path></svg></span>
                <span class="td-stat-label">جلسات این هفته</span>
                <strong>{{ \App\Support\PersianUi::digits($weeklySessions) }}</strong>
                <small>{{ \App\Support\PersianUi::digits($completedSessionsCount) }} جلسه برگزار شده</small>
            </a>

            <a class="td-stat td-stat-violet" href="{{ route('teacher.live-classes.index') }}" data-dashboard-searchable>
                <span class="td-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="13" height="14" rx="2"></rect><path d="m16 10 5-3v10l-5-3z"></path></svg></span>
                <span class="td-stat-label">دانش‌آموزان</span>
                <strong>{{ \App\Support\PersianUi::digits($studentCount) }}</strong>
                <small>عضو فعال کلاس‌های شما</small>
            </a>

            <a class="td-stat td-stat-amber" href="{{ route('teacher.assignments.index') }}" data-dashboard-searchable>
                <span class="td-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 4v4h8V4M8 13h8M8 17h5"></path></svg></span>
                <span class="td-stat-label">تکلیف‌های منتظر تصحیح</span>
                <strong>{{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }}</strong>
                <small>ارسال‌شده و نیازمند بازخورد</small>
            </a>

            <a class="td-stat td-stat-rose" href="{{ route('teacher.exams.index') }}" data-dashboard-searchable>
                <span class="td-stat-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span>
                <span class="td-stat-label">آزمون‌های منتظر بررسی</span>
                <strong>{{ \App\Support\PersianUi::digits($pendingExamReviews) }}</strong>
                <small>{{ \App\Support\PersianUi::digits($pendingReviews) }} کار نیازمند پیگیری</small>
            </a>
        </section>

        <section class="td-focus-card {{ $pendingReviews > 0 ? 'has-pending' : 'is-clear' }}" aria-labelledby="td-focus-title">
            <span class="td-focus-symbol" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 3 2.8 20h18.4L12 3Z"></path><path d="M12 9v5m0 3h.01"></path></svg>
            </span>
            <div class="td-focus-copy">
                <span class="td-focus-label">{{ $pendingReviews > 0 ? 'نیازمند توجه' : 'وضعیت امروز' }}</span>
                <h3 id="td-focus-title">
                    @if($pendingAssignmentReviews > 0)
                        {{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }} تکلیف منتظر تصحیح است.
                    @elseif($pendingExamReviews > 0)
                        {{ \App\Support\PersianUi::digits($pendingExamReviews) }} آزمون برای بررسی باقی مانده است.
                    @elseif($activeClasses > 0)
                        کلاس‌ها آماده‌اند؛ جلسه‌های امروز را مرور کن.
                    @else
                        هنوز کلاس فعالی به این حساب متصل نشده است.
                    @endif
                </h3>
                <p>
                    @if($pendingAssignmentReviews > 0)
                        بازخورد به‌موقع کمک می‌کند دانش‌آموز مسیر بعدی یادگیری را روشن‌تر ببیند.
                    @elseif($pendingExamReviews > 0)
                        پاسخ‌های ارسال‌شده را بررسی کن و نتایج را از بخش آزمون‌ها نهایی کن.
                    @elseif($activeClasses > 0)
                        برنامه امروز، جلسه‌های آنلاین و وضعیت کلاس‌ها از بخش‌های پایین در دسترس‌اند.
                    @else
                        با اتصال کلاس یا دوره به حساب مدرس، اطلاعات آموزشی این صفحه نمایش داده می‌شود.
                    @endif
                </p>
            </div>
            <a class="td-focus-action" href="{{ $focusRoute }}">{{ $focusLabel }} <span aria-hidden="true">←</span></a>
        </section>

        <div class="td-grid td-grid-even">
            <section class="td-panel" aria-labelledby="td-students-title">
                <div class="td-panel-head">
                    <div>
                        <span class="td-panel-eyebrow">یادگیرندگان</span>
                        <h3 id="td-students-title">دانش‌آموزان با پیشرفت بالاتر</h3>
                    </div>
                    <a class="td-text-link" href="{{ route('teacher.students.index') }}">همه دانش‌آموزان <span aria-hidden="true">←</span></a>
                </div>

                <div class="td-student-strip">
                    @forelse($featuredStudents as $studentItem)
                        @php($studentProgress = max(0, min(100, (float) ($studentItem->progress_average ?? 0))))
                        <a class="td-student" href="{{ route('teacher.students.index') }}" data-dashboard-searchable>
                            <span class="td-avatar">{{ \Illuminate\Support\Str::substr(trim((string) ($studentItem->name ?? '')), 0, 1) ?: 'د' }}</span>
                            <strong>{{ $studentItem->name }}</strong>
                            <small>{{ \App\Support\PersianUi::digits($studentProgress) }}٪ پیشرفت</small>
                        </a>
                    @empty
                        <div class="td-empty">هنوز دادهٔ کافی برای نمایش پیشرفت دانش‌آموزان وجود ندارد.</div>
                    @endforelse
                </div>
            </section>

            <section class="td-panel" aria-labelledby="td-shortcuts-title">
                <div class="td-panel-head">
                    <div>
                        <span class="td-panel-eyebrow">دسترسی سریع</span>
                        <h3 id="td-shortcuts-title">مسیرهای پرکاربرد</h3>
                    </div>
                </div>

                <div class="td-shortcuts">
                    <a href="{{ route('teacher.assignments.index') }}" class="td-shortcut td-shortcut-blue" data-dashboard-searchable>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 12h8M8 16h5"></path></svg></span>
                        <span><strong>تکالیف</strong><small>{{ \App\Support\PersianUi::digits($pendingAssignmentReviews) }} مورد برای تصحیح</small></span>
                        <b aria-hidden="true">←</b>
                    </a>
                    <a href="{{ route('teacher.exams.index') }}" class="td-shortcut td-shortcut-violet" data-dashboard-searchable>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"></path><path d="M8 8h8M8 12h8M8 16h5"></path></svg></span>
                        <span><strong>آزمون‌ها</strong><small>{{ \App\Support\PersianUi::digits($pendingExamReviews) }} مورد برای بررسی</small></span>
                        <b aria-hidden="true">←</b>
                    </a>
                    <a href="{{ route('teacher.attendance.index') }}" class="td-shortcut td-shortcut-green" data-dashboard-searchable>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M9 11 12 14 21 5"></path><path d="M20 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg></span>
                        <span><strong>حضور و غیاب</strong><small>ثبت و پیگیری حضور کلاس‌ها</small></span>
                        <b aria-hidden="true">←</b>
                    </a>
                    <a href="{{ route('teacher.schedule.index') }}" class="td-shortcut td-shortcut-amber" data-dashboard-searchable>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M8 3v4m8-4v4M3 10h18"></path></svg></span>
                        <span><strong>برنامه هفتگی</strong><small>جلسات و زمان‌بندی‌ها</small></span>
                        <b aria-hidden="true">←</b>
                    </a>
                    @if(auth()->user()?->hasPermission('student_messages.view'))
                        <a href="{{ route('teacher.student-messages.index') }}" class="td-shortcut td-shortcut-cyan" data-dashboard-searchable>
                            <span aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5h16v12H8l-4 4z"></path><path d="M8 9h8m-8 4h5"></path></svg></span>
                            <span><strong>پیام‌های دانش‌آموزان</strong><small>مکاتبات قابل دسترسی شما</small></span>
                            <b aria-hidden="true">←</b>
                        </a>
                    @endif
                    <a href="{{ route('teacher.meetings.index') }}" class="td-shortcut td-shortcut-rose" data-dashboard-searchable>
                        <span aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle><path d="M8 12h8m-4-4v8"></path></svg></span>
                        <span><strong>جلسات با اولیا</strong><small>برنامه‌ریزی و پیگیری جلسات</small></span>
                        <b aria-hidden="true">←</b>
                    </a>
                </div>
            </section>
        </div>

        <div class="td-grid td-grid-main">
            <section class="td-panel" aria-labelledby="td-courses-title">
                <div class="td-panel-head">
                    <div>
                        <span class="td-panel-eyebrow">محتوای آموزشی</span>
                        <h3 id="td-courses-title">دوره‌های من</h3>
                    </div>
                    <a class="td-text-link" href="{{ route('teacher.courses.index') }}">مدیریت دوره‌ها <span aria-hidden="true">←</span></a>
                </div>

                <div class="td-course-grid">
                    @forelse($courseCards->take(3) as $course)
                        @php($courseValue = max(0, min(100, (float) ($course->progress_average ?? 0))))
                        <a href="{{ route('teacher.courses.index') }}" class="td-course-card" data-dashboard-searchable>
                            <span class="td-course-mark" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5z"></path><path d="M4 5.5v16"></path></svg></span>
                            <strong>{{ $course->title }}</strong>
                            <small>{{ \App\Support\PersianUi::digits($course->student_count ?? 0) }} دانش‌آموز · میانگین {{ \App\Support\PersianUi::digits($courseValue) }}٪</small>
                            <span class="td-progress-track" aria-label="میانگین پیشرفت دوره {{ $courseValue }} درصد"><i style="width: {{ $courseValue }}%"></i></span>
                            <span class="td-course-footer">نمایش دوره <b aria-hidden="true">←</b></span>
                        </a>
                    @empty
                        <div class="td-empty">برای دوره‌های شما هنوز دادهٔ پیشرفتی ثبت نشده است.</div>
                    @endforelse
                </div>
            </section>

            <section class="td-panel" aria-labelledby="td-today-title">
                <div class="td-panel-head">
                    <div>
                        <span class="td-panel-eyebrow">{{ $dashboardDate ?? \App\Support\PersianUi::date(now()) }}</span>
                        <h3 id="td-today-title">برنامه امروز</h3>
                    </div>
                    <a class="td-text-link" href="{{ route('teacher.schedule.index') }}">برنامه کامل <span aria-hidden="true">←</span></a>
                </div>

                <div class="td-list">
                    @forelse(collect($todaySessions ?? [])->take(5) as $session)
                        @php($isLive = ($session['status'] ?? '') === 'در حال برگزاری')
                        <article class="td-list-item" data-dashboard-searchable>
                            <span class="td-time">{{ $session['time'] ?? '—' }}</span>
                            <span class="td-status-dot {{ $isLive ? 'is-live' : '' }}" aria-hidden="true"></span>
                            <span class="td-list-copy">
                                <strong>{{ $session['title'] ?? 'جلسه آموزشی' }}</strong>
                                <small>{{ $session['meta'] ?? 'بدون توضیح' }}</small>
                            </span>
                            <span class="td-list-actions">
                                <span class="td-status {{ $isLive ? 'is-live' : '' }}">{{ $session['status'] ?? 'برنامه‌ریزی‌شده' }}</span>
                                @if(!empty($session['can_join']) && filled($session['meeting_url'] ?? null))
                                    <a class="td-join" href="{{ $session['meeting_url'] }}" target="_blank" rel="noopener noreferrer" aria-label="ورود به جلسه آنلاین {{ $session['title'] ?? 'کلاس' }}">ورود <span aria-hidden="true">↗</span></a>
                                @endif
                            </span>
                        </article>
                    @empty
                        <div class="td-empty">برای امروز جلسه‌ای در برنامه ثبت نشده است.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="td-grid td-grid-main">
            <section class="td-panel" aria-labelledby="td-progress-title">
                <div class="td-panel-head">
                    <div>
                        <span class="td-panel-eyebrow">فعالیت ثبت‌شده در درس‌ها</span>
                        <h3 id="td-progress-title">روند پیشرفت دانش‌آموزان</h3>
                    </div>
                    <div class="td-range" role="group" aria-label="بازه نمودار">
                        <button type="button" class="active" data-teacher-range="week" aria-pressed="true">هفته</button>
                        <button type="button" data-teacher-range="month" aria-pressed="false">ماه</button>
                    </div>
                </div>

                <div class="td-chart-wrap">
                    <div class="td-chart-scale" aria-hidden="true"><span>۱۰۰٪</span><span>۷۵٪</span><span>۵۰٪</span><span>۲۵٪</span><span>۰٪</span></div>
                    <div class="td-chart">
                        <div class="td-chart-lines" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                        <div class="td-chart-bars" data-teacher-bars role="img" aria-label="نمودار فعالیت اخیر درس‌ها بر اساس آخرین مشاهده">
                            @for($index = 0; $index < $chartSlots; $index++)
                                @php($chartValue = max(0, min(100, (float) ($weekChart['values'][$index] ?? 0))))
                                <div class="td-chart-column" data-chart-column>
                                    <span class="td-chart-value" data-chart-value>{{ \App\Support\PersianUi::digits($chartValue) }}٪</span>
                                    <span class="td-chart-bar" data-chart-bar style="height: {{ $chartValue }}%"></span>
                                    <small data-chart-label>{{ $weekChart['labels'][$index] ?? '' }}</small>
                                </div>
                            @endfor
                        </div>
                        @if($chartSlots === 0)
                            <p class="td-chart-empty" data-chart-empty>هنوز داده‌ای برای رسم نمودار این بازه ثبت نشده است.</p>
                        @else
                            <p class="td-chart-empty" data-chart-empty hidden>هنوز داده‌ای برای رسم نمودار این بازه ثبت نشده است.</p>
                        @endif
                    </div>
                </div>

                <p class="td-chart-note">
                    نمودار، رکوردهای فعلیِ پیشرفت درس‌ها را بر اساس آخرین زمان مشاهده گروه‌بندی می‌کند. تاریخچهٔ دقیق تغییرات روزبه‌روز در این داده‌ها ذخیره نمی‌شود.
                </p>
                <div class="td-course-summary">
                    @forelse($courseCards->take(4) as $course)
                        @php($courseValue = max(0, min(100, (float) ($course->progress_average ?? 0))))
                        <div class="td-summary-row" data-dashboard-searchable>
                            <span><strong>{{ $course->title }}</strong><small>{{ \App\Support\PersianUi::digits($course->student_count ?? 0) }} دانش‌آموز</small></span>
                            <span class="td-summary-value">{{ \App\Support\PersianUi::digits($courseValue) }}٪</span>
                            <span class="td-summary-track"><i style="width: {{ $courseValue }}%"></i></span>
                        </div>
                    @empty
                        <div class="td-empty">با ثبت فعالیت در درس‌ها، خلاصهٔ پیشرفت دوره‌ها اینجا نمایش داده می‌شود.</div>
                    @endforelse
                </div>
            </section>

            <section class="td-panel" aria-labelledby="td-online-title">
                <div class="td-panel-head">
                    <div>
                        <span class="td-panel-eyebrow">هفت روز آینده</span>
                        <h3 id="td-online-title">کلاس‌های آنلاین</h3>
                    </div>
                    <a class="td-text-link" href="{{ route('teacher.live-classes.index') }}">همه جلسه‌ها <span aria-hidden="true">←</span></a>
                </div>

                <div class="td-list td-upcoming-list">
                    @forelse(collect($upcomingClasses ?? [])->take(5) as $item)
                        <article class="td-list-item td-upcoming-item" data-dashboard-searchable>
                            <span class="td-time-box">
                                <strong>{{ \App\Support\PersianUi::time($item->scheduled_at) }}</strong>
                                <small>{{ \App\Support\PersianUi::date($item->scheduled_at) }}</small>
                            </span>
                            <span class="td-status-dot {{ ($item->status ?? '') === 'live' ? 'is-live' : '' }}" aria-hidden="true"></span>
                            <span class="td-list-copy">
                                <strong>{{ $item->title }}</strong>
                                <small>{{ $item->course?->title ?? 'دوره آموزشی' }} · {{ $item->classroom?->title ?? 'جلسه آزاد' }}</small>
                            </span>
                            <span class="td-list-actions">
                                <span class="td-status {{ ($item->status ?? '') === 'live' ? 'is-live' : '' }}">{{ ($item->status ?? '') === 'live' ? 'در حال برگزاری' : 'زمان‌بندی‌شده' }}</span>
                                @if($item->can_join && filled($item->meeting_url))
                                    <a class="td-join" href="{{ $item->meeting_url }}" target="_blank" rel="noopener noreferrer" aria-label="ورود به جلسه آنلاین {{ $item->title }}">ورود <span aria-hidden="true">↗</span></a>
                                @endif
                            </span>
                        </article>
                    @empty
                        <div class="td-empty">جلسه آنلاین فعالی برای هفت روز آینده ثبت نشده است.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="td-panel" aria-labelledby="td-assignments-title">
            <div class="td-panel-head">
                <div>
                    <span class="td-panel-eyebrow">پیگیری سریع</span>
                    <h3 id="td-assignments-title">آخرین تکالیف</h3>
                </div>
                <a class="td-text-link" href="{{ route('teacher.assignments.index') }}">مشاهده همه <span aria-hidden="true">←</span></a>
            </div>

            <div class="td-assignment-list">
                @forelse($recentAssignments->take(6) as $activity)
                    <article class="td-assignment-row" data-dashboard-searchable>
                        <span class="td-assignment-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4h8l3 3v13H5V4z"></path><path d="M8 12h8M8 16h5"></path></svg></span>
                        <span class="td-assignment-copy">
                            <strong>{{ $activity->title }}</strong>
                            <small>{{ $activity->classroom?->title ?? 'عمومی دوره' }}
                                ·
                                @if($activity->due_at)
                                    مهلت {{ \App\Support\PersianUi::date($activity->due_at) }}، ساعت {{ \App\Support\PersianUi::time($activity->due_at) }}
                                @else
                                    بدون موعد مشخص
                                @endif
                            </small>
                        </span>
                        <span class="td-assignment-count"><strong>{{ \App\Support\PersianUi::digits($activity->submitted_count ?? 0) }}</strong><small>تحویل</small></span>
                        <span class="td-assignment-count is-pending"><strong>{{ \App\Support\PersianUi::digits($activity->pending_review_count ?? 0) }}</strong><small>منتظر بررسی</small></span>
                    </article>
                @empty
                    <div class="td-empty">هنوز تکلیفی برای نمایش ثبت نشده است.</div>
                @endforelse
            </div>
        </section>
    </section>
@endsection