@extends('layouts.app')

@section('title', 'آموزش تخصصی تیزهوشان')

@section('description', 'فرزین؛ مرکز آموزش تخصصی دانش‌آموزان مستعد برای آمادگی تیزهوشان و تقویت مهارت‌های تحصیلی.')

@php
    /*
    |--------------------------------------------------------------------------
    | Temporary UI Data
    |--------------------------------------------------------------------------
    | این داده‌ها فعلاً فقط برای تکمیل و تست UI هستند.
    | بعداً از Controller / Database دریافت می‌شوند.
    */

    $featuredCourses = [
        [
            'title' => 'دوره جامع آمادگی تیزهوشان ششم',
            'description' => 'آموزش مفهومی و تستی هوش، استعداد تحلیلی و مهارت‌های لازم برای آزمون تیزهوشان.',
            'grade' => 'پایه ششم',
            'subject' => 'هوش و استعداد',
            'level' => 'پیشرفته',
            'teacher' => [
                'name' => 'استاد محمد احمدی',
                'avatar' => null,
            ],
            'sessions' => 24,
            'duration' => '32 ساعت',
            'price' => '۲٬۴۵۰٬۰۰۰ تومان',
            'oldPrice' => '۲٬۹۰۰٬۰۰۰ تومان',
            'discount' => 15,
            'featured' => true,
        ],
        [
            'title' => 'هوش و استعداد تحلیلی',
            'description' => 'از مفاهیم پایه تا حل تست‌های چالشی با تمرکز بر تکنیک‌های سرعت و دقت.',
            'grade' => 'پایه ششم',
            'subject' => 'استعداد تحلیلی',
            'level' => 'متوسط',
            'teacher' => [
                'name' => 'استاد علی رضایی',
                'avatar' => null,
            ],
            'sessions' => 18,
            'duration' => '21 ساعت',
            'price' => '۱٬۹۵۰٬۰۰۰ تومان',
            'oldPrice' => null,
            'discount' => null,
            'featured' => false,
        ],
        [
            'title' => 'هوش کلامی و منطقی',
            'description' => 'تقویت مهارت تحلیل، استدلال و پاسخ‌گویی سریع به سوالات هوش کلامی.',
            'grade' => 'پایه پنجم',
            'subject' => 'هوش',
            'level' => 'مقدماتی',
            'teacher' => [
                'name' => 'استاد سارا محمدی',
                'avatar' => null,
            ],
            'sessions' => 16,
            'duration' => '18 ساعت',
            'price' => '۱٬۶۵۰٬۰۰۰ تومان',
            'oldPrice' => null,
            'discount' => null,
            'featured' => false,
        ],
    ];

    $teachers = [
        [
            'name' => 'دکتر محمد احمدی',
            'specialty' => 'مدرس هوش و استعداد تحلیلی',
            'experience' => 12,
            'coursesCount' => 8,
            'studentsCount' => '۲٬۴۰۰',
            'rating' => '4.9',
            'verified' => true,
        ],
        [
            'name' => 'استاد علی رضایی',
            'specialty' => 'مدرس استعداد منطقی',
            'experience' => 9,
            'coursesCount' => 6,
            'studentsCount' => '۱٬۸۰۰',
            'rating' => '4.8',
            'verified' => true,
        ],
        [
            'name' => 'استاد سارا محمدی',
            'specialty' => 'مدرس هوش کلامی',
            'experience' => 7,
            'coursesCount' => 5,
            'studentsCount' => '۱٬۲۰۰',
            'rating' => '4.9',
            'verified' => true,
        ],
    ];

    $grades = [
        [
            'title' => 'پایه چهارم',
            'description' => 'تقویت پایه و پرورش مهارت حل مسئله',
            'icon' => '۴',
        ],
        [
            'title' => 'پایه پنجم',
            'description' => 'آمادگی اولیه و تقویت استعداد تحلیلی',
            'icon' => '۵',
        ],
        [
            'title' => 'پایه ششم',
            'description' => 'آمادگی تخصصی برای آزمون تیزهوشان',
            'icon' => '۶',
        ],
        [
            'title' => 'پایه هفتم',
            'description' => 'تقویت مهارت‌های تحصیلی و منطقی',
            'icon' => '۷',
        ],
        [
            'title' => 'پایه هشتم',
            'description' => 'توسعه مهارت و آمادگی آزمون',
            'icon' => '۸',
        ],
        [
            'title' => 'پایه نهم',
            'description' => 'آمادگی مسیر تحصیلی و آزمون‌ها',
            'icon' => '۹',
        ],
    ];
