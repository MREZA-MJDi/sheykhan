@extends('layouts.app')

@section('title', 'دوره جامع آمادگی تیزهوشان ششم')

@section('description', 'دوره جامع آمادگی تیزهوشان ششم با آموزش هوش، استعداد تحلیلی، تمرین و آزمون.')

@php
    /*
    |--------------------------------------------------------------------------
    | Temporary UI Data
    |--------------------------------------------------------------------------
    */

    $course = [
        'title' => 'دوره جامع آمادگی تیزهوشان ششم',
        'shortDescription' => 'یک مسیر کامل و هدفمند برای تقویت هوش و استعداد تحلیلی و آمادگی بهتر برای آزمون تیزهوشان.',
        'description' => 'در این دوره از مفاهیم پایه شروع می‌کنیم و قدم‌به‌قدم به سراغ تکنیک‌های حل سؤال، تمرین، تست و ارزیابی می‌رویم تا دانش‌آموز بتواند با برنامه‌ای مشخص مسیر یادگیری خود را دنبال کند.',
        'grade' => 'پایه ششم',
        'subject' => 'هوش و استعداد',
        'level' => 'پیشرفته',
        'sessions' => 24,
        'duration' => '32 ساعت',
        'students' => '۲٬۴۰۰',
        'rating' => '4.9',
        'reviews' => '۱۸۶',
        'price' => '۲٬۴۵۰٬۰۰۰ تومان',
        'oldPrice' => '۲٬۹۰۰٬۰۰۰ تومان',
        'discount' => 15,
        'image' => null,
    ];

    $teacher = [
        'name' => 'دکتر محمد احمدی',
        'specialty' => 'مدرس هوش و استعداد تحلیلی',
        'experience' => 12,
        'students' => '۲٬۴۰۰',
        'courses' => 8,
        'rating' => '4.9',
        'verified' => true,
    ];

    $lessons = [
        [
            'number' => 1,
            'title' => 'آشنایی با ساختار آزمون تیزهوشان',
            'duration' => '۳۵ دقیقه',
            'type' => 'video',
            'status' => 'available',
        ],
        [
            'number' => 2,
            'title' => 'مبانی هوش کلامی',
            'duration' => '۴۲ دقیقه',
            'type' => 'video',
            'status' => 'available',
        ],
        [
            'number' => 3,
            'title' => 'تکنیک‌های حل سریع سؤالات منطقی',
            'duration' => '۵۰ دقیقه',
            'type' => 'video',
            'status' => 'available',
        ],
        [
            'number' => 4,
            'title' => 'هوش تصویری و تجسمی',
            'duration' => '۴۵ دقیقه',
            'type' => 'video',
            'status' => 'available',
        ],
        [
            'number' => 5,
            'title' => 'الگوهای عددی و منطقی',
            'duration' => '۴۸ دقیقه',
            'type' => 'video',
            'status' => 'available',
        ],
        [
            'number' => 6,
            'title' => 'تمرین و آزمونک جلسه اول',
            'duration' => '۲۰ دقیقه',
            'type' => 'quiz',
            'status' => 'locked',
        ],
    ];

    $faqs = [
        [
            'id' => 1,
            'question' => 'این دوره برای چه دانش‌آموزانی مناسب است؟',
            'answer' => 'این دوره برای دانش‌آموزان پایه ششم طراحی شده که قصد تقویت مهارت‌های هوش و استعداد تحلیلی و آمادگی برای آزمون تیزهوشان را دارند.',
        ],
        [
            'id' => 2,
            'question' => 'دوره شامل چه محتوایی است؟',
            'answer' => 'دوره شامل ویدئوهای آموزشی، تمرین، آزمونک و محتوای تکمیلی است که بر اساس یک مسیر آموزشی مشخص ارائه می‌شوند.',
        ],
        [
            'id' => 3,
            'question' => 'آیا بعد از ثبت‌نام به همه جلسات دسترسی دارم؟',
            'answer' => 'پس از ثبت‌نام، محتوای قابل ارائه دوره طبق برنامه آموزشی برای دانش‌آموز فعال می‌شود.',
        ],
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
                        'label' => 'دوره‌ها',
                        'url' => route('courses.index'),
                    ],
                    [
                        'label' => $course['title'],
                    ],
                ]"
            />
        </x-layout.container>
    </x-layout.section>

    {{-- =========================================================
        COURSE HERO
    ========================================================== --}}
    <section class="border-b border-[var(--color-border)] bg-[var(--color-surface)]">
        <x-layout.container>
            <div class="py-8 sm:py-12 lg:py-14">

                <div class="grid gap-10 lg:grid-cols-12 lg:items-start lg:gap-12">

                    {{-- Main content --}}
                    <div class="lg:col-span-8">

                        {{-- Badges --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge variant="brand">
                                {{ $course['grade'] }}
                            </x-ui.badge>

                            <x-ui.badge variant="neutral">
                                {{ $course['subject'] }}
                            </x-ui.badge>

                            <x-ui.badge variant="warning">
                                {{ $course['level'] }}
                            </x-ui.badge>

                            <x-ui.badge variant="success">
                                {{ $course['discount'] }}٪ تخفیف
                            </x-ui.badge>
                        </div>

                        {{-- Title --}}
                        <h1 class="mt-5 max-w-4xl text-3xl font-black leading-[1.45] tracking-tight text-[var(--color-text-primary)] sm:text-4xl lg:text-5xl">
                            {{ $course['title'] }}
                        </h1>

                        {{-- Description --}}
                        <p class="mt-5 max-w-3xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg">
                            {{ $course['shortDescription'] }}
                        </p>

                        {{-- Rating --}}
                        <div class="mt-6 flex flex-wrap items-center gap-4">

                            <div class="flex items-center gap-2">
                                <span class="text-base font-extrabold text-[var(--color-text-primary)]">
                                    {{ $course['rating'] }}
                                </span>

                                <span class="flex items-center gap-0.5 text-[var(--color-warning-500)]">
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

                                <span class="text-sm text-[var(--color-text-muted)]">
                                    ({{ $course['reviews'] }} نظر)
                                </span>
                            </div>

                            <span class="h-1 w-1 rounded-full bg-[var(--color-neutral-300)]"></span>

                            <span class="text-sm text-[var(--color-text-muted)]">
                                {{ $course['students'] }} دانش‌آموز
                            </span>

                        </div>

                        {{-- Facts --}}
                        <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">

                            <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                <p class="text-xs text-[var(--color-text-muted)]">
                                    جلسات
                                </p>

                                <p class="mt-1 text-sm font-extrabold text-[var(--color-text-primary)]">
                                    {{ $course['sessions'] }} جلسه
                                </p>
                            </div>

                            <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                <p class="text-xs text-[var(--color-text-muted)]">
                                    مدت
                                </p>

                                <p class="mt-1 text-sm font-extrabold text-[var(--color-text-primary)]">
                                    {{ $course['duration'] }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                <p class="text-xs text-[var(--color-text-muted)]">
                                    سطح
                                </p>

                                <p class="mt-1 text-sm font-extrabold text-[var(--color-text-primary)]">
                                    {{ $course['level'] }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                <p class="text-xs text-[var(--color-text-muted)]">
                                    نوع آموزش
                                </p>

                                <p class="mt-1 text-sm font-extrabold text-[var(--color-text-primary)]">
                                    آنلاین
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- Purchase card --}}
                    <div class="lg:col-span-4">

                        <div class="sticky top-24 overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[var(--shadow-md)]">

                            {{-- Image --}}
                            <div class="relative overflow-hidden bg-[var(--color-brand-50)]">
                                @if($course['image'])
                                    <img
                                        src="{{ $course['image'] }}"
                                        alt="{{ $course['title'] }}"
                                        class="aspect-[16/9] w-full object-cover"
                                    >
                                @else
                                    <div class="flex aspect-[16/9] items-center justify-center">
                                        <svg
                                            class="h-16 w-16 text-[var(--color-brand-300)]"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            aria-hidden="true"
                                        >
                                            <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                                            <path d="M8 9h8M8 13h6M8 17h4" />
                                        </svg>
                                    </div>
                                @endif

                                <div class="absolute right-4 top-4">
                                    <x-ui.badge variant="danger">
                                        {{ $course['discount'] }}٪ تخفیف
                                    </x-ui.badge>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">

                                {{-- Price --}}
                                <div>
                                    <p class="text-xs text-[var(--color-text-muted)]">
                                        قیمت دوره
                                    </p>

                                    <div class="mt-1 flex flex-wrap items-end gap-3">
                                        <span class="text-2xl font-black text-[var(--color-text-primary)]">
                                            {{ $course['price'] }}
                                        </span>

                                        <span class="text-sm text-[var(--color-text-muted)] line-through">
                                            {{ $course['oldPrice'] }}
                                        </span>
                                    </div>
                                </div>

                                {{-- CTA --}}
                                <div class="mt-5">
                                    <x-ui.button
                                        href="#"
                                        size="lg"
                                        full-width
                                    >
                                        ثبت‌نام در دوره

                                        <svg
                                            class="h-4.5 w-4.5"
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

                                <p class="mt-3 text-center text-xs leading-5 text-[var(--color-text-muted)]">
                                    دسترسی به محتوای دوره پس از ثبت‌نام فعال می‌شود.
                                </p>

                                {{-- Includes --}}
                                <div class="mt-6 border-t border-[var(--color-border)] pt-5">
                                    <p class="text-sm font-bold text-[var(--color-text-primary)]">
                                        این دوره شامل:
                                    </p>

                                    <ul class="mt-4 space-y-3">

                                        <li class="flex items-center gap-2 text-sm text-[var(--color-text-secondary)]">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                                ✓
                                            </span>

                                            {{ $course['sessions'] }} جلسه آموزشی
                                        </li>

                                        <li class="flex items-center gap-2 text-sm text-[var(--color-text-secondary)]">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                                ✓
                                            </span>

                                            تمرین و آزمونک
                                        </li>

                                        <li class="flex items-center gap-2 text-sm text-[var(--color-text-secondary)]">
                                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                                                ✓
                                            </span>

                                            پیگیری پیشرفت
                                        </li>

                                    </ul>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </x-layout.container>
    </section>

    {{-- =========================================================
        COURSE CONTENT
    ========================================================== --}}
    <x-layout.section spacing="lg">
        <x-layout.container>

            <div class="grid gap-10 lg:grid-cols-12 lg:gap-12">

                {{-- Main --}}
                <div class="min-w-0 lg:col-span-8">

                    <x-ui.tabs
                        :tabs="[
                            [
                                'id' => 'overview',
                                'label' => 'معرفی دوره',
                            ],
                            [
                                'id' => 'lessons',
                                'label' => 'سرفصل‌ها',
                                'badge' => count($lessons),
                            ],
                            [
                                'id' => 'teacher',
                                'label' => 'مدرس',
                            ],
                            [
                                'id' => 'faq',
                                'label' => 'سوالات متداول',
                            ],
                        ]"
                        active="overview"
                    >

                        {{-- Overview --}}
                        <div x-show="active === 'overview'" x-cloak>

                            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">
                                <h2 class="text-xl font-extrabold text-[var(--color-text-primary)]">
                                    درباره این دوره
                                </h2>

                                <p class="mt-5 text-sm leading-8 text-[var(--color-text-secondary)]">
                                    {{ $course['description'] }}
                                </p>

                                <div class="mt-8 grid gap-4 sm:grid-cols-2">

                                    <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                        <p class="text-xs text-[var(--color-text-muted)]">
                                            مناسب برای
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-[var(--color-text-primary)]">
                                            دانش‌آموزان پایه ششم
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                        <p class="text-xs text-[var(--color-text-muted)]">
                                            هدف دوره
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-[var(--color-text-primary)]">
                                            آمادگی تیزهوشان
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                        <p class="text-xs text-[var(--color-text-muted)]">
                                            تمرکز اصلی
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-[var(--color-text-primary)]">
                                            هوش و استعداد تحلیلی
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-[var(--color-neutral-50)] p-4">
                                        <p class="text-xs text-[var(--color-text-muted)]">
                                            شیوه برگزاری
                                        </p>

                                        <p class="mt-1 text-sm font-bold text-[var(--color-text-primary)]">
                                            آنلاین
                                        </p>
                                    </div>

                                </div>
                            </div>

                        </div>

                        {{-- Lessons --}}
                        <div x-show="active === 'lessons'" x-cloak>

                            <div class="space-y-3">
                                @foreach($lessons as $lesson)
                                    <x-education.lesson-card
                                        :number="$lesson['number']"
                                        :title="$lesson['title']"
                                        :duration="$lesson['duration']"
                                        :type="$lesson['type']"
                                        :status="$lesson['status']"
                                        href="#"
                                    />
                                @endforeach
                            </div>

                        </div>

                        {{-- Teacher --}}
                        <div x-show="active === 'teacher'" x-cloak>

                            <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 sm:p-6">

                                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                                    <x-ui.avatar
                                        :name="$teacher['name']"
                                        size="xl"
                                        class="ring-4 ring-[var(--color-brand-50)]"
                                    />

                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h2 class="text-xl font-extrabold text-[var(--color-text-primary)]">
                                                {{ $teacher['name'] }}
                                            </h2>

                                            @if($teacher['verified'])
                                                <x-ui.badge variant="brand">
                                                    مدرس تأییدشده
                                                </x-ui.badge>
                                            @endif
                                        </div>

                                        <p class="mt-1 text-sm text-[var(--color-text-secondary)]">
                                            {{ $teacher['specialty'] }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-8 grid grid-cols-3 divide-x divide-[var(--color-border)] divide-x-reverse border-y border-[var(--color-border)] py-5">

                                    <div class="px-3 text-center">
                                        <p class="text-lg font-black text-[var(--color-text-primary)]">
                                            {{ $teacher['experience'] }}
                                        </p>

                                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                            سال تجربه
                                        </p>
                                    </div>

                                    <div class="px-3 text-center">
                                        <p class="text-lg font-black text-[var(--color-text-primary)]">
                                            {{ $teacher['courses'] }}
                                        </p>

                                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                            دوره
                                        </p>
                                    </div>

                                    <div class="px-3 text-center">
                                        <p class="text-lg font-black text-[var(--color-text-primary)]">
                                            {{ $teacher['students'] }}
                                        </p>

                                        <p class="mt-1 text-xs text-[var(--color-text-muted)]">
                                            دانش‌آموز
                                        </p>
                                    </div>

                                </div>

                                <div class="mt-6">
                                    <x-ui.button
                                        href="#"
                                        variant="secondary"
                                    >
                                        مشاهده پروفایل مدرس
                                    </x-ui.button>
                                </div>

                            </div>

                        </div>

                        {{-- FAQ --}}
                        <div x-show="active === 'faq'" x-cloak>

                            <div
                                x-data="{ activeFaq: 1 }"
                                class="space-y-3"
                            >
                                @foreach($faqs as $faq)
                                    <div class="overflow-hidden rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)]">

                                        <button
                                            type="button"
                                            class="flex w-full items-center justify-between gap-4 px-5 py-5 text-right"
                                            @click="activeFaq = activeFaq === {{ $faq['id'] }} ? null : {{ $faq['id'] }}"
                                            :aria-expanded="activeFaq === {{ $faq['id'] }}"
                                            aria-controls="course-faq-{{ $faq['id'] }}"
                                        >
                                            <span class="text-sm font-bold text-[var(--color-text-primary)]">
                                                {{ $faq['question'] }}
                                            </span>

                                            <svg
                                                class="h-5 w-5 shrink-0 text-[var(--color-text-muted)] transition-transform duration-200"
                                                :class="{ 'rotate-180': activeFaq === {{ $faq['id'] }} }"
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
                                            id="course-faq-{{ $faq['id'] }}"
                                            x-show="activeFaq === {{ $faq['id'] }}"
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

                        </div>

                    </x-ui.tabs>

                </div>

                {{-- Sidebar --}}
                <aside class="lg:col-span-4">

                    <div class="sticky top-24 space-y-5">

                        {{-- Course summary --}}
                        <div class="rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-xs)]">
                            <h3 class="text-base font-extrabold text-[var(--color-text-primary)]">
                                خلاصه دوره
                            </h3>

                            <dl class="mt-5 space-y-4">

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        تعداد جلسات
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $course['sessions'] }} جلسه
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        مدت دوره
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $course['duration'] }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        سطح
                                    </dt>

                                    <dd>
                                        <x-ui.badge variant="warning" size="sm">
                                            {{ $course['level'] }}
                                        </x-ui.badge>
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-[var(--color-text-muted)]">
                                        امتیاز
                                    </dt>

                                    <dd class="text-sm font-bold text-[var(--color-text-primary)]">
                                        {{ $course['rating'] }}
                                    </dd>
                                </div>

                            </dl>
                        </div>

                        {{-- Trust card --}}
                        <div class="rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] p-5">
                            <div class="flex gap-3">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-[var(--color-success-600)]">
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
                                </div>

                                <div>
                                    <p class="text-sm font-bold text-[var(--color-success-700)]">
                                        مسیر آموزشی منظم
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-[var(--color-success-700)]/80">
                                        آموزش، تمرین و ارزیابی در یک مسیر مشخص.
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
        RELATED COURSES
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-background)]"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="پیشنهاد فرزین"
                title="دوره‌های مرتبط"
                description="اگر این دوره مناسب مسیر توست، این دوره‌ها را هم بررسی کن."
            />

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                <x-education.course-card
                    title="هوش و استعداد تحلیلی"
                    description="تقویت مهارت تحلیل و حل تست‌های استاندارد."
                    grade="پایه ششم"
                    subject="استعداد تحلیلی"
                    level="متوسط"
                    teacher="استاد علی رضایی"
                    sessions="۱۸"
                    duration="۲۱ ساعت"
                    price="۱٬۹۵۰٬۰۰۰ تومان"
                    href="#"
                />

                <x-education.course-card
                    title="هوش کلامی و منطقی"
                    description="تقویت استدلال و مهارت حل سؤالات هوش."
                    grade="پایه پنجم"
                    subject="هوش"
                    level="مقدماتی"
                    teacher="استاد سارا محمدی"
                    sessions="۱۶"
                    duration="۱۸ ساعت"
                    price="۱٬۶۵۰٬۰۰۰ تومان"
                    href="#"
                />

                <x-education.course-card
                    title="ریاضی ویژه تیزهوشان ششم"
                    description="آموزش نکته‌ای و تستی ریاضی در سطح پیشرفته."
                    grade="پایه ششم"
                    subject="ریاضی"
                    level="پیشرفته"
                    teacher="استاد رضا کریمی"
                    sessions="۲۰"
                    duration="۲۶ ساعت"
                    price="۲٬۱۵۰٬۰۰۰ تومان"
                    href="#"
                />

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection
