@extends('layouts.student')

@section('title', 'داشبورد دانش‌آموز | شیخان')
@section('header-title', 'داشبورد دانش‌آموز')

@section('content')
    @php
        $progress = max(0, min(100, (float) $overallProgress));
        $today = now()->startOfDay();
        $todayClasses = collect($upcomingLiveClasses ?? [])->filter(
            fn ($class) => $class->scheduled_at && $class->scheduled_at->isSameDay($today)
        )->values();
        $nextAssignment = collect($assignments ?? [])->first(fn ($item) => empty($item->submitted_at));
        $latestResult = collect($recentResults ?? [])->first();
        $topCourse = collect($courses ?? [])->sortByDesc('learning_progress')->first();
        $activeCourses = (int) ($activeCourseCount ?? $courses->count());
        $sessionCount = $sessions->count();
        $resourceCount = $resources->count();
        $pending = (int) ($pendingAssignments ?? 0);
        $completedLessons = (int) ($completedLessonsCount ?? 0);
        $totalLessons = (int) ($totalLessonsCount ?? 0);
        $studyMinutes = (int) ($studyMinutesLast7Days ?? 0);
        $studyStreak = (int) ($studyStreak ?? 0);
        $jalaliCalendar = App\Support\PersianUi::calendar(now());
        $daysInMonth = $jalaliCalendar['days_in_month'];
        $firstWeekday = $jalaliCalendar['first_weekday'];
        $maxStudy = max(1, collect($studyWeek ?? [])->max('minutes'));
    @endphp

    <div class="student-dashboard-ui">
        <section class="student-ui-hero">
            <div class="student-ui-hero-copy">
                <span class="student-ui-eyebrow">خانه یادگیری من</span>
                <h2>سلام {{ $student->name }} 👋</h2>
                <p>
                    مسیر یادگیری‌ات از همین‌جا ادامه پیدا می‌کند؛
                    {{ App\Support\PersianUi::digits($activeCourses) }} دوره فعال،
                    {{ App\Support\PersianUi::digits($pending) }} کار باز
                    و {{ App\Support\PersianUi::digits($progress) }}٪ پیشرفت داری.
                </p>
                <div class="student-ui-hero-actions">
                    @if($nextAssignment)
                        <a href="{{ route('student.assignments.show', $nextAssignment->id) }}"
                           class="student-ui-primary">
                            ادامه تکلیف <span>←</span>
                        </a>
                    @elseif($topCourse?->next_lesson)
                        <a href="{{ route('student.lessons.show', $topCourse->next_lesson->id) }}"
                           class="student-ui-primary">
                            ادامه یادگیری <span>←</span>
                        </a>
                    @else
                        <a href="{{ route('student.courses.index') }}" class="student-ui-primary">
                            دیدن دوره‌های من <span>←</span>
                        </a>
                    @endif
                    <a href="{{ route('student.courses.index') }}" class="student-ui-secondary">مسیرهای یادگیری</a>
                </div>
                <div class="student-ui-hero-meta">
                    <span><b>{{ App\Support\PersianUi::digits($studyStreak) }}</b> روز پیوسته</span>
                    <span><b>{{ App\Support\PersianUi::digits($studyMinutes) }}</b> دقیقه در ۷ روز</span>
                    <span><b>{{ App\Support\PersianUi::digits($completedLessons) }}</b> درس کامل</span>
                </div>
            </div>
            <div class="student-ui-hero-art">
                <div class="student-ui-hero-glow"></div>
                <img src="{{ asset('images/default-account-avatar.svg') }}" alt="" loading="lazy">
                <span class="student-ui-hero-badge">شیخان · مسیر تو</span>
            </div>
        </section>

        @if(collect($sessions ?? [])->isNotEmpty())
            <section class="student-workspace-card mt-5" aria-labelledby="student-recordings-title">
                <div class="student-workspace-card-head">
                    <div>
                        <h2 id="student-recordings-title">جلسه‌های من و آرشیو ضبط‌ها</h2>
                        <p>ضبط‌های منتشرشده با چراغ روشن آمادهٔ مشاهده‌اند؛ جلسه‌های دیگر تا انتشار مجاز قفل می‌مانند.</p>
                    </div>
                    <a class="student-workspace-btn secondary" href="{{ route('student.live-classes.index') }}">همه جلسات</a>
                </div>
                <div class="student-workspace-list">
                    @foreach(collect($sessions)->take(8) as $session)
                        <article class="student-workspace-row">
                            <div class="student-workspace-date">
                                <strong>{{ $session['is_future'] ? '◷' : '▶' }}</strong>
                                <small>{{ $session['is_future'] ? 'آینده' : 'آرشیو' }}</small>
                            </div>
                            <div class="student-workspace-row-main">
                                <strong>{{ $session['title'] }}</strong>
                                <span>{{ $session['course'] ?: 'دوره آموزشی' }} · {{ $session['classroom'] ?: 'جلسه دوره' }}</span>
                                <span>{{ $session['date'] }} · {{ $session['time'] }}</span>
                            </div>
                            <div class="student-workspace-actions">
                                @if($session['available'] && $session['href'])
                                    <span class="student-workspace-status success">● چراغ روشن</span>
                                    <a class="student-workspace-btn primary" href="{{ $session['href'] }}">مشاهده ضبط</a>
                                @else
                                    <span class="student-workspace-status warning">○ چراغ خاموش</span>
                                    <small class="max-w-48 text-xs leading-6 text-slate-500">
                                        @if($session['is_future'])
                                            این جلسه هنوز برگزار نشده است.
                                        @elseif(in_array($session['status'], ['completed','published'], true))
                                            ضبط جلسه هنوز منتشر نشده یا زمان دسترسی آن نرسیده است.
                                        @else
                                            ضبطی برای این جلسه منتشر نشده است.
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="student-focus-card" data-student-focus aria-labelledby="student-focus-title">
            <div class="student-focus-art" aria-hidden="true">
                <span class="student-focus-spark s1">✦</span>
                <span class="student-focus-spark s2">•</span>
                <span class="student-focus-ring"></span>
                <strong>{{ App\Support\PersianUi::digits($progress) }}٪</strong>
            </div>

            <div class="student-focus-copy">
                <span class="student-focus-kicker">چالش امروز</span>
                <h2 id="student-focus-title">
                    @if($nextAssignment)
                        اول این تکلیف را جمع کنیم 🎯
                    @elseif($topCourse?->next_lesson)
                        وقت ادامه مسیر یادگیریه 🚀
                    @elseif($latestResult)
                        نتیجه‌ات را ببین و قدم بعدی را بردار ✨
                    @else
                        یک مسیر برای امروز انتخاب کن 🌱
                    @endif
                </h2>
                <p>
                    @if($nextAssignment)
                        «{{ $nextAssignment->title }}» هنوز باز است.
                    @elseif($topCourse?->next_lesson)
                        درس بعدی تو «{{ $topCourse->next_lesson->title }}» است.
                    @elseif($latestResult)
                        آخرین نتیجه‌ات را بررسی کن و ببین چه چیزی را بهتر می‌توانی ادامه بدهی.
                    @else
                        لازم نیست همه‌چیز را یک‌جا انجام بدهی؛ یک قدم کوچک کافی است.
                    @endif
                </p>

                <a
                    href="{{ $nextAssignment ? route('student.assignments.show', $nextAssignment->id) : ($topCourse?->next_lesson ? route('student.lessons.show', $topCourse->next_lesson->id) : ($latestResult ? route('student.results.index') : route('student.courses.index'))) }}"
                    class="student-focus-action"
                >
                    {{ $nextAssignment ? 'ادامه تکلیف' : ($topCourse?->next_lesson ? 'ادامه درس' : ($latestResult ? 'دیدن نتیجه' : 'انتخاب دوره')) }}
                    <span aria-hidden="true">←</span>
                </a>
            </div>

            <div class="student-focus-progress" aria-label="روش محاسبه پیشرفت یادگیری">
                <span>میانگین پیشرفت دوره‌ها</span>
                <div
                    class="student-focus-progress-track"
                    role="progressbar"
                    aria-label="میانگین پیشرفت دوره‌های فعال"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="{{ $progress }}"
                >
                    <i style="width: {{ $progress }}%"></i>
                </div>
                <small>
                    {{ App\Support\PersianUi::digits($completedLessons) }} از {{ App\Support\PersianUi::digits($totalLessons) }} درس کامل
                </small>
                <small class="student-progress-method">
                    محاسبه بر پایه میانگین درصد پیشرفت ثبت‌شده در دوره‌های فعال است؛ هر درسِ منتشرشده که پیشرفت آن به ۱۰۰٪ برسد، کامل حساب می‌شود.
                </small>
            </div>
        </section>

        <section class="student-ui-kpis" aria-label="خلاصه وضعیت یادگیری">
            <article>
                <span>دوره‌های فعال</span>
                <strong>{{ App\Support\PersianUi::digits($activeCourses) }}</strong>
                <small>دوره‌هایی که اکنون در آن‌ها فعال هستی</small>
                <i class="student-ui-kpi-icon is-blue">◉</i>
            </article>
            <article>
                <span>جلسات کلاس</span>
                <strong>{{ App\Support\PersianUi::digits($sessionCount) }}</strong>
                <small>جلسات و کلاس‌های برنامه‌ریزی‌شده</small>
                <i class="student-ui-kpi-icon is-green">◎</i>
            </article>
            <article>
                <span>تکالیف من</span>
                <strong>{{ App\Support\PersianUi::digits($pending) }}</strong>
                <small>تکلیف‌هایی که هنوز باید پیگیری کنی</small>
                <i class="student-ui-kpi-icon is-purple">✓</i>
            </article>
            <article>
                <span>پیشرفت کلی</span>
                <strong>{{ App\Support\PersianUi::digits($progress) }}٪</strong>
                <small>{{ App\Support\PersianUi::digits($completedLessons) }}
                    از {{ App\Support\PersianUi::digits($totalLessons) }} درس کامل</small>
                <i class="student-ui-kpi-icon is-gold">↗</i>
            </article>
        </section>

        <section class="student-ui-task-board" aria-labelledby="student-task-board-title">
            <div class="student-ui-board-head"><div><span>قدم بعدی</span><h2 id="student-task-board-title">الان روی چه چیزی تمرکز کنم؟</h2></div><a href="{{ route('student.assignments.index') }}">همه کارها <span>←</span></a></div>
            <div class="student-ui-board-cards">
                @forelse($assignments->take(3) as $assignment)
                    <a class="student-ui-board-card is-purple" href="{{ route('student.assignments.show', $assignment->id) }}">
                        <div class="student-ui-board-icon">✓</div>
                        <div class="student-ui-board-copy"><small>{{ $assignment->submitted_at ? 'ارسال شده' : 'تکلیف باز' }}</small><strong>{{ $assignment->title }}</strong><span>{{ $assignment->due_at ? 'موعد · '.App\Support\PersianUi::date($assignment->due_at) : 'بدون موعد' }}</span></div>
                        <b>{{ $assignment->submitted_at ? '✓' : '→' }}</b>
                    </a>
                @empty
                    <a class="student-ui-board-card is-blue" href="{{ route('student.courses.index') }}">
                        <div class="student-ui-board-icon">▣</div>
                        <div class="student-ui-board-copy"><small>مسیر یادگیری</small><strong>{{ $topCourse?->title ?: 'دوره‌های من' }}</strong><span>{{ $topCourse ? App\Support\PersianUi::digits(round($topCourse->learning_progress ?? 0)).'٪ پیشرفت' : 'یک دوره را برای شروع انتخاب کن' }}</span></div>
                        <b>→</b>
                    </a>
                @endforelse
            </div>
        </section>

        <section class="student-ui-people-panel">
            <div class="student-ui-board-head"><div><span>آدم‌های مسیر یادگیری</span><h2>اساتید دوره‌های من</h2></div><a href="{{ route('student.courses.index') }}">دوره‌ها <span>←</span></a></div>
            <div class="student-ui-people-grid">
                @forelse(($teachers ?? collect()) as $teacherItem)
                    <article class="student-ui-person-card">
                        <img src="{{ asset('images/default-account-avatar.svg') }}" alt="" loading="lazy">
                        <div><strong>{{ $teacherItem->name }}</strong><span>استاد دوره‌های تو</span></div>
                        <b>✓</b>
                    </article>
                @empty
                    <div class="student-ui-empty">هنوز استادی برای دوره‌های فعال تو ثبت نشده است.</div>
                @endforelse
            </div>
        </section>

        <section class="student-ui-quick">
            <div class="student-ui-section-title">
                <div><span>مسیرهای اصلی</span>
                    <h2>هر کاری که برای یادگیری لازم داری</h2></div>
                <a href="{{ route('student.dashboard') }}">نمای کلی <span>←</span></a>
            </div>
            <div class="student-ui-quick-grid">
                @php
                    $quickLinks = [
                        ['route'=>'student.courses.index','label'=>'دوره‌های من','description'=>'ادامه درس‌ها و دیدن درصد پیشرفت','icon'=>'▤','permission'=>'courses.view'],
                        ['route'=>'student.live-classes.index','label'=>'کلاس‌های من','description'=>'برنامه کلاس‌های زنده و جلسات','icon'=>'◉','permission'=>'live_classes.view'],
                        ['route'=>'student.assignments.index','label'=>'تکالیف من','description'=>'کارهای باز، موعدها و ارسال‌ها','icon'=>'✎','permission'=>'assignments.view'],
                        ['route'=>'student.exams.index','label'=>'آزمون‌ها','description'=>'آزمون‌های منتشرشده و شروع آزمون','icon'=>'▣','permission'=>'exams.view'],
                        ['route'=>'student.results.index','label'=>'نمرات و عملکرد','description'=>'نتایج، نمره‌ها و بازخوردها','icon'=>'↗','permission'=>'results.view'],
                        ['route'=>'student.resources.index','label'=>'جزوه‌ها و منابع','description'=>'فایل‌ها و محتوای اختصاصی تو','icon'=>'▤','permission'=>'resources.view'],
                        ['route'=>'student.notes.index','label'=>'یادداشت‌ها','description'=>'یادداشت‌های شخصی مسیر یادگیری','icon'=>'✎','permission'=>'notes.view'],
                        ['route'=>'student.attendance.index','label'=>'حضور و غیاب','description'=>'سوابق حضور در کلاس‌های تو','icon'=>'◷','permission'=>'attendance.view'],
                        ['route'=>'student.achievements.index','label'=>'دستاوردها','description'=>'مدال‌ها و موفقیت‌های آموزشی','icon'=>'★','permission'=>'achievements.view'],
                        ['route'=>'student.profile.edit','label'=>'حساب کاربری','description'=>'اطلاعات حساب و تنظیمات شخصی','icon'=>'○','permission'=>'profile.view'],
                    ];
                @endphp
                @foreach($quickLinks as $link)
                    @if($student->hasPermission($link['permission']))
                        <a href="{{ route($link['route']) }}">
                            <i>{{ $link['icon'] }}</i>
                            <span>{{ $link['label'] }}</span><small>{{ $link['description'] }}</small>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        <div class="student-ui-main-grid">
            <div class="student-ui-main-column">
                <section class="student-ui-panel">
                    <div class="student-ui-panel-head">
                        <div><span>مسیر یادگیری</span>
                            <h2>دوره‌های فعال</h2></div>
                        <a href="{{ route('student.courses.index') }}">مشاهده همه <span>←</span></a>
                    </div>

                    <div class="student-ui-course-list">
                        @forelse($courses->take(4) as $course)
                            @php
                                $courseProgress = max(0, min(100, (float) ($course->learning_progress ?? 0)));
                                $next = $course->next_lesson;
                            @endphp
                            <article class="student-ui-course">
                                <div
                                    class="student-ui-course-index">{{ App\Support\PersianUi::digits($loop->iteration) }}</div>
                                <div class="student-ui-course-copy">
                                    <small>{{ $course->academy?->name ?: 'آموزش شیخان' }}</small>
                                    <h3>{{ $course->title }}</h3>
                                    <div class="student-ui-progress"><span style="width:{{ $courseProgress }}%"></span>
                                    </div>
                                    <p>{{ App\Support\PersianUi::digits(round($courseProgress)) }}٪ پیشرفت
                                        · {{ $next?->title ?: 'مسیر در حال پیگیری' }}</p>
                                </div>
                                <a href="{{ $next ? route('student.lessons.show', $next->id) : route('student.courses.show', $course->id) }}">
                                    {{ $next ? 'ادامه' : 'مشاهده' }} <span>←</span>
                                </a>
                            </article>
                        @empty
                            <div class="student-ui-empty">هنوز دوره فعالی برای نمایش وجود ندارد.</div>
                        @endforelse
                    </div>
                </section>

                <div class="student-ui-split">
                    <section class="student-ui-panel">
                        <div class="student-ui-panel-head">
                            <div><span>ریتم یادگیری</span>
                                <h2>فعالیت این هفته</h2></div>
                            <strong>{{ App\Support\PersianUi::digits($studyMinutes) }} دقیقه</strong>
                        </div>
                        <div class="student-ui-chart">
                            @foreach(($studyWeek ?? []) as $day)
                                @php $height = $day['minutes'] ? max(10, round(($day['minutes'] / $maxStudy) * 100)) : 5; @endphp
                                <div>
                                    <b>{{ $day['minutes'] ? App\Support\PersianUi::digits($day['minutes']) : '۰' }}</b>
                                    <span><i style="height:{{ $height }}%"></i></span>
                                    <small>{{ $day['weekday'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="student-ui-panel">
                        <div class="student-ui-panel-head">
                            <div><span>عملیات نزدیک</span>
                                <h2>تکالیف من</h2></div>
                            <a href="{{ route('student.assignments.index') }}">همه <span>←</span></a>
                        </div>
                        <div class="student-ui-task-list">
                            @forelse($assignments->take(4) as $assignment)
                                <a href="{{ route('student.assignments.show', $assignment->id) }}">
                                    <div>
                                        <strong>{{ $assignment->title }}</strong><small>{{ $assignment->due_at ? App\Support\PersianUi::date($assignment->due_at) : 'بدون موعد' }}</small>
                                    </div>
                                    <b class="{{ $assignment->submitted_at ? 'done' : '' }}">{{ $assignment->submitted_at ? '✓' : '→' }}</b>
                                </a>
                            @empty
                                <div class="student-ui-empty">تکلیف فعالی برای نمایش نیست.</div>
                            @endforelse
                        </div>
                    </section>
                </div>

                <section class="student-ui-panel">
                    <div class="student-ui-panel-head">
                        <div><span>بازخورد</span>
                            <h2>آخرین نتیجه‌ها</h2></div>
                        <a href="{{ route('student.results.index') }}">همه نتایج <span>←</span></a>
                    </div>
                    <div class="student-ui-results">
                        @forelse($recentResults as $result)
                            <article>
                                <strong>{{ $result->score === null ? '—' : App\Support\PersianUi::digits($result->score) }}</strong>
                                <div><b>{{ $result->title }}</b><span>{{ $result->status_label ?? 'ثبت‌شده' }}</span>
                                </div>
                                <small>{{ $result->occurred_at ? App\Support\PersianUi::date($result->occurred_at) : '—' }}</small>
                            </article>
                        @empty
                            <div class="student-ui-empty">هنوز نتیجه‌ای برای نمایش نداریم.</div>
                        @endforelse
                    </div>
                </section>

                <section class="student-ui-panel">
                    <div class="student-ui-panel-head">
                        <div><span>منابع</span>
                            <h2>جزوه‌ها و فایل‌های من</h2></div>
                        <a href="{{ route('student.resources.index') }}">مشاهده همه <span>←</span></a>
                    </div>
                    <div class="student-ui-resource-grid">
                        @forelse($resources as $resource)
                            <a href="{{ route('student.resources.view', $resource->id) }}">
                                <i>▤</i>
                                <div>
                                    <b>{{ $resource->title }}</b><span>{{ $resource->course?->title ?? 'منبع آموزشی' }}</span>
                                </div>
                                <em>↗</em>
                            </a>
                        @empty
                            <div class="student-ui-empty">هنوز منبع آموزشی اختصاصی برایت ثبت نشده است.</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="student-ui-rail">
                <section class="student-ui-calendar">
                    <div class="student-ui-rail-head"><h2>تقویم</h2><span>{{ App\Support\PersianUi::date(now()) }}</span>
                    </div>
                    <div class="student-ui-calendar-month">{{ $jalaliCalendar['month_label'] }}</div>
                    <div class="student-ui-calendar-week"><span>ش</span><span>ی</span><span>د</span><span>س</span><span>چ</span><span>پ</span><span>ج</span>
                    </div>
                    <div class="student-ui-calendar-days">
                        @for($i=0;$i<$firstWeekday;$i++)<i></i>@endfor
                        @for($day=1;$day<=$daysInMonth;$day++)
                            <span
                                class="{{ $day === $jalaliCalendar['day'] ? 'today' : '' }}">{{ App\Support\PersianUi::digits($day) }}</span>
                        @endfor
                    </div>
                </section>

                <section class="student-ui-rail-panel">
                    <div class="student-ui-rail-head"><h2>برنامه امروز</h2><a
                            href="{{ route('student.live-classes.index') }}">جدول</a></div>
                    @forelse($todayClasses as $class)
                        <a class="student-ui-schedule" href="{{ route('student.live-classes.index') }}">
                            <time>{{ App\Support\PersianUi::time($class->scheduled_at) }}</time>
                            <div><b>{{ $class->title }}</b><span>{{ $class->course_title }}</span></div>
                            <i>→</i>
                        </a>
                    @empty
                        <div class="student-ui-rail-empty">امروز جلسه کلاسی ثبت نشده است.</div>
                    @endforelse
                </section>

                <section class="student-ui-rail-panel">
                    <div class="student-ui-rail-head"><h2>یادآورها</h2></div>
                    <a class="student-ui-reminder" href="{{ route('student.assignments.index') }}"><i>◷</i>
                        <div><b>{{ App\Support\PersianUi::digits($pending) }} تکلیف
                                باز</b><span>قبل از موعد بررسی کن</span></div>
                        <em>→</em></a>
                    <a class="student-ui-reminder" href="{{ route('student.results.index') }}"><i>◎</i>
                        <div><b>آخرین نتیجه</b><span>{{ $latestResult?->title ?: 'هنوز نتیجه‌ای ثبت نشده' }}</span>
                        </div>
                        <em>→</em></a>
                    <a class="student-ui-reminder" href="{{ route('student.courses.index') }}"><i>✓</i>
                        <div><b>پیشرفت {{ App\Support\PersianUi::digits($progress) }}٪</b><span>مسیرت را ادامه بده</span>
                        </div>
                        <em>→</em></a>
                </section>

                <section class="student-ui-rail-profile">
                    <img src="{{ asset('images/default-account-avatar.svg') }}" alt="" loading="lazy">
                    <div>
                        <span>حساب دانش‌آموز</span><strong>{{ $student->name }}</strong><small>{{ $student->studentProfile?->school_name ?: 'دانش‌آموز شیخان' }}</small>
                    </div>
                </section>
            </aside>
        </div>
    </div>
@endsection
