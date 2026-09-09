@extends('layouts.app')

@section('title', 'مجله فرزین')

@section('description', 'مجله آموزشی فرزین؛ مقالات تیزهوشان، هوش و استعداد، برنامه‌ریزی درسی، آزمون‌ها و راهنمای والدین.')

@php
    /*
    |--------------------------------------------------------------------------
    | Temporary UI Data
    |--------------------------------------------------------------------------
    | بعداً این داده‌ها مستقیماً از Blog Controller / Database می‌آیند.
    */

    $categories = [
        [
            'label' => 'همه',
            'slug' => null,
        ],
        [
            'label' => 'تیزهوشان',
            'slug' => 'smart-school',
        ],
        [
            'label' => 'هوش و استعداد',
            'slug' => 'iq',
        ],
        [
            'label' => 'برنامه‌ریزی درسی',
            'slug' => 'study-planning',
        ],
        [
            'label' => 'آزمون‌ها',
            'slug' => 'exams',
        ],
        [
            'label' => 'راهنمای والدین',
            'slug' => 'parents',
        ],
        [
            'label' => 'مهارت‌های یادگیری',
            'slug' => 'learning-skills',
        ],
    ];

    $featuredArticle = [
        'title' => 'چطور برای آزمون تیزهوشان از پایه ششم آماده شویم؟',
        'excerpt' => 'از شناخت مسیر و برنامه‌ریزی تا تمرین، تست‌زنی و ارزیابی؛ یک نقشه راه ساده برای شروع آمادگی.',
        'category' => 'تیزهوشان',
        'date' => '۱۲ شهریور ۱۴۰۵',
        'readTime' => '۸ دقیقه',
        'author' => 'تیم آموزشی فرزین',
        'slug' => 'how-to-prepare-for-smart-school-exam',
    ];

    $latestArticles = [
        [
            'title' => '۵ روش مؤثر برای تقویت هوش تصویری',
            'excerpt' => 'تمرین‌هایی برای تقویت تجسم، تشخیص الگو و دقت دیداری.',
            'category' => 'هوش و استعداد',
            'date' => '۱۰ شهریور ۱۴۰۵',
            'readTime' => '۶ دقیقه',
            'slug' => 'improve-visual-iq',
        ],
        [
            'title' => 'برنامه‌ریزی مطالعه برای دانش‌آموزان تیزهوشان',
            'excerpt' => 'چطور بین درس مدرسه، تمرین، کلاس و استراحت تعادل ایجاد کنیم.',
            'category' => 'برنامه‌ریزی درسی',
            'date' => '۸ شهریور ۱۴۰۵',
            'readTime' => '۷ دقیقه',
            'slug' => 'study-planning-smart-school',
        ],
        [
            'title' => 'تفاوت هوش و استعداد تحلیلی چیست؟',
            'excerpt' => 'یک توضیح ساده درباره دو مهارت مهم در مسیر آمادگی آزمون.',
            'category' => 'هوش و استعداد',
            'date' => '۶ شهریور ۱۴۰۵',
            'readTime' => '۵ دقیقه',
            'slug' => 'iq-vs-analytical-reasoning',
        ],
        [
            'title' => 'والدین چطور روند یادگیری فرزندشان را پیگیری کنند؟',
            'excerpt' => 'چند راهکار برای همراهی بهتر بدون ایجاد فشار اضافه.',
            'category' => 'راهنمای والدین',
            'date' => '۴ شهریور ۱۴۰۵',
            'readTime' => '۶ دقیقه',
            'slug' => 'parents-track-learning-progress',
        ],
        [
            'title' => 'چطور تست‌های هوش را سریع‌تر حل کنیم؟',
            'excerpt' => 'تکنیک‌هایی برای افزایش سرعت، دقت و مدیریت زمان.',
            'category' => 'آزمون‌ها',
            'date' => '۲ شهریور ۱۴۰۵',
            'readTime' => '۸ دقیقه',
            'slug' => 'solve-iq-tests-faster',
        ],
        [
            'title' => 'اشتباهات رایج در مطالعه برای آزمون تیزهوشان',
            'excerpt' => 'اشتباهاتی که ممکن است زمان زیادی بگیرند اما نتیجه مطلوبی نداشته باشند.',
            'category' => 'تیزهوشان',
            'date' => '۳۱ مرداد ۱۴۰۵',
            'readTime' => '۵ دقیقه',
            'slug' => 'common-smart-school-study-mistakes',
        ],
    ];

    $smartSchoolArticles = [
        [
            'title' => 'از چه زمانی مطالعه برای تیزهوشان را شروع کنیم؟',
            'category' => 'تیزهوشان',
            'date' => '۲۸ مرداد ۱۴۰۵',
            'readTime' => '۷ دقیقه',
        ],
        [
            'title' => 'آشنایی با انواع سوالات استعداد تحلیلی',
            'category' => 'تیزهوشان',
            'date' => '۲۵ مرداد ۱۴۰۵',
            'readTime' => '۶ دقیقه',
        ],
        [
            'title' => 'چرا تحلیل پاسخ‌های غلط مهم‌تر از تعداد تست‌هاست؟',
            'category' => 'آزمون‌ها',
            'date' => '۲۲ مرداد ۱۴۰۵',
            'readTime' => '۵ دقیقه',
        ],
    ];

    $parentsArticles = [
        [
            'title' => 'چطور بدون ایجاد استرس کنار فرزندمان باشیم؟',
            'category' => 'راهنمای والدین',
            'date' => '۲۰ مرداد ۱۴۰۵',
            'readTime' => '۶ دقیقه',
        ],
        [
            'title' => 'نشانه‌های یک برنامه مطالعاتی نامناسب',
            'category' => 'راهنمای والدین',
            'date' => '۱۸ مرداد ۱۴۰۵',
            'readTime' => '۵ دقیقه',
        ],
        [
            'title' => 'چطور پیشرفت تحصیلی فرزندمان را درست ارزیابی کنیم؟',
            'category' => 'راهنمای والدین',
            'date' => '۱۵ مرداد ۱۴۰۵',
            'readTime' => '۷ دقیقه',
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
            <div class="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-[var(--color-brand-100)]/70 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-[var(--color-info-50)] blur-3xl"></div>
        </div>

        <x-layout.container>

            <div class="relative py-14 sm:py-18 lg:py-20">

                <div class="grid items-center gap-10 lg:grid-cols-12">

                    {{-- Copy --}}
                    <div class="lg:col-span-7">

                        <x-ui.badge variant="brand">
                            مجله فرزین
                        </x-ui.badge>

                        <h1 class="mt-5 max-w-3xl text-4xl font-black leading-[1.4] tracking-tight text-[var(--color-text-primary)] sm:text-5xl lg:text-6xl">
                            برای بهتر یاد گرفتن،
                            فقط بیشتر نخوان؛
                            <span class="text-[var(--color-brand-600)]">
                                بهتر فکر کن.
                            </span>
                        </h1>

                        <p class="mt-5 max-w-2xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg">
                            مقاله‌ها، راهنماها و نکته‌های آموزشی فرزین برای دانش‌آموزان
                            و والدینی که می‌خواهند آگاهانه‌تر در مسیر یادگیری حرکت کنند.
                        </p>

                        {{-- Search --}}
                        <form
                            action="{{ route('blog.index') }}"
                            method="GET"
                            class="mt-8 max-w-xl"
                        >
                            <label
                                for="blog-search"
                                class="sr-only"
                            >
                                جستجوی مقاله
                            </label>

                            <div class="flex flex-col gap-2 sm:flex-row">

                                <div class="relative min-w-0 flex-1">
                                    <svg
                                        class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-text-muted)]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="11"
                                            cy="11"
                                            r="7"
                                        />
                                        <path d="m20 20-3.5-3.5" />
                                    </svg>

                                    <input
                                        id="blog-search"
                                        type="search"
                                        name="q"
                                        value="{{ request('q') }}"
                                        placeholder="مثلاً: هوش تصویری، تیزهوشان، برنامه‌ریزی..."
                                        class="ui-input pr-10"
                                    />
                                </div>

                                <x-ui.button
                                    type="submit"
                                    size="lg"
                                >
                                    جستجو
                                </x-ui.button>

                            </div>
                        </form>

                    </div>

                    {{-- Editorial visual --}}
                    <div class="hidden lg:col-span-5 lg:block">

                        <div class="relative mx-auto aspect-[4/3] max-w-md">

                            <div class="absolute inset-6 rotate-3 rounded-3xl bg-[var(--color-brand-100)]"></div>

                            <div class="absolute inset-0 rounded-3xl border border-[var(--color-border)] bg-white p-6 shadow-[var(--shadow-lg)]">

                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-[var(--color-text-muted)]">
                                            محتوای منتخب
                                        </p>

                                        <p class="mt-1 text-lg font-black text-[var(--color-text-primary)]">
                                            راهنمای یادگیری
                                        </p>
                                    </div>

                                    <x-ui.badge
                                        variant="success"
                                        size="sm"
                                    >
                                        جدید
                                    </x-ui.badge>
                                </div>

                                <div class="mt-6 rounded-2xl bg-[var(--color-brand-50)] p-5">
                                    <svg
                                        class="h-10 w-10 text-[var(--color-brand-600)]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        aria-hidden="true"
                                    >
                                        <path d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                        <path d="M8 9h8M8 13h6M8 17h4" />
                                    </svg>

                                    <p class="mt-4 text-sm font-extrabold leading-7 text-[var(--color-text-primary)]">
                                        مسیر آمادگی تیزهوشان از کجا شروع می‌شود؟
                                    </p>

                                    <p class="mt-2 text-xs leading-6 text-[var(--color-text-secondary)]">
                                        یک راهنمای ساده برای شناخت مسیر و شروع درست.
                                    </p>
                                </div>

                                <div class="mt-5 flex items-center justify-between border-t border-[var(--color-border)] pt-4">
                                    <span class="text-xs text-[var(--color-text-muted)]">
                                        تیم آموزشی فرزین
                                    </span>

                                    <span class="text-xs font-bold text-[var(--color-brand-600)]">
                                        مطالعه ←
                                    </span>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </x-layout.container>
    </section>


    {{-- =========================================================
        CATEGORIES
    ========================================================== --}}
    <section class="border-b border-[var(--color-border)] bg-[var(--color-surface)]">
        <x-layout.container>

            <nav
                class="overflow-x-auto py-4"
                aria-label="دسته‌بندی مقالات"
            >
                <div class="flex min-w-max items-center gap-2">

                    @foreach($categories as $index => $category)

                        @php
                            $isActive = $index === 0 && !request('category');
                        @endphp

                        <a
                            href="{{ $category['slug'] ? route('blog.index', ['category' => $category['slug']]) : route('blog.index') }}"
                            class="
                                rounded-full
                                px-4
                                py-2
                                text-sm
                                font-semibold
                                transition-colors
                                duration-200
                                {{ $isActive
                                    ? 'bg-[var(--color-brand-600)] text-white'
                                    : 'text-[var(--color-text-secondary)] hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]'
                                }}
                                "
                        >
                            {{ $category['label'] }}
                        </a>

                    @endforeach

                </div>
            </nav>

        </x-layout.container>
    </section>


    {{-- =========================================================
        FEATURED ARTICLE
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-[var(--color-brand-600)]">
                        پیشنهاد امروز
                    </p>

                    <h2 class="mt-1 text-2xl font-black text-[var(--color-text-primary)] sm:text-3xl">
                        مقاله ویژه
                    </h2>
                </div>
            </div>

            <article class="mt-8 overflow-hidden rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[var(--shadow-sm)]">

                <div class="grid lg:grid-cols-2">

                    {{-- Visual --}}
                    <a
                        href="{{ route('blog.show', $featuredArticle['slug']) }}"
                        class="relative flex min-h-[320px] items-center justify-center overflow-hidden bg-[var(--color-brand-50)] lg:min-h-[440px]"
                    >
                        <div class="absolute right-8 top-8 h-28 w-28 rounded-full bg-[var(--color-brand-100)]"></div>

                        <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-[var(--color-brand-600)] shadow-[var(--shadow-md)]">
                            <svg
                                class="h-12 w-12"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.5"
                                aria-hidden="true"
                            >
                                <path d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                <path d="M8 9h8M8 13h6M8 17h4" />
                            </svg>
                        </div>

                        <div class="absolute bottom-6 left-6 right-6 rounded-2xl border border-[var(--color-border)] bg-white/95 p-4 shadow-[var(--shadow-sm)]">
                            <p class="text-xs font-bold text-[var(--color-brand-600)]">
                                {{ $featuredArticle['category'] }}
                            </p>

                            <p class="mt-1 text-sm font-extrabold text-[var(--color-text-primary)]">
                                {{ $featuredArticle['readTime'] }} مطالعه
                            </p>
                        </div>
                    </a>

                    {{-- Content --}}
                    <div class="flex flex-col justify-center p-6 sm:p-8 lg:p-10">

                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge variant="brand">
                                {{ $featuredArticle['category'] }}
                            </x-ui.badge>

                            <span class="text-xs text-[var(--color-text-muted)]">
                                {{ $featuredArticle['date'] }}
                            </span>
                        </div>

                        <a
                            href="{{ route('blog.show', $featuredArticle['slug']) }}"
                            class="mt-5"
                        >
                            <h2 class="text-2xl font-black leading-9 text-[var(--color-text-primary)] sm:text-3xl">
                                {{ $featuredArticle['title'] }}
                            </h2>
                        </a>

                        <p class="mt-4 text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            {{ $featuredArticle['excerpt'] }}
                        </p>

                        <div class="mt-6 flex items-center gap-3">

                            <x-ui.avatar
                                name="{{ $featuredArticle['author'] }}"
                                size="sm"
                            />

                            <div>
                                <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                    {{ $featuredArticle['author'] }}
                                </p>

                                <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                    نویسنده
                                </p>
                            </div>

                        </div>

                        <div class="mt-7">
                            <x-ui.button
                                :href="route('blog.show', $featuredArticle['slug'])"
                            >
                                مطالعه مقاله

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

                </div>

            </article>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        LATEST ARTICLES
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-[var(--color-brand-600)]">
                        تازه منتشر شده
                    </p>

                    <h2 class="mt-1 text-2xl font-black text-[var(--color-text-primary)]">
                        جدیدترین مقالات
                    </h2>
                </div>

                <x-ui.badge variant="neutral">
                    {{ count($latestArticles) }} مقاله
                </x-ui.badge>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                @foreach($latestArticles as $article)

                    <article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[var(--shadow-xs)] transition-all duration-200 hover:-translate-y-1 hover:border-[var(--color-brand-200)] hover:shadow-[var(--shadow-md)]">

                        <a
                            href="{{ route('blog.show', $article['slug']) }}"
                            class="relative flex aspect-[16/9] items-center justify-center overflow-hidden bg-[var(--color-brand-50)]"
                        >
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[var(--color-brand-600)] shadow-[var(--shadow-sm)] transition-transform duration-200 group-hover:scale-105">
                                <svg
                                    class="h-7 w-7"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    aria-hidden="true"
                                >
                                    <path d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                                    <path d="M8 9h8M8 13h6M8 17h4" />
                                </svg>
                            </span>
                        </a>

                        <div class="flex flex-1 flex-col p-5">

                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.badge
                                    variant="brand"
                                    size="sm"
                                >
                                    {{ $article['category'] }}
                                </x-ui.badge>

                                <span class="text-xs text-[var(--color-text-muted)]">
                                    {{ $article['readTime'] }}
                                </span>
                            </div>

                            <a
                                href="{{ route('blog.show', $article['slug']) }}"
                                class="mt-4"
                            >
                                <h3 class="line-clamp-2 text-lg font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors group-hover:text-[var(--color-brand-700)]">
                                    {{ $article['title'] }}
                                </h3>
                            </a>

                            <p class="mt-2 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                                {{ $article['excerpt'] }}
                            </p>

                            <div class="mt-auto flex items-center justify-between gap-4 border-t border-[var(--color-border)] pt-4 mt-5">

                                <span class="text-xs text-[var(--color-text-muted)]">
                                    {{ $article['date'] }}
                                </span>

                                <a
                                    href="{{ route('blog.show', $article['slug']) }}"
                                    class="text-xs font-bold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
                                >
                                    ادامه مطلب ←
                                </a>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        SMART SCHOOL CONTENT
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="flex items-end justify-between gap-4">

                <x-layout.page-header
                    eyebrow="آمادگی تیزهوشان"
                    title="برای آزمون آماده‌تر شو"
                    description="محتوای منتخب فرزین برای دانش‌آموزانی که می‌خواهند مسیر آمادگی خود را بهتر بشناسند."
                    class="max-w-2xl"
                />

                <x-ui.button
                    href="{{ route('blog.index', ['category' => 'smart-school']) }}"
                    variant="secondary"
                >
                    همه مطالب
                </x-ui.button>

            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-3">

                @foreach($smartSchoolArticles as $article)

                    <article class="group rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 transition-all duration-200 hover:-translate-y-1 hover:border-[var(--color-brand-200)] hover:shadow-[var(--shadow-md)]">

                        <x-ui.badge
                            variant="brand"
                            size="sm"
                        >
                            {{ $article['category'] }}
                        </x-ui.badge>

                        <a
                            href="#"
                            class="mt-4 block"
                        >
                            <h3 class="text-lg font-extrabold leading-8 text-[var(--color-text-primary)] group-hover:text-[var(--color-brand-700)]">
                                {{ $article['title'] }}
                            </h3>
                        </a>

                        <div class="mt-5 flex items-center justify-between border-t border-[var(--color-border)] pt-4">
                            <span class="text-xs text-[var(--color-text-muted)]">
                                {{ $article['date'] }}
                            </span>

                            <span class="text-xs font-semibold text-[var(--color-text-muted)]">
                                {{ $article['readTime'] }}
                            </span>
                        </div>

                    </article>

                @endforeach

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        PARENTS CONTENT
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-background)] p-6 sm:p-8 lg:p-10">

                <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

                    <div class="max-w-2xl">

                        <x-ui.badge variant="neutral">
                            برای والدین
                        </x-ui.badge>

                        <h2 class="mt-4 text-2xl font-black text-[var(--color-text-primary)] sm:text-3xl">
                            همراه فرزندت باش، نه بالای سرش
                        </h2>

                        <p class="mt-3 text-sm leading-7 text-[var(--color-text-secondary)] sm:text-base">
                            راهنماهای فرزین برای اینکه والدین بتوانند با آرامش بیشتری
                            روند یادگیری و آمادگی فرزندشان را همراهی کنند.
                        </p>

                    </div>

                    <x-ui.button
                        href="{{ route('blog.index', ['category' => 'parents']) }}"
                        variant="secondary"
                        size="lg"
                    >
                        مطالب مخصوص والدین
                    </x-ui.button>

                </div>

                <div class="mt-8 grid gap-4 md:grid-cols-3">

                    @foreach($parentsArticles as $article)

                        <a
                            href="#"
                            class="group rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 transition-all duration-200 hover:border-[var(--color-brand-200)] hover:shadow-[var(--shadow-sm)]"
                        >
                            <div class="flex items-center justify-between gap-3">

                                <x-ui.badge
                                    variant="success"
                                    size="sm"
                                >
                                    {{ $article['category'] }}
                                </x-ui.badge>

                                <span class="text-xs text-[var(--color-text-muted)]">
                                    {{ $article['readTime'] }}
                                </span>

                            </div>

                            <h3 class="mt-4 text-sm font-extrabold leading-7 text-[var(--color-text-primary)] group-hover:text-[var(--color-brand-700)]">
                                {{ $article['title'] }}
                            </h3>

                            <p class="mt-4 text-xs text-[var(--color-text-muted)]">
                                {{ $article['date'] }}
                            </p>
                        </a>

                    @endforeach

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        COURSE CTA
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <div class="overflow-hidden rounded-3xl bg-[var(--color-brand-600)] px-6 py-12 text-center sm:px-10 sm:py-16">

                <x-ui.badge
                    variant="neutral"
                    class="bg-white/10 text-white"
                >
                    یادگیری فقط خواندن نیست
                </x-ui.badge>

                <h2 class="mx-auto mt-5 max-w-3xl text-3xl font-black leading-tight text-white sm:text-4xl">
                    مقاله را بخوان؛ بعد وارد مسیر یادگیری شو
                </h2>

                <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    وقتی آماده‌ای، دوره مناسب پایه و هدف خودت را پیدا کن و تمرین را شروع کن.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">

                    <x-ui.button
                        href="{{ route('courses.index') }}"
                        variant="secondary"
                        size="lg"
                    >
                        مشاهده دوره‌ها
                    </x-ui.button>

                    <x-ui.button
                        href="{{ route('teachers.index') }}"
                        variant="ghost"
                        size="lg"
                        class="text-white hover:bg-white/10"
                    >
                        آشنایی با اساتید
                    </x-ui.button>

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection
