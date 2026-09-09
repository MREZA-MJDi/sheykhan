@extends('layouts.app')

@section('title', 'پروفایل مدرس')

@section('description', 'معرفی مدرس، تخصص، تجربه و دوره‌های آموزشی مدرس در فرزین.')

@php
    /*
    |--------------------------------------------------------------------------
    | Temporary UI Data
    |--------------------------------------------------------------------------
    | بعداً این اطلاعات از Teacher model / controller می‌آیند.
    */

    $teacher = [
        'slug' => 'mohammad-ahmadi',
        'name' => 'دکتر محمد احمدی',
        'avatar' => asset('images/teachers/mohammad-ahmadi.jpg'),
        'specialty' => 'مدرس هوش و استعداد تحلیلی',
        'bio' => 'متخصص آموزش هوش و استعداد تحلیلی با تمرکز بر آموزش مفهومی، حل مسئله و آمادگی آزمون‌های تیزهوشان.',
        'experience' => 12,
        'coursesCount' => 8,
        'studentsCount' => '۲٬۴۰۰',
        'rating' => '4.9',
        'reviewsCount' => '۱۸۶',
        'verified' => true,
    ];

    $courses = [
        [
            'slug' => 'comprehensive-smart-school-grade-six',
            'title' => 'دوره جامع آمادگی تیزهوشان ششم',
            'description' => 'آموزش جامع هوش و استعداد تحلیلی برای آمادگی آزمون.',
            'grade' => 'پایه ششم',
            'subject' => 'هوش و استعداد',
            'level' => 'پیشرفته',
            'teacher' => [
                'name' => $teacher['name'],
                'avatar' => $teacher['avatar'],
            ],
            'sessions' => 24,
            'duration' => '32 ساعت',
            'price' => '۲٬۴۵۰٬۰۰۰ تومان',
            'discount' => 15,
            'featured' => true,
        ],
        [
            'slug' => 'analytical-reasoning',
            'title' => 'هوش و استعداد تحلیلی',
            'description' => 'تقویت مهارت حل مسئله و تحلیل سؤالات استاندارد.',
            'grade' => 'پایه ششم',
            'subject' => 'استعداد تحلیلی',
            'level' => 'متوسط',
            'teacher' => [
                'name' => $teacher['name'],
                'avatar' => $teacher['avatar'],
            ],
            'sessions' => 18,
            'duration' => '21 ساعت',
            'price' => '۱٬۹۵۰٬۰۰۰ تومان',
            'discount' => null,
            'featured' => false,
        ],
        [
            'slug' => 'smart-school-complete-exams',
            'title' => 'آزمون‌های جامع تیزهوشان',
            'description' => 'مجموعه آزمون‌های استاندارد برای سنجش آمادگی دانش‌آموز.',
            'grade' => 'پایه ششم',
            'subject' => 'آزمون',
            'level' => 'پیشرفته',
            'teacher' => [
                'name' => $teacher['name'],
                'avatar' => $teacher['avatar'],
            ],
            'sessions' => 10,
            'duration' => '8 ساعت',
            'price' => '۸۹۰٬۰۰۰ تومان',
            'discount' => null,
            'featured' => false,
        ],
    ];

    $specialties = [
        'هوش و استعداد تحلیلی',
        'حل مسئله',
        'آمادگی تیزهوشان',
        'هوش منطقی',
        'آزمون‌های استاندارد',
    ];
@endphp

@section('content')

    {{-- =========================================================
        BREADCRUMBS
    ========================================================== --}}
    <x-layout.section spacing="sm">
        <x-layout.container>

            <x-navigation.breadcrumbs
                :items="[
                    [
                        'label' => 'اساتید',
                        'url' => route('teachers.index'),
                    ],
                    [
                        'label' => $teacher['name'],
                    ],
                ]"
            />

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        PROFILE HERO
    ========================================================== --}}
    <section class="border-y border-[var(--color-border)] bg-[var(--color-surface)]">
        <x-layout.container>

            <div class="py-10 sm:py-14">

                <div class="grid gap-10 lg:grid-cols-12 lg:items-center">

                    {{-- Teacher profile --}}
                    <div class="lg:col-span-8">

                        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">

                            {{-- Real teacher image --}}
                            <x-ui.avatar
                                :src="$teacher['avatar']"
                                :name="$teacher['name']"
                                :alt="$teacher['name']"
                                size="2xl"
                                class="shrink-0 ring-4 ring-[var(--color-brand-50)]"
                            />

                            <div class="min-w-0">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h1 class="text-3xl font-black leading-tight text-[var(--color-text-primary)] sm:text-4xl">
                                        {{ $teacher['name'] }}
                                    </h1>

                                    @if($teacher['verified'])
                                        <x-ui.badge variant="brand">
                                            مدرس تأییدشده
                                        </x-ui.badge>
                                    @endif

                                </div>

                                <p class="mt-2 text-base font-medium text-[var(--color-text-secondary)]">
                                    {{ $teacher['specialty'] }}
                                </p>

                                {{-- Rating --}}
                                <div class="mt-4 flex flex-wrap items-center gap-3">

                                    <div class="flex items-center gap-1">

                                        <span class="text-base font-extrabold text-[var(--color-text-primary)]">
                                            {{ $teacher['rating'] }}
                                        </span>

                                        <span
                                            class="flex items-center gap-0.5 text-[var(--color-warning-500)]"
                                            aria-label="امتیاز {{ $teacher['rating'] }} از ۵"
                                        >
                                            @for($i = 0; $i < 5; $i++)
                                                <svg
                                                    class="h-4 w-4 fill-current"
                                                    viewBox="0 0 20 20"
                                                    aria-hidden="true"
                                                >
                                                    <path d="m10 1.8 2.5 5.1 5.6.8-4 4 1 5.6-5.1-2.7-5.1 2.7 1-5.6-4-4 5.6-.8L10 1.8Z" />
                                                </svg>
                                            @endfor
                                        </span>

                                    </div>

                                    <span class="text-sm text-[var(--color-text-muted)]">
                                        {{ $teacher['reviewsCount'] }} نظر
                                    </span>

                                </div>

                                {{-- Specialties --}}
                                <div class="mt-5 flex flex-wrap gap-2">

                                    @foreach($specialties as $specialty)
                                        <x-ui.badge
                                            variant="neutral"
                                            size="sm"
                                        >
                                            {{ $specialty }}
                                        </x-ui.badge>
                                    @endforeach

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Stats --}}
                    <div class="lg:col-span-4">

                        <div class="grid grid-cols-3 divide-x divide-[var(--color-border)] divide-x-reverse rounded-2xl border border-[var(--color-border)] bg-[var(--color-background)] py-5">

                            <div class="px-3 text-center">
                                <p class="text-xl font-black text-[var(--color-text-primary)]">
                                    {{ $teacher['experience'] }}
                                </p>

                                <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                    سال تجربه
                                </p>
                            </div>

                            <div class="px-3 text-center">
                                <p class="text-xl font-black text-[var(--color-text-primary)]">
                                    {{ $teacher['coursesCount'] }}
                                </p>

                                <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                    دوره
                                </p>
                            </div>

                            <div class="px-3 text-center">
                                <p class="text-xl font-black text-[var(--color-text-primary)]">
                                    {{ $teacher['studentsCount'] }}
                                </p>

                                <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                    دانش‌آموز
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>
    </section>


    {{-- =========================================================
        MAIN CONTENT
    ========================================================== --}}
    <x-layout.section spacing="lg">
        <x-layout.container>

            <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">

                {{-- =====================================================
                    MAIN COLUMN
                ====================================================== --}}
                <div class="min-w-0 lg:col-span-8">

                    {{-- About --}}
                    <section>

                        <x-layout.page-header
                            eyebrow="درباره مدرس"
                            title="معرفی {{ $teacher['name'] }}"
                            class="max-w-2xl"
                        />

                        <div class="mt-6 rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">

                            <p class="text-sm leading-8 text-[var(--color-text-secondary)]">
                                {{ $teacher['bio'] }}
                            </p>

                            <p class="mt-4 text-sm leading-8 text-[var(--color-text-secondary)]">
                                هدف این مدرس، کمک به دانش‌آموزان برای درک عمیق‌تر مفاهیم،
                                افزایش مهارت حل مسئله و آمادگی اصولی برای آزمون‌های تیزهوشان است.
                            </p>

                        </div>

                    </section>


                    {{-- Courses --}}
                    <section class="mt-14">

                        <x-layout.page-header
                            eyebrow="دوره‌های این مدرس"
                            title="آموزش‌هایی که ارائه می‌دهد"
                            description="دوره‌های آموزشی این مدرس را بررسی کنید."
                            class="max-w-2xl"
                        />

                        <div class="mt-8 grid gap-6 md:grid-cols-2">

                            @foreach($courses as $course)

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
                                    :discount="$course['discount']"
                                    :featured="$course['featured']"
                                    :href="route('courses.show', $course['slug'])"
                                />

                            @endforeach

                        </div>

                    </section>


                    {{-- Reviews --}}
                    <section class="mt-14">

                        <x-layout.page-header
                            eyebrow="نظرات"
                            title="دانش‌آموزان درباره این مدرس چه می‌گویند؟"
                            class="max-w-2xl"
                        />

                        <div class="mt-8 space-y-4">

                            {{-- Review 1 --}}
                            <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">

                                <div class="flex items-center gap-3">

                                    <x-ui.avatar
                                        :src="asset('images/avatars/mohammad-rezaei.jpg')"
                                        name="محمد رضایی"
                                        alt="تصویر محمد رضایی"
                                        size="md"
                                    />

                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                            محمد رضایی
                                        </p>

                                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                            دانش‌آموز پایه ششم
                                        </p>
                                    </div>

                                    <div
                                        class="mr-auto flex items-center gap-0.5 text-[var(--color-warning-500)]"
                                        aria-label="امتیاز ۵ از ۵"
                                    >
                                        @for($i = 0; $i < 5; $i++)
                                            <svg
                                                class="h-4 w-4 fill-current"
                                                viewBox="0 0 20 20"
                                                aria-hidden="true"
                                            >
                                                <path d="m10 1.8 2.5 5.1 5.6.8-4 4 1 5.6-5.1-2.7-5.1 2.7 1-5.6-4-4 5.6-.8L10 1.8Z" />
                                            </svg>
                                        @endfor
                                    </div>

                                </div>

                                <p class="mt-4 text-sm leading-7 text-[var(--color-text-secondary)]">
                                    توضیحات استاد خیلی واضح بود و باعث شد مسائل هوش رو راحت‌تر درک کنم.
                                </p>

                            </article>


                            {{-- Review 2 --}}
                            <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">

                                <div class="flex items-center gap-3">

                                    <x-ui.avatar
                                        :src="asset('images/avatars/maryam-karimi.jpg')"
                                        name="مریم کریمی"
                                        alt="تصویر مریم کریمی"
                                        size="md"
                                    />

                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                            مریم کریمی
                                        </p>

                                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                            والد دانش‌آموز
                                        </p>
                                    </div>

                                    <div
                                        class="mr-auto flex items-center gap-0.5 text-[var(--color-warning-500)]"
                                        aria-label="امتیاز ۵ از ۵"
                                    >
                                        @for($i = 0; $i < 5; $i++)
                                            <svg
                                                class="h-4 w-4 fill-current"
                                                viewBox="0 0 20 20"
                                                aria-hidden="true"
                                            >
                                                <path d="m10 1.8 2.5 5.1 5.6.8-4 4 1 5.6-5.1-2.7-5.1 2.7 1-5.6-4-4 5.6-.8L10 1.8Z" />
                                            </svg>
                                        @endfor
                                    </div>

                                </div>

                                <p class="mt-4 text-sm leading-7 text-[var(--color-text-secondary)]">
                                    ساختار آموزش منظم بود و برای ما مهم بود که فرزندمان بداند دقیقاً چه چیزی را باید یاد بگیرد.
                                </p>

                            </article>

                        </div>

                    </section>

                </div>


                {{-- =====================================================
                    SIDEBAR
                ====================================================== --}}
                <aside class="lg:col-span-4">

                    <div class="sticky top-24 space-y-5">

                        {{-- Teacher mini profile --}}
                        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-xs)]">

                            <div class="flex items-center gap-3">

                                <x-ui.avatar
                                    :src="$teacher['avatar']"
                                    :name="$teacher['name']"
                                    :alt="$teacher['name']"
                                    size="md"
                                />

                                <div class="min-w-0">

                                    <p class="text-xs text-[var(--color-text-muted)]">
                                        مدرس
                                    </p>

                                    <p class="mt-1 truncate text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $teacher['name'] }}
                                    </p>

                                </div>

                            </div>

                            <div class="mt-5">
                                <x-ui.button
                                    :href="route('courses.index')"
                                    size="lg"
                                    full-width
                                >
                                    مشاهده دوره‌ها
                                </x-ui.button>
                            </div>

                        </div>


                        {{-- Stats --}}
                        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">

                            <h2 class="text-base font-extrabold text-[var(--color-text-primary)]">
                                آمار مدرس
                            </h2>

                            <dl class="mt-5 space-y-4">

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        امتیاز
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $teacher['rating'] }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        تعداد دوره
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $teacher['coursesCount'] }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        دانش‌آموزان
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $teacher['studentsCount'] }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        تجربه
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $teacher['experience'] }} سال
                                    </dd>
                                </div>

                            </dl>

                        </div>

                    </div>

                </aside>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection

