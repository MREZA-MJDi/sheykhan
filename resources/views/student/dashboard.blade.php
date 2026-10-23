@extends('layouts.student')

@section('title', 'داشبورد دانش‌آموز | شیخان')
@section('header-title', 'خانه یادگیری')

@section('content')
    @php
        $progress = max(0, min(100, (float) ($overallProgress ?? 0)));
        $today = now()->startOfDay();
        $courses = collect($courses ?? []);
        $assignments = collect($assignments ?? []);
        $recentResults = collect($recentResults ?? []);
        $teachers = collect($teachers ?? []);
        $resources = collect($resources ?? []);
        $sessions = collect($sessions ?? []);
        $upcomingLiveClasses = collect($upcomingLiveClasses ?? []);
        $todayClasses = $upcomingLiveClasses->filter(
            fn ($class) => $class->scheduled_at && $class->scheduled_at->isSameDay($today)
        )->values();
        $upcomingMeetings = collect($upcomingMeetings ?? []);
        $achievements = collect($achievements ?? []);
        $nextAssignment = $assignments->first(fn ($item) => empty($item->submitted_at));
        $latestResult = $recentResults->first();
        $topCourse = $courses->sortByDesc('learning_progress')->first();
        $activeCourses = (int) ($activeCourseCount ?? $courses->count());
        $sessionCount = $sessions->count();
        $pending = (int) ($pendingAssignments ?? 0);
        $completedLessons = (int) ($completedLessonsCount ?? 0);
        $totalLessons = (int) ($totalLessonsCount ?? 0);
        $studyMinutes = (int) ($studyMinutesLast7Days ?? 0);
        $studyStreak = (int) ($studyStreak ?? 0);
        $studyWeek = collect($studyWeek ?? []);
        $maxStudy = max(1, (int) $studyWeek->max('minutes'));
        $jalaliCalendar = App\Support\PersianUi::calendar(now());
        $daysInMonth = $jalaliCalendar['days_in_month'];
        $firstWeekday = $jalaliCalendar['first_weekday'];
        $studentUser = $student;
        $quickLinks = [
            ['route'=>'student.courses.index','label'=>'دوره‌های من','description'=>'درس‌ها و میزان پیشرفت','icon'=>'▤','permission'=>'courses.view'],
            ['route'=>'student.live-classes.index','label'=>'کلاس‌های من','description'=>'برنامه کلاس و ضبط جلسات','icon'=>'◉','permission'=>'live_classes.view'],
            ['route'=>'student.assignments.index','label'=>'تکالیف','description'=>'موعدها و ارسال تمرین‌ها','icon'=>'✎','permission'=>'assignments.view'],
            ['route'=>'student.exams.index','label'=>'آزمون‌ها','description'=>'آزمون‌های منتشرشده','icon'=>'▣','permission'=>'exams.view'],
            ['route'=>'student.results.index','label'=>'نمرات و عملکرد','description'=>'نمرات و بازخوردهای ثبت‌شده','icon'=>'↗','permission'=>'results.view'],
            ['route'=>'student.resources.index','label'=>'جزوه‌ها و منابع','description'=>'فایل‌های مجاز دوره‌ها','icon'=>'▤','permission'=>'resources.view'],
            ['route'=>'student.notes.index','label'=>'یادداشت‌ها','description'=>'یادداشت‌های شخصی','icon'=>'✎','permission'=>'notes.view'],
            ['route'=>'student.attendance.index','label'=>'حضور و غیاب','description'=>'سوابق حضور کلاس','icon'=>'◷','permission'=>'attendance.view'],
            ['route'=>'student.achievements.index','label'=>'دستاوردها','description'=>'موفقیت‌های آموزشی','icon'=>'★','permission'=>'achievements.view'],
            ['route'=>'student.profile.edit','label'=>'حساب کاربری','description'=>'پروفایل و تنظیمات','icon'=>'○','permission'=>'profile.view'],
        ];
    @endphp

    <div class="student-dashboard-reference">
        <section class="sdr-hero" aria-labelledby="sdr-welcome-title">
            <div class="sdr-hero-copy">
                <span class="sdr-eyebrow">مرکز یادگیری شیخان</span>
                <h2 id="sdr-welcome-title">سلام {{ $student->name }} 👋</h2>
                <p>
                    مسیر یادگیری‌ات اینجاست؛
                    {{ App\Support\PersianUi::digits($activeCourses) }} دوره فعال داری،
                    {{ App\Support\PersianUi::digits($pending) }} تکلیف منتظر پیگیری است
                    و میانگین پیشرفتت {{ App\Support\PersianUi::digits($progress) }}٪ است.
                </p>
                <div class="sdr-hero-actions">
                    @if($nextAssignment)
                        <a class="sdr-btn primary" href="{{ route('student.assignments.show', $nextAssignment->id) }}">ادامه تکلیف <span aria-hidden="true">←</span></a>
                    @elseif($topCourse?->next_lesson)
                        <a class="sdr-btn primary" href="{{ route('student.lessons.show', $topCourse->next_lesson->id) }}">ادامه یادگیری <span aria-hidden="true">←</span></a>
                    @else
                        <a class="sdr-btn primary" href="{{ route('student.courses.index') }}">دیدن دوره‌های من <span aria-hidden="true">←</span></a>
                    @endif
                    @if($studentUser->hasPermission('exams.view'))
                        <a class="sdr-btn" href="{{ route('student.exams.index') }}">آزمون‌ها</a>
                    @endif
                    @if($studentUser->hasPermission('student_messages.view'))
                        <a class="sdr-btn" href="{{ route('student.messages.index') }}">پیام‌های من</a>
                    @endif
                </div>
                <div class="sdr-hero-meta">
                    <span><b>{{ App\Support\PersianUi::digits($studyStreak) }}</b> روز پیوسته</span>
                    <span><b>{{ App\Support\PersianUi::digits($studyMinutes) }}</b> دقیقه در ۷ روز</span>
                    <span><b>{{ App\Support\PersianUi::digits($completedLessons) }}</b> درس کامل</span>
                </div>
            </div>
            <div class="sdr-hero-art" aria-hidden="true">
                <span class="sdr-hero-orbit"></span>
                <span class="sdr-hero-symbol">ش</span>
                <div class="sdr-hero-mini">
                    <div class="sdr-hero-mini-head"><span>پیشرفت کلی</span><strong>{{ App\Support\PersianUi::digits($progress) }}٪</strong></div>
                    <div class="sdr-mini-track"><i style="width: {{ $progress }}%"></i></div>
                </div>
            </div>
        </section>

        <section class="sdr-kpis" aria-label="خلاصه وضعیت یادگیری">
            <article class="sdr-kpi blue">
                <i class="sdr-kpi-icon" aria-hidden="true">▤</i>
                <small>دوره‌های فعال</small>
                <strong>{{ App\Support\PersianUi::digits($activeCourses) }}</strong>
                <span>دوره‌هایی که اکنون در آن‌ها ثبت‌نام فعالی داری</span>
            </article>
            <article class="sdr-kpi green">
                <i class="sdr-kpi-icon" aria-hidden="true">◷</i>
                <small>جلسه‌های ثبت‌شده</small>
                <strong>{{ App\Support\PersianUi::digits($sessionCount) }}</strong>
                <span>جلسه‌های در بازهٔ نمایش داشبورد</span>
            </article>
            <article class="sdr-kpi purple">
                <i class="sdr-kpi-icon" aria-hidden="true">✓</i>
                <small>تکلیف‌های باز</small>
                <strong>{{ App\Support\PersianUi::digits($pending) }}</strong>
                <span>تکلیف‌هایی که هنوز ارسالی برایشان ثبت نشده</span>
            </article>
            <article class="sdr-kpi yellow">
                <i class="sdr-kpi-icon" aria-hidden="true">↗</i>
                <small>پیشرفت کلی</small>
                <strong>{{ App\Support\PersianUi::digits($progress) }}٪</strong>
                <span>{{ App\Support\PersianUi::digits($completedLessons) }} از {{ App\Support\PersianUi::digits($totalLessons) }} درس کامل</span>
            </article>
        </section>

        <div class="sdr-main-grid">
            <div class="sdr-main-column">
                <section class="sdr-card" aria-labelledby="sdr-courses-title">
                    <div class="sdr-card-head">
                        <div><span class="sdr-card-kicker">مسیر یادگیری</span><h2 id="sdr-courses-title">دوره‌های فعال من</h2></div>
                        <a class="sdr-card-link" href="{{ route('student.courses.index') }}">مشاهده همه <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="sdr-course-list">
                        @forelse($courses->take(6) as $course)
                            @php
                                $courseProgress = max(0, min(100, (float) ($course->learning_progress ?? 0)));
                                $next = $course->next_lesson;
                            @endphp
                            <article class="sdr-course-row">
                                <div class="sdr-course-mark" aria-hidden="true">{{ App\Support\PersianUi::digits($loop->iteration) }}</div>
                                <div class="sdr-course-copy">
                                    <small>{{ $course->academy?->name ?: 'آموزش شیخان' }}</small>
                                    <strong>{{ $course->title }}</strong>
                                    <div class="sdr-course-track" role="progressbar" aria-label="پیشرفت {{ $course->title }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($courseProgress) }}"><i style="width: {{ $courseProgress }}%"></i></div>
                                    <p>{{ App\Support\PersianUi::digits(round($courseProgress)) }}٪ پیشرفت · {{ $next?->title ?: 'مسیر در حال پیگیری' }}</p>
                                </div>
                                <a class="sdr-course-open" href="{{ $next ? route('student.lessons.show', $next->id) : route('student.courses.show', $course->id) }}">{{ $next ? 'ادامه' : 'مشاهده' }} <span aria-hidden="true">←</span></a>
                            </article>
                        @empty
                            <div class="sdr-muted-empty">هنوز دورهٔ فعالی برای حساب تو ثبت نشده است. با مشاهده دوره‌های در دسترس، مسیر یادگیری‌ات را شروع کن.</div>
                        @endforelse
                    </div>
                </section>

                <div class="sdr-two-grid">
                    <section class="sdr-card" aria-labelledby="sdr-activity-title">
                        <div class="sdr-card-head">
                            <div><span class="sdr-card-kicker">ریتم مطالعه</span><h2 id="sdr-activity-title">فعالیت ۷ روز اخیر</h2><p>زمان ثبت‌شدهٔ مطالعه در هر روز</p></div>
                            <strong class="sdr-activity-total">{{ App\Support\PersianUi::digits($studyMinutes) }} دقیقه</strong>
                        </div>
                        <div class="sdr-chart" role="img" aria-label="نمودار دقایق مطالعه در هفت روز اخیر">
                            @foreach($studyWeek as $day)
                                @php
                                    $minutes = max(0, (int) ($day['minutes'] ?? 0));
                                    $height = $minutes > 0 ? max(7, (int) round(($minutes / $maxStudy) * 100)) : 3;
                                @endphp
                                <div class="sdr-chart-day">
                                    <strong>{{ App\Support\PersianUi::digits($minutes) }}</strong>
                                    <span class="sdr-chart-track"><i style="height: {{ $height }}%"></i></span>
                                    <small>{{ $day['weekday'] ?? '—' }}</small>
                                </div>
                            @endforeach
                        </div>
                        <div class="sdr-chart-caption"><span>آمار بر پایهٔ فعالیت ثبت‌شده در سامانه است.</span><span>{{ App\Support\PersianUi::digits($studyStreak) }} روز پیوسته</span></div>
                    </section>

                    <section class="sdr-card" aria-labelledby="sdr-assignments-title">
                        <div class="sdr-card-head">
                            <div><span class="sdr-card-kicker">قدم بعدی</span><h2 id="sdr-assignments-title">تکلیف‌های من</h2></div>
                            <a class="sdr-card-link" href="{{ route('student.assignments.index') }}">همه <span aria-hidden="true">←</span></a>
                        </div>
                        <div class="sdr-item-list">
                            @forelse($assignments->take(5) as $assignment)
                                <a class="sdr-item-row" href="{{ route('student.assignments.show', $assignment->id) }}">
                                    <div><strong>{{ $assignment->title }}</strong><small>{{ $assignment->due_at ? 'موعد · '.App\Support\PersianUi::date($assignment->due_at) : 'بدون موعد' }}</small></div>
                                    <span class="sdr-item-mark {{ $assignment->submitted_at ? 'done' : '' }}">{{ $assignment->submitted_at ? '✓' : '→' }}</span>
                                </a>
                            @empty
                                <div class="sdr-muted-empty">در حال حاضر تکلیفِ قابل‌پیگیری‌ای برای نمایش وجود ندارد.</div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <div class="sdr-two-grid">
                    <section class="sdr-card" aria-labelledby="sdr-results-title">
                        <div class="sdr-card-head">
                            <div><span class="sdr-card-kicker">بازخورد آموزشی</span><h2 id="sdr-results-title">آخرین نتیجه‌ها</h2></div>
                            <a class="sdr-card-link" href="{{ route('student.results.index') }}">همه نتایج <span aria-hidden="true">←</span></a>
                        </div>
                        <div class="sdr-item-list">
                            @forelse($recentResults->take(5) as $result)
                                <article class="sdr-item-row">
                                    <div><strong>{{ $result->title }}</strong><small>{{ $result->status_label ?? 'ثبت‌شده' }} · {{ $result->occurred_at ? App\Support\PersianUi::date($result->occurred_at) : '—' }}</small></div>
                                    <span class="sdr-score {{ !empty($result->is_provisional) ? 'is-provisional' : '' }}">{{ $result->score === null ? '—' : App\Support\PersianUi::digits($result->score) }}</span>
                                </article>
                            @empty
                                <div class="sdr-muted-empty">هنوز نتیجه‌ای برای نمایش ثبت نشده است.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="sdr-card" aria-labelledby="sdr-resources-title">
                        <div class="sdr-card-head">
                            <div><span class="sdr-card-kicker">منابع مجاز</span><h2 id="sdr-resources-title">جزوه‌ها و فایل‌ها</h2></div>
                            <a class="sdr-card-link" href="{{ route('student.resources.index') }}">مشاهده همه <span aria-hidden="true">←</span></a>
                        </div>
                        <div class="sdr-item-list">
                            @forelse($resources as $resource)
                                <a class="sdr-resource-row" href="{{ route('student.resources.view', $resource->id) }}">
                                    <i class="sdr-resource-icon" aria-hidden="true">▤</i>
                                    <span><strong>{{ $resource->title }}</strong><small>{{ $resource->course?->title ?? 'منبع آموزشی' }}</small></span>
                                    <em aria-hidden="true">↗</em>
                                </a>
                            @empty
                                <div class="sdr-muted-empty">هنوز منبع اختصاصیِ قابل‌نمایشی برای دوره‌های تو منتشر نشده است.</div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <section class="sdr-card" aria-labelledby="sdr-shortcuts-title">
                    <div class="sdr-card-head">
                        <div><span class="sdr-card-kicker">همه ابزارها</span><h2 id="sdr-shortcuts-title">دسترسی سریع</h2></div>
                        <a class="sdr-card-link" href="{{ route('student.dashboard') }}">نمای کلی <span aria-hidden="true">←</span></a>
                    </div>
                    <div class="sdr-shortcut-grid">
                        @foreach($quickLinks as $link)
                            @if($studentUser->hasPermission($link['permission']))
                                <a class="sdr-shortcut" href="{{ route($link['route']) }}">
                                    <i aria-hidden="true">{{ $link['icon'] }}</i>
                                    <strong>{{ $link['label'] }}</strong>
                                    <small>{{ $link['description'] }}</small>
                                </a>
                            @endif
                        @endforeach
                        @if($studentUser->hasPermission('student_messages.view'))
                            <a class="sdr-shortcut" href="{{ route('student.messages.index') }}"><i aria-hidden="true">✉</i><strong>پیام‌های من</strong><small>اطلاعیه‌ها و پیام‌های آموزشی</small></a>
                        @endif
                        <a class="sdr-shortcut" href="{{ route('courses.index') }}"><i aria-hidden="true">▣</i><strong>دوره‌های عمومی</strong><small>دوره‌های منتشرشدهٔ سایت</small></a>
                        <a class="sdr-shortcut" href="{{ route('store.index') }}"><i aria-hidden="true">▤</i><strong>فروشگاه</strong><small>کتاب‌ها و محصولات سایت</small></a>
                        <a class="sdr-shortcut" href="{{ route('blog.index') }}"><i aria-hidden="true">✦</i><strong>مقالات</strong><small>محتوای آموزشی عمومی</small></a>
                    </div>
                </section>

                @if($sessions->isNotEmpty())
                    <section class="sdr-card" aria-labelledby="sdr-recordings-title">
                        <div class="sdr-card-head">
                            <div><span class="sdr-card-kicker">کلاس‌های آنلاین</span><h2 id="sdr-recordings-title">جلسه‌ها و آرشیو ضبط‌ها</h2><p>ضبط فقط زمانی باز می‌شود که دسترسی آن از طرف آموزشگاه مجاز و منتشر شده باشد.</p></div>
                            <a class="sdr-card-link" href="{{ route('student.live-classes.index') }}">همه جلسات <span aria-hidden="true">←</span></a>
                        </div>
                        @foreach($sessions->take(4) as $session)
                            <div class="sdr-session-row">
                                <span class="sdr-session-icon" aria-hidden="true">{{ $session['is_future'] ? '◷' : '▶' }}</span>
                                <div class="sdr-session-copy"><strong>{{ $session['title'] }}</strong><small>{{ $session['course'] ?: 'دوره آموزشی' }} · {{ $session['classroom'] ?: 'جلسه دوره' }}<br>{{ $session['date'] }} · {{ $session['time'] }}</small></div>
                                @if($session['available'] && $session['href'])
                                    <a class="sdr-status good" href="{{ $session['href'] }}">مشاهده ضبط</a>
                                @elseif($session['is_future'])
                                    <span class="sdr-status wait">در انتظار برگزاری</span>
                                @else
                                    <span class="sdr-status">ضبط منتشر نشده</span>
                                @endif
                            </div>
                        @endforeach
                    </section>
                @endif

                @if($upcomingMeetings->isNotEmpty())
                    <section class="sdr-card" aria-labelledby="sdr-meetings-title">
                        <div class="sdr-card-head"><div><span class="sdr-card-kicker">هماهنگی آموزشگاه</span><h2 id="sdr-meetings-title">جلسه‌های تأییدشده</h2></div></div>
                        @foreach($upcomingMeetings as $meeting)
                            <div class="sdr-session-row">
                                <span class="sdr-session-icon" aria-hidden="true">◷</span>
                                <div class="sdr-session-copy"><strong>{{ $meeting->title }}</strong><small>{{ $meeting->course_title }} · {{ $meeting->classroom_title ?: 'جلسه آموزشگاه' }}<br>{{ App\Support\PersianUi::date($meeting->scheduled_at) }} · {{ App\Support\PersianUi::time($meeting->scheduled_at) }}@if($meeting->message)<br>{{ $meeting->message }}@endif</small></div>
                                <span class="sdr-status good">تأییدشده</span>
                            </div>
                        @endforeach
                    </section>
                @endif
            </div>

            <aside class="sdr-rail">
                <section class="sdr-card" aria-labelledby="sdr-teachers-title">
                    <div class="sdr-card-head"><div><span class="sdr-card-kicker">همراهان یادگیری</span><h2 id="sdr-teachers-title">اساتید دوره‌های من</h2></div></div>
                    <div class="sdr-teacher-list">
                        @forelse($teachers as $teacherItem)
                            <article class="sdr-teacher-row">
                                <span class="sdr-teacher-avatar" aria-hidden="true">{{ mb_substr(trim($teacherItem->name), 0, 1) }}</span>
                                <div class="sdr-teacher-copy"><strong>{{ $teacherItem->name }}</strong><span>استاد مرتبط با کلاس‌های فعال تو</span></div>
                                <span class="sdr-teacher-tag">استاد</span>
                            </article>
                        @empty
                            <div class="sdr-muted-empty">هنوز استادی برای دوره‌های فعال تو ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>

                <section class="sdr-progress-widget" aria-label="میانگین پیشرفت">
                    <div class="sdr-progress-ring" style="--progress: {{ $progress }}%"><strong>{{ App\Support\PersianUi::digits($progress) }}٪</strong></div>
                    <div class="sdr-progress-copy"><strong>پیشرفت یادگیری</strong><p>میانگین درصد پیشرفت ثبت‌شده در دوره‌های فعال؛ نه یک امتیاز تخمینی.</p><a href="{{ route('student.courses.index') }}">ادامه مسیر ←</a></div>
                </section>

                <section class="sdr-card sdr-calendar" aria-labelledby="sdr-calendar-title">
                    <div class="sdr-card-head"><div><span class="sdr-card-kicker">امروز</span><h2 id="sdr-calendar-title">تقویم</h2></div><span class="sdr-card-link">{{ App\Support\PersianUi::date(now()) }}</span></div>
                    <div class="sdr-calendar-month">{{ $jalaliCalendar['month_label'] }}</div>
                    <div class="sdr-calendar-week"><span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span></div>
                    <div class="sdr-calendar-days">
                        @for($i = 0; $i < $firstWeekday; $i++)<i aria-hidden="true"></i>@endfor
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <span class="{{ $day === $jalaliCalendar['day'] ? 'today' : '' }}">{{ App\Support\PersianUi::digits($day) }}</span>
                        @endfor
                    </div>
                </section>

                <section class="sdr-card" aria-labelledby="sdr-schedule-title">
                    <div class="sdr-card-head"><div><span class="sdr-card-kicker">برنامه امروز</span><h2 id="sdr-schedule-title">کلاس‌های امروز</h2></div><a class="sdr-card-link" href="{{ route('student.live-classes.index') }}">جدول <span aria-hidden="true">←</span></a></div>
                    @forelse($todayClasses as $class)
                        <a class="sdr-schedule" href="{{ route('student.live-classes.index') }}">
                            <time>{{ App\Support\PersianUi::time($class->scheduled_at) }}</time>
                            <span><strong>{{ $class->title }}</strong><small>{{ $class->course_title }}</small></span>
                            <i aria-hidden="true">←</i>
                        </a>
                    @empty
                        <div class="sdr-muted-empty">برای امروز جلسه‌ای ثبت نشده است.</div>
                    @endforelse
                </section>

                <section class="sdr-card" aria-labelledby="sdr-reminders-title">
                    <div class="sdr-card-head"><div><span class="sdr-card-kicker">پیگیری</span><h2 id="sdr-reminders-title">یادآورها</h2></div></div>
                    <a class="sdr-reminder" href="{{ route('student.assignments.index') }}"><i class="sdr-reminder-icon" aria-hidden="true">◷</i><span><strong>{{ App\Support\PersianUi::digits($pending) }} تکلیف باز</strong><small>موعدها را بررسی کن</small></span><em aria-hidden="true">←</em></a>
                    <a class="sdr-reminder" href="{{ route('student.results.index') }}"><i class="sdr-reminder-icon" aria-hidden="true">◎</i><span><strong>آخرین نتیجه</strong><small>{{ $latestResult?->title ?: 'هنوز نتیجه‌ای ثبت نشده' }}</small></span><em aria-hidden="true">←</em></a>
                    <a class="sdr-reminder" href="{{ route('student.courses.index') }}"><i class="sdr-reminder-icon" aria-hidden="true">✓</i><span><strong>{{ App\Support\PersianUi::digits($progress) }}٪ پیشرفت</strong><small>درس بعدی را ادامه بده</small></span><em aria-hidden="true">←</em></a>
                </section>

                <section class="sdr-card" aria-labelledby="sdr-achievements-title">
                    <div class="sdr-card-head"><div><span class="sdr-card-kicker">دستاوردها</span><h2 id="sdr-achievements-title">موفقیت‌های آموزشی</h2></div><a class="sdr-card-link" href="{{ route('student.achievements.index') }}">همه <span aria-hidden="true">←</span></a></div>
                    @forelse($achievements as $achievement)
                        <div class="sdr-achievement"><i aria-hidden="true">★</i><span><strong>{{ $achievement->display_name ?: $achievement->title }}</strong><small>{{ $achievement->school_name ?: 'دستاورد ثبت‌شده در شیخان' }}</small></span></div>
                    @empty
                        <div class="sdr-muted-empty">{{ App\Support\PersianUi::digits((int) ($achievementCount ?? 0)) }} دستاورد منتشرشده برای حساب تو ثبت شده است.</div>
                    @endforelse
                </section>

                <section class="sdr-profile">
                    <img src="{{ asset('images/default-account-avatar.svg') }}" alt="" loading="lazy">
                    <div><span>حساب دانش‌آموز</span><strong>{{ $student->name }}</strong><small>{{ $student->studentProfile?->school_name ?: 'دانش‌آموز شیخان' }}</small></div>
                </section>
            </aside>
        </div>
    </div>
@endsection