@endphp

@section('content')

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <x-sections.hero
        eyebrow="آموزش تخصصی تیزهوشان"
        title="برای موفقیت، فقط بیشتر درس نخوان؛ درست یاد بگیر."
        description="فرزین کنار دانش‌آموزان مستعد است تا با آموزش هدفمند، مدرسین متخصص و یک مسیر یادگیری منظم، برای آزمون‌های تیزهوشان آماده شوند."
        primary-text="مشاهده دوره‌ها"
        primary-href="{{ route('courses.index') }}"
        secondary-text="آشنایی با فرزین"
        secondary-href="#about"
    />

    {{-- =========================================================
        GRADE SELECTION
    ========================================================== --}}
    <x-layout.section
        id="grades"
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="انتخاب مسیر"
                title="از پایه خودت شروع کن"
                description="دوره‌های فرزین بر اساس پایه و نیاز آموزشی دانش‌آموز طراحی شده‌اند تا سریع‌تر به مسیر مناسب خودت برسی."
            />

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                @foreach($grades as $grade)
                    <a
                        href="{{ route('courses.index') }}"
                        class="group rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-xs)] transition-all duration-200 hover:-translate-y-1 hover:border-[var(--color-brand-200)] hover:shadow-[var(--shadow-md)]"
                    >
                        <div class="flex items-center gap-4">

                            <span
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-lg font-black text-[var(--color-brand-700)] transition-colors duration-200 group-hover:bg-[var(--color-brand-100)]"
                            >
                                {{ $grade['icon'] }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                                    {{ $grade['title'] }}
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-[var(--color-text-secondary)]">
                                    {{ $grade['description'] }}
                                </p>
                            </div>

                            <svg
                                class="h-5 w-5 shrink-0 text-[var(--color-text-muted)] transition-transform duration-200 group-hover:-translate-x-1 group-hover:text-[var(--color-brand-600)]"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path d="m9 18 6-6-6-6" />
                            </svg>

                        </div>
                    </a>
                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        FEATURED COURSES
    ========================================================== --}}
    <x-layout.section
        id="courses"
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">

                <x-layout.page-header
                    eyebrow="دوره‌های منتخب"
                    title="برای مسیرت انتخاب درست داشته باش"
                    description="دوره‌هایی که برای شروع یا ادامه مسیر آمادگی تیزهوشان پیشنهاد می‌کنیم."
                    class="max-w-2xl"
                />

                <div class="shrink-0">
                    <x-ui.button
                        href="{{ route('courses.index') }}"
                        variant="secondary"
                    >
                        مشاهده همه دوره‌ها

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </x-ui.button>
                </div>

            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                @foreach($featuredCourses as $course)
                    <x-education.course-card
                        :title="$course['title']"
                        :description="$course['description']"
                        :grade="$course['grade']"
                        :subject="$course['subject']"
                        :level="$course['level']"
                        :teacher="$course['teacher']"
                        :sessions="$course['sessions']"
                        :duration="$course['duration']"
                        :price="$course['price']"
                        :old-price="$course['oldPrice']"
                        :discount="$course['discount']"
                        :featured="$course['featured']"
                        href="#"
                    />
                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        WHY FARZIN
    ========================================================== --}}
    <x-layout.section
        id="about"
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="grid items-center gap-12 lg:grid-cols-2">

                {{-- Content --}}
                <div>

                    <span class="inline-flex items-center rounded-full bg-[var(--color-brand-50)] px-3 py-1.5 text-xs font-bold text-[var(--color-brand-700)]">
                        چرا فرزین؟
                    </span>

                    <h2 class="mt-5 text-3xl font-black leading-tight text-[var(--color-text-primary)] sm:text-4xl">
                        آموزش فقط دیدن ویدئو نیست
                    </h2>

                    <p class="mt-5 max-w-xl text-base leading-8 text-[var(--color-text-secondary)]">
                        ما می‌خواهیم دانش‌آموز بداند چه چیزی را، چرا و با چه روشی یاد می‌گیرد.
                        به همین دلیل ساختار آموزشی فرزین بر پایه مسیر مشخص، تمرین مستمر و ارزیابی پیشرفت طراحی می‌شود.
                    </p>

                    <div class="mt-8 space-y-4">

                        <div class="flex gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="m5 12 4.5 4.5L19 7" />
                                </svg>
                            </span>

                            <div>
                                <h3 class="text-sm font-extrabold text-[var(--color-text-primary)]">
                                    مدرسین متخصص
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-[var(--color-text-secondary)]">
                                    آموزش توسط مدرسینی که تجربه کار با دانش‌آموزان مستعد را دارند.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-[var(--color-brand-600)]">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                                    <path d="M8 9h8M8 13h6M8 17h4" />
                                </svg>
                            </span>

                            <div>
                                <h3 class="text-sm font-extrabold text-[var(--color-text-primary)]">
                                    مسیر یادگیری مشخص
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-[var(--color-text-secondary)]">
                                    هر دوره، سرفصل و ترتیب مشخصی دارد تا دانش‌آموز گم نشود.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-warning-50)] text-[var(--color-warning-600)]">
                                <svg
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>

                            <div>
                                <h3 class="text-sm font-extrabold text-[var(--color-text-primary)]">
                                    ارزیابی و پیشرفت
                                </h3>

                                <p class="mt-1 text-sm leading-6 text-[var(--color-text-secondary)]">
                                    دانش‌آموز می‌تواند میزان پیشرفت خود را در طول مسیر دنبال کند.
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="mt-8">
                        <x-ui.button
                            href="{{ route('courses.index') }}"
                        >
                            شروع مسیر یادگیری
                        </x-ui.button>
                    </div>

                </div>

                {{-- Visual --}}
                <div class="relative">

                    <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-brand-50)] p-6 sm:p-8">

                        <div class="rounded-2xl border border-[var(--color-border)] bg-white p-5 shadow-[var(--shadow-md)]">

                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs text-[var(--color-text-muted)]">
                                        پیشرفت مسیر
                                    </p>

                                    <p class="mt-1 text-xl font-black text-[var(--color-text-primary)]">
                                        آمادگی تیزهوشان
                                    </p>
                                </div>

                                <x-ui.badge
                                    variant="success"
                                    size="sm"
                                >
                                    در مسیر درست
                                </x-ui.badge>
                            </div>

                            <div class="mt-6">
                                <x-ui.progress
                                    :value="78"
                                    label="پیشرفت کلی"
                                    size="md"
                                />
                            </div>

                            <div class="mt-6 space-y-3">

                                <div class="flex items-center gap-3 rounded-xl bg-[var(--color-neutral-50)] p-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                        ✓
                                    </span>

                                    <span class="flex-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                        هوش کلامی
                                    </span>

                                    <span class="text-xs font-bold text-[var(--color-success-600)]">
                                        کامل شد
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 rounded-xl bg-[var(--color-neutral-50)] p-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-brand-50)] text-[var(--color-brand-600)]">
                                        →
                                    </span>

                                    <span class="flex-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                        هوش تصویری
                                    </span>

                                    <span class="text-xs font-bold text-[var(--color-brand-600)]">
                                        در حال یادگیری
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 rounded-xl bg-[var(--color-neutral-50)] p-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-neutral-100)] text-[var(--color-text-muted)]">
                                        ۳
                                    </span>

                                    <span class="flex-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                        آزمون جامع
                                    </span>

                                    <span class="text-xs font-bold text-[var(--color-text-muted)]">
                                        بعدی
                                    </span>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        STATS
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="grid overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] sm:grid-cols-2 lg:grid-cols-4">

                <div class="border-b border-[var(--color-border)] p-6 text-center sm:border-l lg:border-b-0">
                    <p class="text-3xl font-black text-[var(--color-brand-600)]">
                        ۲٬۵۰۰+
                    </p>

                    <p class="mt-2 text-sm font-medium text-[var(--color-text-secondary)]">
                        دانش‌آموز فعال
                    </p>
                </div>

                <div class="border-b border-[var(--color-border)] p-6 text-center lg:border-b-0 lg:border-l">
                    <p class="text-3xl font-black text-[var(--color-brand-600)]">
                        ۳۰+
                    </p>

                    <p class="mt-2 text-sm font-medium text-[var(--color-text-secondary)]">
                        دوره آموزشی
                    </p>
                </div>

                <div class="border-b border-[var(--color-border)] p-6 text-center sm:border-l lg:border-b-0">
                    <p class="text-3xl font-black text-[var(--color-brand-600)]">
                        ۲۰+
                    </p>

                    <p class="mt-2 text-sm font-medium text-[var(--color-text-secondary)]">
                        مدرس متخصص
                    </p>
                </div>

                <div class="p-6 text-center">
                    <p class="text-3xl font-black text-[var(--color-brand-600)]">
                        ۹۵٪
                    </p>

                    <p class="mt-2 text-sm font-medium text-[var(--color-text-secondary)]">
                        رضایت دانش‌آموزان
                    </p>
                </div>

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        TEACHERS
    ========================================================== --}}
    <x-layout.section
        id="teachers"
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">

                <x-layout.page-header
                    eyebrow="اساتید فرزین"
                    title="با بهترین مدرس‌ها یاد بگیر"
                    description="اساتید فرزین بر اساس تخصص، تجربه و توانایی آموزش به دانش‌آموزان انتخاب می‌شوند."
                    class="max-w-2xl"
                />

                <div class="shrink-0">
                    <x-ui.button
                        href="{{ route('teachers.index') }}"
                        variant="secondary"
                    >
                        مشاهده همه اساتید
                    </x-ui.button>
                </div>

            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                @foreach($teachers as $teacher)
                    <x-education.teacher-card
                        :name="$teacher['name']"
                        :specialty="$teacher['specialty']"
                        :experience="$teacher['experience']"
                        :courses-count="$teacher['coursesCount']"
                        :students-count="$teacher['studentsCount']"
                        :rating="$teacher['rating']"
                        :verified="$teacher['verified']"
                        href="#"
                    />
                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        HOW IT WORKS
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="شروع مسیر"
                title="فرزین چطور کار می‌کند؟"
                description="بدون پیچیدگی، فقط چند قدم تا شروع یک مسیر آموزشی منظم."
                align="center"
                class="mx-auto max-w-2xl"
            />

            <div class="mt-12 grid gap-8 md:grid-cols-3">

                <div class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-600)] text-lg font-black text-white shadow-[var(--shadow-sm)]">
                        ۱
                    </span>

                    <h3 class="mt-5 text-base font-extrabold text-[var(--color-text-primary)]">
                        پایه خودت را انتخاب کن
                    </h3>

                    <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-[var(--color-text-secondary)]">
                        پایه تحصیلی و هدف آموزشی خودت را مشخص کن.
                    </p>
                </div>

                <div class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-600)] text-lg font-black text-white shadow-[var(--shadow-sm)]">
                        ۲
                    </span>

                    <h3 class="mt-5 text-base font-extrabold text-[var(--color-text-primary)]">
                        دوره مناسب را پیدا کن
                    </h3>

                    <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-[var(--color-text-secondary)]">
                        دوره‌ها را بررسی کن و بر اساس نیازت انتخاب کن.
                    </p>
                </div>

                <div class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-600)] text-lg font-black text-white shadow-[var(--shadow-sm)]">
                        ۳
                    </span>

                    <h3 class="mt-5 text-base font-extrabold text-[var(--color-text-primary)]">
                        یادگیری را شروع کن
                    </h3>

                    <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-[var(--color-text-secondary)]">
                        یاد بگیر، تمرین کن و پیشرفتت را قدم‌به‌قدم دنبال کن.
                    </p>
                </div>

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        TESTIMONIALS
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="تجربه خانواده فرزین"
                title="نظر دانش‌آموزان و والدین"
                description="این بخش فعلاً با محتوای نمونه ساخته شده و بعداً به داده واقعی متصل می‌شود."
                align="center"
                class="mx-auto max-w-2xl"
            />

            <div class="mt-10 grid gap-6 md:grid-cols-3">

                <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-xs)]">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar
                            name="محمد رضایی"
                            size="md"
                        />

                        <div>
                            <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                محمد رضایی
                            </p>

                            <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                دانش‌آموز پایه ششم
                            </p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-ui.badge
                            variant="warning"
                            size="sm"
                        >
                            تجربه دانش‌آموز
                        </x-ui.badge>
                    </div>

                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-secondary)]">
                        درس‌ها منظم و قابل فهم بودن و مهم‌تر از همه می‌دونستم بعد از هر جلسه باید چه چیزی رو تمرین کنم.
                    </p>
                </article>

                <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-xs)]">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar
                            name="مریم کریمی"
                            size="md"
                        />

                        <div>
                            <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                مریم کریمی
                            </p>

                            <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                والد دانش‌آموز
                            </p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-ui.badge
                            variant="success"
                            size="sm"
                        >
                            تجربه والد
                        </x-ui.badge>
                    </div>

                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-secondary)]">
                        چیزی که برای من مهم بود، مشخص بودن برنامه و پیشرفت فرزندم بود. اطلاعات آموزشی خیلی واضح ارائه می‌شد.
                    </p>
                </article>

                <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-xs)]">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar
                            name="امیرحسین احمدی"
                            size="md"
                        />

                        <div>
                            <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                امیرحسین احمدی
                            </p>

                            <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                دانش‌آموز پایه پنجم
                            </p>
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-ui.badge
                            variant="brand"
                            size="sm"
                        >
                            تجربه دانش‌آموز
                        </x-ui.badge>
                    </div>

                    <p class="mt-4 text-sm leading-7 text-[var(--color-text-secondary)]">
                        تمرین‌ها باعث شد فقط ویدئو نبینم و واقعاً سوال حل کنم. این مدل برای من خیلی بهتر جواب داد.
                    </p>
                </article>

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        FAQ
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="سوالات متداول"
                title="قبل از شروع، شاید این‌ها سوال تو هم باشند"
                description="جواب چند سوال رایج درباره دوره‌ها و مسیر یادگیری فرزین."
                align="center"
                class="mx-auto max-w-2xl"
            />

            <div
                x-data="{ active: 1 }"
                class="mx-auto mt-10 max-w-3xl space-y-3"
            >

                @php
                    $faqs = [
                        [
                            'id' => 1,
                            'question' => 'فرزین برای چه دانش‌آموزانی مناسب است؟',
                            'answer' => 'فرزین برای دانش‌آموزانی طراحی شده که قصد تقویت مهارت‌های درسی و آمادگی برای آزمون‌های تیزهوشان را دارند.',
                        ],
                        [
                            'id' => 2,
                            'question' => 'دوره‌ها به چه صورت برگزار می‌شوند؟',
                            'answer' => 'ساختار دوره‌ها می‌تواند شامل کلاس آنلاین، محتوای ضبط‌شده، تمرین، تکلیف و آزمون باشد. جزئیات هر دوره در صفحه همان دوره نمایش داده می‌شود.',
                        ],
                        [
                            'id' => 3,
                            'question' => 'آیا والدین هم می‌توانند پیشرفت فرزندشان را ببینند؟',
                            'answer' => 'بله. در پنل والدین می‌توان وضعیت دوره‌ها، کلاس‌ها، آزمون‌ها و روند پیشرفت دانش‌آموز را مشاهده کرد.',
                        ],
                        [
                            'id' => 4,
                            'question' => 'چطور دوره مناسب خودم را پیدا کنم؟',
                            'answer' => 'می‌توانی ابتدا پایه تحصیلی و سپس موضوع یا هدف آموزشی خودت را انتخاب کنی و از بین دوره‌های مرتبط انتخاب داشته باشی.',
                        ],
                    ];
                @endphp

                @foreach($faqs as $faq)
                    <div
                        class="overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)]"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-5 py-5 text-right"
                            @click="active = active === {{ $faq['id'] }} ? null : {{ $faq['id'] }}"
                            :aria-expanded="active === {{ $faq['id'] }}"
                            aria-controls="faq-{{ $faq['id'] }}"
                        >
                            <span class="text-sm font-bold text-[var(--color-text-primary)]">
                                {{ $faq['question'] }}
                            </span>

                            <svg
                                class="h-5 w-5 shrink-0 text-[var(--color-text-muted)] transition-transform duration-200"
                                :class="{ 'rotate-180': active === {{ $faq['id'] }} }"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                aria-hidden="true"
                            >
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div
                            id="faq-{{ $faq['id'] }}"
                            x-show="active === {{ $faq['id'] }}"
                            x-collapse
                            x-cloak
                        >
                            <div class="border-t border-[var(--color-border)] px-5 pb-5 pt-4">
                                <p class="text-sm leading-7 text-[var(--color-text-secondary)]">
                                    {{ $faq['answer'] }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        FINAL CTA
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="overflow-hidden rounded-3xl bg-[var(--color-brand-600)] px-6 py-12 text-center sm:px-10 sm:py-16">

                <span class="inline-flex rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-white">
                    آماده‌ای؟
                </span>

                <h2 class="mx-auto mt-5 max-w-3xl text-3xl font-black leading-tight text-white sm:text-4xl">
                    قدم اول مسیر موفقیتت فقط یک انتخابه
                </h2>

                <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    دوره مناسب خودت را پیدا کن و مسیر یادگیری منظم و هدفمندت را با فرزین شروع کن.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">

                    <x-ui.button
                        href="{{ route('courses.index') }}"
                        variant="secondary"
                        size="lg"
                    >
                        مشاهده دوره‌ها

                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <path d="m9 18 6-6-6-6" />
                        </svg>
                    </x-ui.button>

                    <a
                        href="{{ route('teachers.index') }}"
                        class="inline-flex min-h-12 items-center justify-center rounded-full border border-white/25 bg-white/10 px-7 py-3.5 text-sm font-semibold text-white transition-colors duration-200 hover:bg-white/15 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-white/20"
                    >
                        آشنایی با اساتید
                    </a>

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection
