@extends('layouts.app')

@section('title', 'درباره فرزین')

@section('description', 'آشنایی با فرزین، رویکرد آموزشی، تیم مدرسین و مسیر یادگیری تخصصی برای دانش‌آموزان مستعد و داوطلبان تیزهوشان.')

@php
    $stats = [
        [
            'value' => '۲٬۵۰۰+',
            'label' => 'دانش‌آموز فعال',
        ],
        [
            'value' => '۳۰+',
            'label' => 'دوره آموزشی',
        ],
        [
            'value' => '۲۰+',
            'label' => 'مدرس متخصص',
        ],
        [
            'value' => '۹۵٪',
            'label' => 'رضایت خانواده‌ها',
        ],
    ];

    $values = [
        [
            'title' => 'آموزش هدفمند',
            'description' => 'هر محتوای آموزشی باید بخشی از یک مسیر مشخص و قابل فهم برای دانش‌آموز باشد.',
        ],
        [
            'title' => 'یادگیری عمیق',
            'description' => 'هدف فقط حل کردن یک سؤال نیست؛ هدف ساختن مهارت، درک و قدرت حل مسئله است.',
        ],
        [
            'title' => 'پیگیری پیشرفت',
            'description' => 'دانش‌آموز و والد باید بدانند در مسیر یادگیری کجا قرار دارند و قدم بعدی چیست.',
        ],
        [
            'title' => 'ارتباط با خانواده',
            'description' => 'والدین باید بتوانند تصویر واضحی از روند آموزشی فرزندشان داشته باشند.',
        ],
    ];

    $steps = [
        [
            'number' => '۱',
            'title' => 'شناخت مسیر',
            'description' => 'پایه، هدف و سطح آموزشی دانش‌آموز مشخص می‌شود.',
        ],
        [
            'number' => '۲',
            'title' => 'انتخاب دوره',
            'description' => 'دوره مناسب بر اساس نیاز و هدف آموزشی انتخاب می‌شود.',
        ],
        [
            'number' => '۳',
            'title' => 'یادگیری و تمرین',
            'description' => 'آموزش، تمرین و آزمون در یک مسیر منظم ادامه پیدا می‌کند.',
        ],
        [
            'number' => '۴',
            'title' => 'اندازه‌گیری پیشرفت',
            'description' => 'عملکرد و پیشرفت دانش‌آموز در طول مسیر قابل مشاهده خواهد بود.',
        ],
    ];
@endphp

@section('content')

    {{-- =========================================================
        HERO
    ========================================================== --}}
    <section class="relative overflow-hidden border-b border-[var(--color-border)] bg-[var(--color-surface)]">
        <div
            class="pointer-events-none absolute inset-0"
            aria-hidden="true"
        >
            <div class="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-[var(--color-brand-100)]/60 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-[var(--color-info-50)] blur-3xl"></div>
        </div>

        <x-layout.container>
            <div class="relative py-16 sm:py-20 lg:py-24">

                <div class="max-w-3xl">
                    <x-ui.badge variant="brand">
                        درباره فرزین
                    </x-ui.badge>

                    <h1 class="mt-6 text-4xl font-black leading-[1.35] tracking-tight text-[var(--color-text-primary)] sm:text-5xl lg:text-6xl">
                        ما آمده‌ایم تا مسیر یادگیری را
                        برای دانش‌آموزان
                        <span class="text-[var(--color-brand-600)]">
                            ساده‌تر و هدفمندتر
                        </span>
                        کنیم.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg">
                        فرزین یک مجموعه آموزشی تخصصی برای دانش‌آموزانی است که می‌خواهند
                        عمیق‌تر یاد بگیرند، بهتر تمرین کنند و با آمادگی بیشتری در مسیر تیزهوشان
                        و رشد تحصیلی حرکت کنند.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button
                            href="{{ route('courses.index') }}"
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

                        <x-ui.button
                            href="{{ route('teachers.index') }}"
                            variant="secondary"
                            size="lg"
                        >
                            آشنایی با اساتید
                        </x-ui.button>
                    </div>
                </div>

            </div>
        </x-layout.container>
    </section>

    {{-- =========================================================
        STATS
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="grid overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] sm:grid-cols-2 lg:grid-cols-4">
                @foreach($stats as $stat)
                    <div class="border-b border-[var(--color-border)] p-7 text-center last:border-b-0 sm:border-l sm:last:border-l-0 lg:border-b-0">
                        <p class="text-3xl font-black text-[var(--color-brand-600)]">
                            {{ $stat['value'] }}
                        </p>

                        <p class="mt-2 text-sm font-medium text-[var(--color-text-secondary)]">
                            {{ $stat['label'] }}
                        </p>
                    </div>
                @endforeach
            </div>

        </x-layout.container>
    </x-layout.section>

    {{-- =========================================================
        MISSION
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="grid items-center gap-12 lg:grid-cols-2">

                <div>
                    <x-ui.badge variant="neutral">
                        مأموریت ما
                    </x-ui.badge>

                    <h2 class="mt-5 text-3xl font-black leading-tight text-[var(--color-text-primary)] sm:text-4xl">
                        آموزش باید قابل فهم،
                        قابل پیگیری و نتیجه‌محور باشد.
                    </h2>

                    <div class="mt-6 space-y-4">
                        <p class="text-base leading-8 text-[var(--color-text-secondary)]">
                            فرزین با این نگاه شکل گرفته که دانش‌آموز فقط مصرف‌کننده محتوای آموزشی نباشد.
                            او باید بداند چه چیزی را یاد می‌گیرد، چطور باید تمرین کند و میزان پیشرفت خودش را ببیند.
                        </p>

                        <p class="text-base leading-8 text-[var(--color-text-secondary)]">
                            به همین دلیل ساختار فرزین فقط به دوره آموزشی محدود نمی‌شود؛
                            بلکه دوره، کلاس، تمرین، آزمون، برنامه و گزارش پیشرفت را در یک مسیر یکپارچه کنار هم قرار می‌دهد.
                        </p>
                    </div>
                </div>

                <div class="rounded-3xl border border-[var(--color-brand-100)] bg-[var(--color-brand-50)] p-6 sm:p-8">

                    <div class="rounded-2xl border border-[var(--color-border)] bg-white p-6 shadow-[var(--shadow-md)]">

                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs text-[var(--color-text-muted)]">
                                    مسیر آموزشی
                                </p>

                                <p class="mt-1 text-lg font-extrabold text-[var(--color-text-primary)]">
                                    آمادگی تیزهوشان
                                </p>
                            </div>

                            <x-ui.badge
                                variant="success"
                                size="sm"
                            >
                                در حال پیشرفت
                            </x-ui.badge>
                        </div>

                        <div class="mt-7">
                            <x-ui.progress
                                :value="78"
                                label="پیشرفت کلی"
                            />
                        </div>

                        <div class="mt-6 space-y-3">

                            <div class="flex items-center gap-3 rounded-xl bg-[var(--color-neutral-50)] p-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-success-50)] text-sm font-bold text-[var(--color-success-600)]">
                                    ✓
                                </span>

                                <span class="flex-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                    آموزش مفاهیم
                                </span>

                                <x-ui.badge
                                    variant="success"
                                    size="sm"
                                >
                                    تکمیل
                                </x-ui.badge>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl bg-[var(--color-neutral-50)] p-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-brand-50)] text-sm font-bold text-[var(--color-brand-600)]">
                                    ۲
                                </span>

                                <span class="flex-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                    تمرین و حل سؤال
                                </span>

                                <x-ui.badge
                                    variant="brand"
                                    size="sm"
                                >
                                    جاری
                                </x-ui.badge>
                            </div>

                            <div class="flex items-center gap-3 rounded-xl bg-[var(--color-neutral-50)] p-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-neutral-100)] text-sm font-bold text-[var(--color-text-muted)]">
                                    ۳
                                </span>

                                <span class="flex-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                    آزمون و ارزیابی
                                </span>

                                <x-ui.badge
                                    variant="neutral"
                                    size="sm"
                                >
                                    بعدی
                                </x-ui.badge>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>

    {{-- =========================================================
        VALUES
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="ارزش‌های فرزین"
                title="اصولی که در طراحی تجربه آموزشی دنبال می‌کنیم"
                description="همه بخش‌های فرزین، از محتوای آموزشی تا پنل کاربری، حول چند اصل مشخص ساخته می‌شوند."
            />

            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                @foreach($values as $index => $value)
                    <article class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-xs)]">

                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-sm font-black text-[var(--color-brand-700)]">
                            {{ $index + 1 }}
                        </span>

                        <h3 class="mt-5 text-base font-extrabold text-[var(--color-text-primary)]">
                            {{ $value['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                            {{ $value['description'] }}
                        </p>

                    </article>
                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>

    {{-- =========================================================
        PROCESS
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="مسیر یادگیری"
                title="فرزین از انتخاب تا پیشرفت کنار دانش‌آموز است"
                description="یک جریان ساده و قابل فهم برای دانش‌آموز و خانواده."
                align="center"
                class="mx-auto max-w-2xl"
            />

            <div class="mt-12 grid gap-6 md:grid-cols-2 xl:grid-cols-4">

                @foreach($steps as $step)
                    <article class="relative rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-xs)]">

                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--color-brand-600)] text-base font-black text-white">
                            {{ $step['number'] }}
                        </span>

                        <h3 class="mt-5 text-base font-extrabold text-[var(--color-text-primary)]">
                            {{ $step['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                            {{ $step['description'] }}
                        </p>

                    </article>
                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>

    {{-- =========================================================
        TEAM / TEACHERS
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">

                <x-layout.page-header
                    eyebrow="تیم آموزشی"
                    title="آدم‌های پشت فرزین"
                    description="فرزین با کمک مدرسین و متخصصانی ساخته می‌شود که دغدغه آموزش درست دارند."
                    class="max-w-2xl"
                />

                <x-ui.button
                    href="{{ route('teachers.index') }}"
                    variant="secondary"
                >
                    مشاهده اساتید
                </x-ui.button>

            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                <x-education.teacher-card
                    name="دکتر محمد احمدی"
                    specialty="مدرس هوش و استعداد تحلیلی"
                    experience="12"
                    courses-count="8"
                    students-count="۲٬۴۰۰"
                    rating="4.9"
                    verified
                    href="#"
                />

                <x-education.teacher-card
                    name="استاد علی رضایی"
                    specialty="مدرس استعداد منطقی"
                    experience="9"
                    courses-count="6"
                    students-count="۱٬۸۰۰"
                    rating="4.8"
                    verified
                    href="#"
                />

                <x-education.teacher-card
                    name="استاد سارا محمدی"
                    specialty="مدرس هوش کلامی"
                    experience="7"
                    courses-count="5"
                    students-count="۱٬۲۰۰"
                    rating="4.9"
                    verified
                    href="#"
                />

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

            <div class="rounded-3xl bg-[var(--color-brand-600)] px-6 py-12 text-center sm:px-10 sm:py-16">

                <x-ui.badge
                    variant="neutral"
                    class="bg-white/10 text-white"
                >
                    آماده شروع؟
                </x-ui.badge>

                <h2 class="mx-auto mt-5 max-w-3xl text-3xl font-black leading-tight text-white sm:text-4xl">
                    مسیر یادگیری خودت را با فرزین شروع کن
                </h2>

                <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    دوره مناسب خودت را پیدا کن و یادگیری را با یک مسیر منظم و هدفمند شروع کن.
                </p>

                <div class="mt-8">
                    <x-ui.button
                        href="{{ route('courses.index') }}"
                        variant="secondary"
                        size="lg"
                    >
                        مشاهده دوره‌ها
                    </x-ui.button>
                </div>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection
