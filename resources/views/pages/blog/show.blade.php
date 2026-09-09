@extends('layouts.app')

@php
    /*
    |--------------------------------------------------------------------------
    | Temporary Article Data
    |--------------------------------------------------------------------------
    | بعداً این داده‌ها از Blog model / controller دریافت می‌شوند.
    */

    $article = [
        'slug' => 'how-to-prepare-for-smart-school-exam',
        'title' => 'چطور برای آزمون تیزهوشان از پایه ششم آماده شویم؟',
        'excerpt' => 'از شناخت مسیر و برنامه‌ریزی تا تمرین، تست‌زنی و ارزیابی؛ یک نقشه راه ساده برای شروع آمادگی آزمون تیزهوشان.',
        'category' => 'تیزهوشان',
        'categorySlug' => 'smart-school',
        'publishedAt' => '۱۲ شهریور ۱۴۰۵',
        'updatedAt' => '۱۳ شهریور ۱۴۰۵',
        'readTime' => '۸ دقیقه',
        'author' => 'تیم آموزشی فرزین',
        'authorRole' => 'تیم محتوای آموزشی',
    ];

    $relatedArticles = [
        [
            'slug' => 'improve-visual-iq',
            'title' => '۵ روش مؤثر برای تقویت هوش تصویری',
            'excerpt' => 'تمرین‌هایی برای تقویت تجسم، تشخیص الگو و دقت دیداری.',
            'category' => 'هوش و استعداد',
            'date' => '۱۰ شهریور ۱۴۰۵',
            'readTime' => '۶ دقیقه',
        ],
        [
            'slug' => 'solve-iq-tests-faster',
            'title' => 'چطور تست‌های هوش را سریع‌تر حل کنیم؟',
            'excerpt' => 'تکنیک‌هایی برای افزایش سرعت، دقت و مدیریت زمان.',
            'category' => 'آزمون‌ها',
            'date' => '۲ شهریور ۱۴۰۵',
            'readTime' => '۸ دقیقه',
        ],
        [
            'slug' => 'study-planning-smart-school',
            'title' => 'برنامه‌ریزی مطالعه برای دانش‌آموزان تیزهوشان',
            'excerpt' => 'چطور بین مدرسه، تمرین، کلاس و استراحت تعادل ایجاد کنیم.',
            'category' => 'برنامه‌ریزی درسی',
            'date' => '۸ شهریور ۱۴۰۵',
            'readTime' => '۷ دقیقه',
        ],
    ];

    $tags = [
        'تیزهوشان',
        'پایه ششم',
        'آزمون',
        'هوش و استعداد',
        'برنامه‌ریزی',
    ];
@endphp

@section('title', $article['title'])

@section('description', $article['excerpt'])

@section('content')

    {{-- =========================================================
        BREADCRUMBS
    ========================================================== --}}
    <x-layout.section spacing="sm">
        <x-layout.container>

            <x-navigation.breadcrumbs
                :items="[
                    [
                        'label' => 'مجله',
                        'url' => route('blog.index'),
                    ],
                    [
                        'label' => $article['category'],
                        'url' => route('blog.index', [
                            'category' => $article['categorySlug'],
                        ]),
                    ],
                    [
                        'label' => $article['title'],
                    ],
                ]"
            />

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        ARTICLE HEADER
    ========================================================== --}}
    <header class="border-y border-[var(--color-border)] bg-[var(--color-surface)]">
        <x-layout.container>

            <div class="mx-auto max-w-4xl py-12 text-center sm:py-16">

                <div class="flex flex-wrap items-center justify-center gap-2">

                    <a
                        href="{{ route('blog.index', ['category' => $article['categorySlug']]) }}"
                    >
                        <x-ui.badge
                            variant="brand"
                        >
                            {{ $article['category'] }}
                        </x-ui.badge>
                    </a>

                    <span class="text-xs text-[var(--color-text-muted)]">
                        انتشار:
                        {{ $article['publishedAt'] }}
                    </span>

                    <span class="h-1 w-1 rounded-full bg-[var(--color-neutral-300)]"></span>

                    <span class="text-xs text-[var(--color-text-muted)]">
                        {{ $article['readTime'] }}
                    </span>

                </div>

                <h1 class="mt-6 text-3xl font-black leading-[1.45] tracking-tight text-[var(--color-text-primary)] sm:text-4xl lg:text-5xl">
                    {{ $article['title'] }}
                </h1>

                <p class="mx-auto mt-6 max-w-3xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg">
                    {{ $article['excerpt'] }}
                </p>

                {{-- Author --}}
                <div class="mt-8 flex items-center justify-center gap-3">

                    <x-ui.avatar
                        name="{{ $article['author'] }}"
                        size="md"
                    />

                    <div class="text-right">
                        <p class="text-sm font-bold text-[var(--color-text-primary)]">
                            {{ $article['author'] }}
                        </p>

                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                            {{ $article['authorRole'] }}
                        </p>
                    </div>

                </div>

            </div>

        </x-layout.container>
    </header>


    {{-- =========================================================
        ARTICLE
    ========================================================== --}}
    <x-layout.section spacing="lg">
        <x-layout.container>

            <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">

                {{-- Main article --}}
                <article class="min-w-0 lg:col-span-8">

                    {{-- Cover --}}
                    <figure class="relative flex aspect-[16/8] items-center justify-center overflow-hidden rounded-3xl bg-[var(--color-brand-50)]">

                        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-[var(--color-brand-100)]"></div>
                        <div class="absolute -bottom-10 -left-10 h-40 w-40 rounded-full bg-[var(--color-info-50)]"></div>

                        <div class="relative flex h-24 w-24 items-center justify-center rounded-3xl bg-white text-[var(--color-brand-600)] shadow-[var(--shadow-lg)]">
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

                    </figure>


                    {{-- Intro --}}
                    <div class="mt-10 rounded-2xl border border-[var(--color-brand-100)] bg-[var(--color-brand-50)] p-5 sm:p-6">
                        <p class="text-base font-semibold leading-8 text-[var(--color-brand-900)]">
                            {{ $article['excerpt'] }}
                        </p>
                    </div>


                    {{-- Content --}}
                    <div class="mt-10 max-w-none">

                        <p class="text-base leading-9 text-[var(--color-text-secondary)]">
                            آزمون تیزهوشان برای بسیاری از دانش‌آموزان یک هدف مهم در مسیر تحصیلی است.
                            اما آمادگی برای آن بهتر است به جای مطالعه فشرده و پراکنده،
                            به شکل مرحله‌ای، منظم و قابل پیگیری انجام شود.
                        </p>

                        <h2 class="mt-12 text-2xl font-black leading-9 text-[var(--color-text-primary)]">
                            از کجا باید شروع کنیم؟
                        </h2>

                        <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)]">
                            اولین قدم این است که دانش‌آموز بداند هدفش چیست و در چه مهارت‌هایی
                            نیاز به تمرین بیشتری دارد. پایه تحصیلی، سطح فعلی و زمان باقی‌مانده
                            تا آزمون روی برنامه مناسب تأثیر می‌گذارند.
                        </p>

                        <h2 class="mt-12 text-2xl font-black leading-9 text-[var(--color-text-primary)]">
                            ۱. یک برنامه قابل اجرا داشته باشید
                        </h2>

                        <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)]">
                            یک برنامه خوب لازم نیست سنگین باشد. مهم‌تر این است که دانش‌آموز بتواند
                            آن را به صورت منظم اجرا کند. زمان مشخصی برای آموزش، تمرین، تست و استراحت
                            در نظر بگیرید.
                        </p>

                        <div class="my-8 rounded-2xl border border-[var(--color-warning-100)] bg-[var(--color-warning-50)] p-5">
                            <div class="flex gap-3">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-[var(--color-warning-600)]">
                                    !
                                </span>

                                <div>
                                    <p class="text-sm font-extrabold text-[var(--color-warning-800)]">
                                        نکته مهم
                                    </p>

                                    <p class="mt-1 text-sm leading-7 text-[var(--color-warning-800)]">
                                        برنامه‌ای که هر روز بتوانی اجرا کنی، از برنامه‌ای که فقط روی کاغذ کامل به نظر می‌رسد ارزشمندتر است.
                                    </p>
                                </div>

                            </div>
                        </div>

                        <h2 class="mt-12 text-2xl font-black leading-9 text-[var(--color-text-primary)]">
                            ۲. مهارت‌های هوش را جداگانه تمرین کنید
                        </h2>

                        <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)]">
                            فقط تست زدن کافی نیست. دانش‌آموز باید مهارت‌هایی مثل تشخیص الگو،
                            استدلال منطقی، تحلیل، تجسم و حل مسئله را به صورت هدفمند تمرین کند.
                        </p>

                        <ul class="mt-6 space-y-3 text-base leading-8 text-[var(--color-text-secondary)]">
                            <li class="flex gap-3">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[var(--color-brand-600)]"></span>
                                تشخیص الگوها و روابط
                            </li>

                            <li class="flex gap-3">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[var(--color-brand-600)]"></span>
                                استدلال و حل مسئله
                            </li>

                            <li class="flex gap-3">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[var(--color-brand-600)]"></span>
                                افزایش سرعت و دقت
                            </li>

                            <li class="flex gap-3">
                                <span class="mt-2 h-2 w-2 shrink-0 rounded-full bg-[var(--color-brand-600)]"></span>
                                تحلیل پاسخ‌های اشتباه
                            </li>
                        </ul>

                        <h2 class="mt-12 text-2xl font-black leading-9 text-[var(--color-text-primary)]">
                            ۳. تست را همراه با تحلیل انجام دهید
                        </h2>

                        <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)]">
                            ارزش یک آزمون فقط در تعداد پاسخ‌های درست نیست.
                            بررسی سؤال‌های اشتباه و پیدا کردن دلیل خطا می‌تواند اطلاعات بیشتری
                            درباره نقاط ضعف و مسیر تمرین بعدی به دانش‌آموز بدهد.
                        </p>

                        <h2 class="mt-12 text-2xl font-black leading-9 text-[var(--color-text-primary)]">
                            ۴. زمان را مدیریت کنید
                        </h2>

                        <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)]">
                            در کنار یادگیری، بهتر است دانش‌آموز بخشی از تمرین‌هایش را با زمان مشخص انجام دهد
                            تا به تدریج سرعت و دقت خود را بهبود دهد.
                        </p>

                        <h2 class="mt-12 text-2xl font-black leading-9 text-[var(--color-text-primary)]">
                            ۵. پیشرفت را هر هفته بررسی کنید
                        </h2>

                        <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)]">
                            در پایان هر هفته سه سؤال ساده از خودتان بپرسید:
                            چه چیزهایی یاد گرفتم؟ کجا اشتباه بیشتری دارم؟ قدم بعدی چیست؟
                            همین بررسی ساده می‌تواند مسیر مطالعه را بسیار منظم‌تر کند.
                        </p>

                        {{-- Summary --}}
                        <div class="my-10 rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] p-5 sm:p-6">
                            <h3 class="text-base font-extrabold text-[var(--color-success-800)]">
                                جمع‌بندی
                            </h3>

                            <p class="mt-2 text-sm leading-8 text-[var(--color-success-800)]">
                                آمادگی موفق برای تیزهوشان ترکیبی از آموزش، تمرین، آزمون،
                                تحلیل اشتباهات و پیگیری منظم پیشرفت است.
                            </p>
                        </div>

                    </div>


                    {{-- Tags --}}
                    <div class="mt-10 border-t border-[var(--color-border)] pt-6">

                        <p class="text-sm font-bold text-[var(--color-text-primary)]">
                            برچسب‌ها
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach($tags as $tag)
                                <x-ui.badge
                                    variant="neutral"
                                    size="sm"
                                >
                                    {{ $tag }}
                                </x-ui.badge>
                            @endforeach
                        </div>

                    </div>


                    {{-- Update info --}}
                    <div class="mt-8 flex flex-col gap-4 rounded-2xl bg-[var(--color-neutral-50)] p-5 sm:flex-row sm:items-center sm:justify-between">

                        <div>
                            <p class="text-xs text-[var(--color-text-muted)]">
                                آخرین به‌روزرسانی
                            </p>

                            <p class="mt-1 text-sm font-semibold text-[var(--color-text-primary)]">
                                {{ $article['updatedAt'] }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <x-ui.badge variant="brand">
                                {{ $article['category'] }}
                            </x-ui.badge>

                            <span class="text-xs text-[var(--color-text-muted)]">
                                {{ $article['readTime'] }} مطالعه
                            </span>
                        </div>

                    </div>

                </article>


                {{-- =====================================================
                    SIDEBAR
                ====================================================== --}}
                <aside class="lg:col-span-4">

                    <div class="sticky top-24 space-y-5">

                        {{-- Related Course --}}
                        <div class="rounded-2xl border border-[var(--color-brand-100)] bg-[var(--color-brand-50)] p-5">

                            <x-ui.badge variant="brand">
                                قدم بعدی
                            </x-ui.badge>

                            <h2 class="mt-4 text-xl font-black leading-8 text-[var(--color-text-primary)]">
                                فقط درباره آمادگی نخوان؛ تمرینش کن
                            </h2>

                            <p class="mt-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                                اگر پایه ششم هستی و می‌خواهی آمادگی تیزهوشان را جدی‌تر دنبال کنی،
                                دوره‌های تخصصی فرزین را ببین.
                            </p>

                            <div class="mt-5">
                                <x-ui.button
                                    :href="route('courses.index')"
                                    full-width
                                >
                                    مشاهده دوره‌ها
                                </x-ui.button>
                            </div>

                        </div>


                        {{-- Article Info --}}
                        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">

                            <h2 class="text-base font-extrabold text-[var(--color-text-primary)]">
                                اطلاعات مقاله
                            </h2>

                            <dl class="mt-5 space-y-4">

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        دسته‌بندی
                                    </dt>

                                    <dd>
                                        <x-ui.badge
                                            variant="brand"
                                            size="sm"
                                        >
                                            {{ $article['category'] }}
                                        </x-ui.badge>
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        زمان مطالعه
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $article['readTime'] }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        تاریخ انتشار
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $article['publishedAt'] }}
                                    </dd>
                                </div>

                            </dl>

                        </div>


                        {{-- Author --}}
                        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5">

                            <p class="text-xs font-medium text-[var(--color-text-muted)]">
                                نویسنده
                            </p>

                            <div class="mt-4 flex items-center gap-3">

                                <x-ui.avatar
                                    name="{{ $article['author'] }}"
                                    size="md"
                                />

                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $article['author'] }}
                                    </p>

                                    <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                        {{ $article['authorRole'] }}
                                    </p>
                                </div>

                            </div>

                        </div>

                    </div>

                </aside>

            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        RELATED ARTICLES
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="ادامه مطالعه"
                title="مقالات مرتبط"
                description="اگر این مطلب برایت مفید بود، این مقاله‌ها را هم ببین."
            />

            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                @foreach($relatedArticles as $related)

                    <article class="group flex h-full flex-col rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-xs)] transition-all duration-200 hover:-translate-y-1 hover:border-[var(--color-brand-200)] hover:shadow-[var(--shadow-md)]">

                        <div class="flex items-center justify-between gap-3">

                            <x-ui.badge
                                variant="brand"
                                size="sm"
                            >
                                {{ $related['category'] }}
                            </x-ui.badge>

                            <span class="text-xs text-[var(--color-text-muted)]">
                                {{ $related['readTime'] }}
                            </span>

                        </div>

                        <a
                            href="{{ route('blog.show', $related['slug']) }}"
                            class="mt-4 block"
                        >
                            <h3 class="line-clamp-2 text-lg font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                                {{ $related['title'] }}
                            </h3>
                        </a>

                        <p class="mt-2 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                            {{ $related['excerpt'] }}
                        </p>

                        <div class="mt-auto flex items-center justify-between gap-4 border-t border-[var(--color-border)] pt-4 mt-5">

                            <span class="text-xs text-[var(--color-text-muted)]">
                                {{ $related['date'] }}
                            </span>

                            <a
                                href="{{ route('blog.show', $related['slug']) }}"
                                class="text-xs font-bold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
                            >
                                مطالعه ←
                            </a>

                        </div>

                    </article>

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

            <div class="rounded-3xl bg-[var(--color-brand-600)] px-6 py-12 text-center sm:px-10 sm:py-16">

                <x-ui.badge
                    variant="neutral"
                    class="bg-white/10 text-white"
                >
                    مسیر یادگیری فرزین
                </x-ui.badge>

                <h2 class="mx-auto mt-5 max-w-3xl text-3xl font-black leading-tight text-white sm:text-4xl">
                    آماده‌ای یادگیری را جدی‌تر ادامه بدهی؟
                </h2>

                <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-white/80 sm:text-base">
                    دوره‌ای متناسب با پایه و هدفت پیدا کن و آموزش را در یک مسیر منظم ادامه بده.
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">

                    <x-ui.button
                        :href="route('courses.index')"
                        variant="secondary"
                        size="lg"
                    >
                        مشاهده دوره‌ها
                    </x-ui.button>

                    <x-ui.button
                        :href="route('blog.index')"
                        variant="ghost"
                        size="lg"
                        class="text-white hover:bg-white/10"
                    >
                        برگشت به مجله
                    </x-ui.button>

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection
