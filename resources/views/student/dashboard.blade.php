@extends('layouts.student')

@section('title', 'داشبورد دانش‌آموز | شیخان')
@section('header-title', 'خانه یادگیری من')

@section('content')
    @php
        $progress = max(0, min(100, (float) $overallProgress));
        $coursesCount = $courses->count();
        $sessionsCount = $sessions->count();
        $resultsCount = $recentResults->count();
        $completedLessons = (int) ($completedLessonsCount ?? 0);
        $totalLessons = max(0, (int) ($totalLessonsCount ?? 0));
        $completionRate = $totalLessons > 0
            ? (int) round(($completedLessons / $totalLessons) * 100)
            : $progress;
        $studyMinutes = (int) ($studyMinutesLast7Days ?? 0);
        $studyStreak = (int) ($studyStreak ?? 0);
        $studyWeek = $studyWeek ?? [];

        $nextAssignment = collect($assignments ?? [])
            ->first(fn ($assignment) => empty($assignment->submitted_at));

        $latestResult = $recentResults->first();
        $topCourse = $courses
            ->sortByDesc('learning_progress')
            ->first();

        $focusType = 'learning';
        $focusTitle = 'یک قدم از مسیرت را جلو ببر';
        $focusText = 'با ادامه‌ی یک درس، مسیر یادگیری امروزت را شروع کن.';
        $focusHref = route('student.courses.index');
        $focusLabel = 'رفتن به دوره‌های من';

        if ($nextAssignment) {
            $focusType = 'task';
            $focusTitle = 'یک کار نیمه‌تمام داری';
            $focusText = 'تکلیف «' . $nextAssignment->title . '» هنوز تحویل نشده است.';
            $focusHref = route('student.assignments.show', $nextAssignment->id);
            $focusLabel = 'انجام تکلیف';
        } elseif ($nextLesson) {
            $focusType = 'lesson';
            $focusTitle = 'بهترین قدم بعدی مشخص است';
            $focusText = 'درس «' . $nextLesson->title . '» ادامه‌ی طبیعی مسیر یادگیری توست.';
            $focusHref = route('student.lessons.show', $nextLesson->id);
            $focusLabel = 'ادامه درس';
        } elseif ($nextLiveClass) {
            $focusType = 'class';
            $focusTitle = 'برای کلاس بعدی آماده شو';
            $focusText = 'جلسه «' . $nextLiveClass->title . '» به‌زودی برگزار می‌شود.';
            $focusHref = route('student.live-classes.index');
            $focusLabel = 'دیدن برنامه کلاس';
        }

        $maxStudyMinutes = max(1, collect($studyWeek)->max('minutes'));
    @endphp

    <div class="student-dashboard-v2">
        {{-- =======================================================
            HEADER / MOTIVATION
        ======================================================== --}}
        <section class="student-v2-hero" aria-labelledby="student-dashboard-title">
            <div class="student-v2-hero-grid">
                <div class="student-v2-hero-copy">
                    <span class="student-v2-eyebrow">
                        مرکز فرماندهی یادگیری
                    </span>

                    <h1 id="student-dashboard-title">
                        سلام {{ $student->name }}،
                        <span>امروز فقط یک قدم.</span>
                    </h1>

                    <p>
                        لازم نیست همه‌چیز را یک‌جا انجام بدهی.
                        شیخان اینجاست تا قدم بعدی را روشن کند و مسیر پیشرفتت را قابل دیدن نگه دارد.
                    </p>

                    <div class="student-v2-hero-actions">
                        <a href="{{ $focusHref }}" class="student-v2-primary-action">
                            <span>{{ $focusLabel }}</span>
                            <i aria-hidden="true">←</i>
                        </a>

                        <a href="{{ route('student.courses.index') }}" class="student-v2-ghost-action">
                            دیدن مسیرهای یادگیری
                        </a>
                    </div>

                    <div class="student-v2-hero-signals">
                        <span>
                            <b>{{ \App\Support\PersianUi::digits($studyStreak) }}</b>
                            روز پشت‌سرهم
                        </span>
                        <span>
                            <b>{{ \App\Support\PersianUi::digits($studyMinutes) }}</b>
                            دقیقه مطالعه در ۷ روز
                        </span>
                        <span>
                            <b>{{ \App\Support\PersianUi::digits($completedLessons) }}</b>
                            درس کامل‌شده
                        </span>
                    </div>
                </div>

                <div class="student-v2-hero-focus">
                    <div class="student-v2-focus-orb"></div>

                    <div class="student-v2-focus-card is-{{ $focusType }}">
                        <div class="student-v2-focus-top">
                            <span>تمرکز امروز</span>
                            <b>{{ \App\Support\PersianUi::digits($completionRate) }}٪</b>
                        </div>

                        <div
                            class="student-v2-progress-ring"
                            style="--ring-progress: {{ $completionRate }}%;"
                            aria-label="درصد تکمیل مسیر یادگیری"
                        >
                            <div>
                                <strong>{{ \App\Support\PersianUi::digits($completionRate) }}٪</strong>
                                <span>پیشرفت</span>
                            </div>
                        </div>

                        <strong class="student-v2-focus-title">{{ $focusTitle }}</strong>
                        <p>{{ $focusText }}</p>

                        <a href="{{ $focusHref }}" class="student-v2-focus-link">
                            {{ $focusLabel }}
                            <span aria-hidden="true">←</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- =======================================================
            SIGNAL CARDS
        ======================================================== --}}
        <section class="student-v2-stats" aria-label="تصویر کلی پیشرفت">
            <article class="student-v2-stat">
                <div class="student-v2-stat-icon is-indigo" aria-hidden="true">◉</div>
                <div>
                    <span>دوره‌های فعال</span>
                    <strong>{{ \App\Support\PersianUi::digits($coursesCount) }}</strong>
                    <small>مسیرهایی که اکنون دنبال می‌کنی</small>
                </div>
            </article>

            <article class="student-v2-stat">
                <div class="student-v2-stat-icon is-teal" aria-hidden="true">↗</div>
                <div>
                    <span>میانگین پیشرفت</span>
                    <strong>{{ \App\Support\PersianUi::digits($progress) }}٪</strong>
                    <small>برآیند دوره‌های فعال تو</small>
                </div>
            </article>

            <article class="student-v2-stat">
                <div class="student-v2-stat-icon is-coral" aria-hidden="true">✓</div>
                <div>
                    <span>کارهای باز</span>
                    <strong>{{ \App\Support\PersianUi::digits($pendingAssignments) }}</strong>
                    <small>تکلیف نیازمند اقدام</small>
                </div>
            </article>

            <article class="student-v2-stat">
                <div class="student-v2-stat-icon is-gold" aria-hidden="true">★</div>
                <div>
                    <span>آخرین نتیجه</span>
                    <strong>{{ $latestResult?->score === null ? '—' : \App\Support\PersianUi::digits($latestResult->score) }}</strong>
                    <small>{{ $latestResult?->title ?: 'هنوز نتیجه‌ای ثبت نشده' }}</small>
                </div>
            </article>
        </section>

        {{-- =======================================================
            NEXT MOVE / PATH
        ======================================================== --}}
        <section class="student-v2-section" aria-labelledby="student-next-move-title">
            <div class="student-v2-section-head">
                <div>
                    <span>قدم بعدی</span>
                    <h2 id="student-next-move-title">از اینجا ادامه بده</h2>
                    <p>سه چیزی که همین حالا بیشترین ارزش را برای مسیرت دارند.</p>
                </div>
                <span class="student-v2-section-index">۰۱</span>
            </div>

            <div class="student-v2-next-grid">
                <a href="{{ $nextLesson ? route('student.lessons.show', $nextLesson->id) : route('student.courses.index') }}" class="student-v2-next-card is-learning">
                    <div class="student-v2-next-icon">→</div>
                    <div class="student-v2-next-copy">
                        <span>ادامه یادگیری</span>
                        <strong>{{ $nextLesson?->title ?: 'اولین درس دوره‌ات را شروع کن' }}</strong>
                        <p>
                            {{ $topCourse?->title ?: 'دوره‌های فعال تو' }}
                        </p>
                    </div>
                    <i aria-hidden="true">←</i>
                </a>

                <a href="{{ $nextAssignment ? route('student.assignments.show', $nextAssignment->id) : route('student.assignments.index') }}" class="student-v2-next-card is-task">
                    <div class="student-v2-next-icon">✓</div>
                    <div class="student-v2-next-copy">
                        <span>کار بعدی</span>
                        <strong>{{ $nextAssignment?->title ?: 'تکلیف جدیدی منتظر تو نیست' }}</strong>
                        <p>
                            {{ $nextAssignment ? 'این مورد هنوز تحویل نشده است.' : 'وضعیت تکالیف تو مرتب است.' }}
                        </p>
                    </div>
                    <i aria-hidden="true">←</i>
                </a>

                <a href="{{ route('student.live-classes.index') }}" class="student-v2-next-card is-class">
                    <div class="student-v2-next-icon">●</div>
                    <div class="student-v2-next-copy">
                        <span>کلاس بعدی</span>
                        <strong>{{ $nextLiveClass?->title ?: 'برنامه‌ای برای کلاس بعدی نیست' }}</strong>
                        <p>
                            @if($nextLiveClass)
                                {{ \App\Support\PersianUi::date($nextLiveClass->scheduled_at) }}
                                ·
                                {{ \App\Support\PersianUi::time($nextLiveClass->scheduled_at) }}
                            @else
                                برنامه کلاس‌ها را بررسی کن.
                            @endif
                        </p>
                    </div>
                    <i aria-hidden="true">←</i>
                </a>
            </div>
        </section>

        {{-- =======================================================
            LEARNING PATH
        ======================================================== --}}
        <section class="student-v2-section" aria-labelledby="student-learning-path-title">
            <div class="student-v2-section-head">
                <div>
                    <span>مسیر یادگیری</span>
                    <h2 id="student-learning-path-title">دوره‌هایت را مثل یک مسیر ببین</h2>
                    <p>هر دوره از یک «شروع» به یک «قدم بعدی» تبدیل شده است؛ فقط ادامه بده.</p>
                </div>

                <a href="{{ route('student.courses.index') }}" class="student-v2-section-link">
                    همه دوره‌ها <span aria-hidden="true">←</span>
                </a>
            </div>

            <div class="student-v2-course-path">
                @forelse($courses as $index => $course)
                    @php
                        $courseProgress = max(0, min(100, (float) ($course->learning_progress ?? 0)));
                        $courseTotal = (int) ($course->total_lessons ?? 0);
                        $courseCompleted = (int) ($course->completed_lessons ?? 0);
                        $lesson = $course->next_lesson;
                    @endphp

                    <article class="student-v2-course-card">
                        <div class="student-v2-course-top">
                            <div class="student-v2-course-number">{{ \App\Support\PersianUi::digits($index + 1) }}</div>
                            <div class="student-v2-course-heading">
                                <span>{{ $course->academy?->name ?: 'آموزش شیخان' }}</span>
                                <h3>{{ $course->title }}</h3>
                            </div>
                            <span class="student-v2-course-percent">
                                {{ \App\Support\PersianUi::digits(round($courseProgress)) }}٪
                            </span>
                        </div>

                        <div class="student-v2-course-progress">
                            <div class="student-v2-course-progress-track">
                                <span style="width: {{ $courseProgress }}%"></span>
                            </div>
                            <div class="student-v2-course-progress-meta">
                                <span>{{ \App\Support\PersianUi::digits($courseCompleted) }} از {{ \App\Support\PersianUi::digits($courseTotal) }} درس کامل شده</span>
                                <span>{{ $course->level ?: 'مسیر آموزشی' }}</span>
                            </div>
                        </div>

                        <div class="student-v2-course-next">
                            <div>
                                <span>قدم بعدی</span>
                                <strong>{{ $lesson?->title ?: 'این دوره فعلاً درس ناتمامی ندارد.' }}</strong>
                            </div>

                            @if($lesson)
                                <a href="{{ route('student.lessons.show', $lesson->id) }}">
                                    ادامه
                                    <span aria-hidden="true">←</span>
                                </a>
                            @else
                                <a href="{{ route('student.courses.show', $course->id) }}">
                                    مشاهده دوره
                                    <span aria-hidden="true">←</span>
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="student-v2-empty-path">
                        <div class="student-v2-empty-icon">+</div>
                        <strong>هنوز مسیر یادگیری فعالی نداری.</strong>
                        <p>اولین دوره را انتخاب کن؛ بعد از آن داشبورد قدم بعدی را برایت روشن می‌کند.</p>
                        <a href="{{ route('courses.index') }}">پیدا کردن دوره مناسب <span>←</span></a>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- =======================================================
            ACTIVITY + SCHEDULE
        ======================================================== --}}
        <div class="student-v2-two-col">
            <section class="student-v2-panel" aria-labelledby="student-activity-title">
                <div class="student-v2-panel-head">
                    <div>
                        <span>ریتم یادگیری</span>
                        <h2 id="student-activity-title">این هفته چه‌قدر جلو رفتی؟</h2>
                    </div>
                    <div class="student-v2-mini-stat">
                        <strong>{{ \App\Support\PersianUi::digits($studyMinutes) }}</strong>
                        <span>دقیقه</span>
                    </div>
                </div>

                <div class="student-v2-week-chart" aria-label="فعالیت هفت روز اخیر">
                    @foreach($studyWeek as $day)
                        @php
                            $height = $day['minutes'] > 0
                                ? max(12, (int) round(($day['minutes'] / $maxStudyMinutes) * 100))
                                : 5;
                        @endphp
                        <div class="student-v2-week-day">
                            <span class="student-v2-week-value">
                                {{ $day['minutes'] > 0 ? \App\Support\PersianUi::digits($day['minutes']) : '۰' }}
                            </span>
                            <div class="student-v2-week-bar">
                                <span class="{{ $day['minutes'] > 0 ? 'has-data' : '' }}" style="height: {{ $height }}%"></span>
                            </div>
                            <small>{{ $day['weekday'] }}</small>
                            <b>{{ $day['label'] }}</b>
                        </div>
                    @endforeach
                </div>

                <div class="student-v2-streak">
                    <span class="student-v2-streak-mark">🔥</span>
                    <div>
                        <strong>
                            {{ $studyStreak > 0
                                ? 'ریتمت را حفظ کردی.'
                                : 'امروز فرصت خوبی برای شروع دوباره است.' }}
                        </strong>
                        <p>
                            {{ $studyStreak > 0
                                ? 'هر روز یک قدم کوچک، مسیر بزرگ‌تری می‌سازد.'
                                : 'یک درس کوتاه را شروع کن تا دوباره ریتم یادگیری شکل بگیرد.' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="student-v2-panel" aria-labelledby="student-schedule-title">
                <div class="student-v2-panel-head">
                    <div>
                        <span>تقویم عمل</span>
                        <h2 id="student-schedule-title">آنچه نزدیک است</h2>
                    </div>
                    <a href="{{ route('student.live-classes.index') }}" class="student-v2-panel-link">همه برنامه</a>
                </div>

                <div class="student-v2-schedule-list">
                    @forelse($upcomingLiveClasses as $class)
                        <a href="{{ route('student.live-classes.index') }}" class="student-v2-schedule-item">
                            <div class="student-v2-schedule-time">
                                <strong>{{ \App\Support\PersianUi::time($class->scheduled_at) }}</strong>
                                <small>{{ \App\Support\PersianUi::date($class->scheduled_at) }}</small>
                            </div>
                            <div class="student-v2-schedule-copy">
                                <strong>{{ $class->title }}</strong>
                                <span>{{ $class->course_title }}</span>
                            </div>
                            <i aria-hidden="true">←</i>
                        </a>
                    @empty
                        <div class="student-v2-schedule-empty">
                            <span>●</span>
                            <strong>جلسه‌ی پیش‌رو ثبت نشده است.</strong>
                            <p>برنامه کلاس‌ها را هر زمان بخواهی می‌توانی بررسی کنی.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- =======================================================
            TASKS / RESULTS
        ======================================================== --}}
        <div class="student-v2-two-col">
            <section class="student-v2-panel" aria-labelledby="student-tasks-title">
                <div class="student-v2-panel-head">
                    <div>
                        <span>عملیات</span>
                        <h2 id="student-tasks-title">تکلیف‌ها را از دست نده</h2>
                    </div>
                    <a href="{{ route('student.assignments.index') }}" class="student-v2-panel-link">همه تکلیف‌ها</a>
                </div>

                <div class="student-v2-task-list">
                    @forelse($assignments as $assignment)
                        <a href="{{ route('student.assignments.show', $assignment->id) }}" class="student-v2-task-item">
                            <div class="student-v2-task-date">
                                <strong>
                                    {{ $assignment->due_at ? \App\Support\PersianUi::date($assignment->due_at) : '—' }}
                                </strong>
                                <small>موعد</small>
                            </div>
                            <div class="student-v2-task-copy">
                                <strong>{{ $assignment->title }}</strong>
                                <span>
                                    @if($assignment->submitted_at)
                                        {{ $assignment->graded_at ? 'تصحیح‌شده' : 'ارسال‌شده' }}
                                    @else
                                        در انتظار اقدام
                                    @endif
                                </span>
                            </div>
                            <b class="{{ $assignment->submitted_at ? 'is-done' : 'is-open' }}">
                                {{ $assignment->submitted_at ? '✓' : '→' }}
                            </b>
                        </a>
                    @empty
                        <div class="student-v2-schedule-empty">
                            <span>✓</span>
                            <strong>فعلاً کاری عقب نمانده است.</strong>
                            <p>وقتی تکلیف جدید منتشر شود، همین‌جا اولویت آن را می‌بینی.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="student-v2-panel" aria-labelledby="student-results-title-v2">
                <div class="student-v2-panel-head">
                    <div>
                        <span>بازخورد</span>
                        <h2 id="student-results-title-v2">آخرین نتیجه‌های تو</h2>
                    </div>
                    <a href="{{ route('student.results.index') }}" class="student-v2-panel-link">مشاهده همه</a>
                </div>

                <div class="student-v2-results-list">
                    @forelse($recentResults as $result)
                        <article class="student-v2-result-item">
                            <div class="student-v2-result-score">
                                @if($result->score !== null)
                                    {{ \App\Support\PersianUi::digits($result->score) }}
                                @else
                                    —
                                @endif
                            </div>
                            <div>
                                <strong>{{ $result->title }}</strong>
                                <span>{{ $result->status_label ?? 'ثبت‌شده' }}</span>
                            </div>
                            <small>
                                @if($result->occurred_at)
                                    {{ \App\Support\PersianUi::date($result->occurred_at) }}
                                @else
                                    —
                                @endif
                            </small>
                        </article>
                    @empty
                        <div class="student-v2-schedule-empty">
                            <span>◎</span>
                            <strong>هنوز نتیجه‌ای برای نمایش نداریم.</strong>
                            <p>نمره‌ها و بازخوردها بعد از ثبت و ارزیابی اینجا ظاهر می‌شوند.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- =======================================================
            ACHIEVEMENTS / RECOGNITION
        ======================================================== --}}
        <section class="student-v2-section" aria-labelledby="student-achievements-title">
            <div class="student-v2-section-head">
                <div>
                    <span>رشد و افتخار</span>
                    <h2 id="student-achievements-title">موفقیت‌هایت را ببین</h2>
                    <p>هر دستاورد واقعی بخشی از مسیر توست؛ کوچک یا بزرگ، ثبتش می‌کنیم.</p>
                </div>
                <a href="{{ route('student.achievements.index') }}" class="student-v2-section-link">
                    {{ \App\Support\PersianUi::digits($achievementCount ?? 0) }} دستاورد
                    <span aria-hidden="true">←</span>
                </a>
            </div>

            <div class="student-v2-achievements">
                @forelse($achievements as $achievement)
                    <article class="student-v2-achievement">
                        <div class="student-v2-achievement-mark">★</div>
                        <div class="student-v2-achievement-copy">
                            <strong>{{ $achievement->title ?: $achievement->display_name }}</strong>
                            <span>{{ $achievement->school_name ?: $achievement->achievement_type }}</span>
                        </div>
                        <small>
                            @if($achievement->published_at)
                                {{ \App\Support\PersianUi::date($achievement->published_at) }}
                            @else
                                ثبت‌شده
                            @endif
                        </small>
                    </article>
                @empty
                    <div class="student-v2-achievement-empty">
                        <span>★</span>
                        <strong>هنوز دستاوردی ثبت نشده است.</strong>
                        <p>این قسمت برای موفقیت‌های واقعی توست؛ نه عددهای ساختگی.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- =======================================================
            RESOURCES / FULL NAV
        ======================================================== --}}
        <section class="student-v2-section student-v2-explore" aria-labelledby="student-explore-title">
            <div class="student-v2-section-head">
                <div>
                    <span>ابزارهای رشد</span>
                    <h2 id="student-explore-title">چیزهای بیشتری برای بهتر شدن داری</h2>
                    <p>این قسمت‌ها برای زمانی هستند که می‌خواهی از «انجام دادن» به «بهتر شدن» برسی.</p>
                </div>
            </div>

            <div class="student-v2-explore-grid">
                <a href="{{ route('student.resources.index') }}">
                    <b>جزوه و منابع</b>
                    <span>منابع امن مرتبط با دوره‌ها و کلاس‌ها</span>
                    <i>↗</i>
                </a>

                @if(auth()->user()->hasPermission('exams.view'))
                    <a href="{{ route('student.exams.index') }}">
                        <b>آزمون‌ها</b>
                        <span>دانسته‌هایت را قبل از فراموش شدن محک بزن</span>
                        <i>↗</i>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('attendance.view'))
                    <a href="{{ route('student.attendance.index') }}">
                        <b>حضور و غیاب</b>
                        <span>نظم حضورت بخشی از مسیر پیشرفت توست</span>
                        <i>↗</i>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('achievements.view'))
                    <a href="{{ route('student.achievements.index') }}">
                        <b>دستاوردها</b>
                        <span>موفقیت‌هایی که ارزش ثبت و دیدن دارند</span>
                        <i>↗</i>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('notes.view'))
                    <a href="{{ route('student.notes.index') }}">
                        <b>یادداشت‌های من</b>
                        <span>چیزهایی که نباید از یادگیری‌ات گم شوند</span>
                        <i>↗</i>
                    </a>
                @endif

                @if(auth()->user()->hasPermission('profile.view'))
                    <a href="{{ route('student.profile.edit') }}">
                        <b>حساب و امنیت</b>
                        <span>اطلاعات حساب و تنظیمات شخصی</span>
                        <i>↗</i>
                    </a>
                @endif
            </div>
        </section>
    </div>
@endsection
