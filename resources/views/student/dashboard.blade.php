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
        $activeCourses = $courses->count();
        $sessionCount = $sessions->count();
        $resourceCount = $resources->count();
        $pending = (int) ($pendingAssignments ?? 0);
        $completedLessons = (int) ($completedLessonsCount ?? 0);
        $totalLessons = (int) ($totalLessonsCount ?? 0);
        $studyMinutes = (int) ($studyMinutesLast7Days ?? 0);
        $studyStreak = (int) ($studyStreak ?? 0);
        $jalaliCalendar = App\\Support\\PersianUi::calendar(now());
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

        <section class="student-ui-kpis" aria-label="خلاصه وضعیت یادگیری">
            <article>
                <span>دوره‌های فعال</span>
                <strong>{{ App\Support\PersianUi::digits($activeCourses) }}</strong>
                <small>دوره‌ای که اکنون دنبال می‌کنی</small>
                <i class="student-ui-kpi-icon is-blue">◉</i>
            </article>
            <article>
                <span>جلسات کلاس</span>
                <strong>{{ App\Support\PersianUi::digits($sessionCount) }}</strong>
                <small>جلسات ثبت‌شده در مسیرهای تو</small>
                <i class="student-ui-kpi-icon is-green">◎</i>
            </article>
            <article>
                <span>تکالیف من</span>
                <strong>{{ App\Support\PersianUi::digits($pending) }}</strong>
                <small>مورد نیازمند اقدام</small>
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

        <section class="student-ui-quick">
            <div class="student-ui-section-title">
                <div><span>دسترسی سریع</span>
                    <h2>Quick Links</h2></div>
                <a href="{{ route('student.dashboard') }}">خانه <span>←</span></a>
            </div>
            <div class="student-ui-quick-grid">
                @php
                    $quickLinks = [
                        ['route'=>'student.courses.index','label'=>'دوره‌های من','icon'=>'▣','permission'=>'courses.view'],
                        ['route'=>'student.live-classes.index','label'=>'کلاس‌های من','icon'=>'◷','permission'=>'live_classes.view'],
                        ['route'=>'student.assignments.index','label'=>'تکالیف من','icon'=>'✓','permission'=>'assignments.view'],
                        ['route'=>'student.exams.index','label'=>'آزمون‌ها','icon'=>'▤','permission'=>'exams.view'],
                        ['route'=>'student.results.index','label'=>'نتایج','icon'=>'↗','permission'=>'results.view'],
                        ['route'=>'student.resources.index','label'=>'جزوه‌ها و فایل‌های من','icon'=>'▤','permission'=>'resources.view'],
                        ['route'=>'student.notes.index','label'=>'یادداشت‌ها','icon'=>'✎','permission'=>'notes.view'],
                        ['route'=>'student.profile.edit','label'=>'حساب کاربری','icon'=>'○','permission'=>'profile.view'],
                    ];
                @endphp
                @foreach($quickLinks as $link)
                    @if($student->hasPermission($link['permission']))
                        <a href="{{ route($link['route']) }}">
                            <i>{{ $link['icon'] }}</i>
                            <span>{{ $link['label'] }}</span>
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
                            <div><span>ریتم مطالعه</span>
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
                                <div class="student-ui-empty">تکلیف بازی برای نمایش نیست.</div>
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
