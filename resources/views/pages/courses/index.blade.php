@extends('layouts.app')

@section('title', 'دوره‌ها')

@section('description', 'دوره‌های تخصصی فرزین برای آمادگی تیزهوشان و تقویت مهارت‌های دانش‌آموزان.')

@php
    $courses = [
        [
            'title' => 'دوره جامع آمادگی تیزهوشان ششم',
            'description' => 'آموزش مفهومی و تستی هوش، استعداد تحلیلی و مهارت‌های لازم برای آزمون.',
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
            'description' => 'حل تمرین و تست‌های استاندارد با تمرکز بر سرعت و دقت.',
            'grade' => 'پایه ششم',
            'subject' => 'استعداد تحلیلی',
            'level' => 'متوسط',
            'teacher' => 'استاد علی رضایی',
            'sessions' => 18,
            'duration' => '21 ساعت',
            'price' => '۱٬۹۵۰٬۰۰۰ تومان',
            'oldPrice' => null,
            'discount' => null,
            'featured' => false,
        ],
        [
            'title' => 'هوش کلامی و منطقی',
            'description' => 'تقویت استدلال، تحلیل و مهارت حل سؤالات هوش کلامی.',
            'grade' => 'پایه پنجم',
            'subject' => 'هوش',
            'level' => 'مقدماتی',
            'teacher' => 'استاد سارا محمدی',
            'sessions' => 16,
            'duration' => '18 ساعت',
            'price' => '۱٬۶۵۰٬۰۰۰ تومان',
            'oldPrice' => null,
            'discount' => null,
            'featured' => false,
        ],
        [
            'title' => 'ریاضی ویژه تیزهوشان ششم',
            'description' => 'آموزش نکته‌ای و تستی ریاضی با تمرکز روی سؤالات سطح بالا.',
            'grade' => 'پایه ششم',
            'subject' => 'ریاضی',
            'level' => 'پیشرفته',
            'teacher' => 'استاد رضا کریمی',
            'sessions' => 20,
            'duration' => '26 ساعت',
            'price' => '۲٬۱۵۰٬۰۰۰ تومان',
            'oldPrice' => null,
            'discount' => null,
            'featured' => false,
        ],
        [
            'title' => 'آمادگی تیزهوشان پنجم',
            'description' => 'شروع اصولی مسیر آمادگی برای دانش‌آموزان پایه پنجم.',
            'grade' => 'پایه پنجم',
            'subject' => 'آمادگی تیزهوشان',
            'level' => 'متوسط',
            'teacher' => 'استاد مهدی مرادی',
            'sessions' => 22,
            'duration' => '28 ساعت',
            'price' => '۲٬۳۰۰٬۰۰۰ تومان',
            'oldPrice' => '۲٬۶۰۰٬۰۰۰ تومان',
            'discount' => 10,
            'featured' => false,
        ],
        [
            'title' => 'هوش تصویری و تجسمی',
            'description' => 'تمرین الگوها، اشکال و سؤالات تصویری با تکنیک‌های سریع.',
            'grade' => 'پایه چهارم',
            'subject' => 'هوش تصویری',
            'level' => 'مقدماتی',
            'teacher' => 'استاد نگار احمدی',
            'sessions' => 14,
            'duration' => '16 ساعت',
            'price' => '۱٬۴۵۰٬۰۰۰ تومان',
            'oldPrice' => null,
            'discount' => null,
            'featured' => false,
        ],
    ];
@endphp

@section('content')

    {{-- Header --}}
    <x-layout.section
        spacing="default"
        class="pb-6"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="مسیرهای آموزشی"
                title="دوره مناسب خودت را پیدا کن"
                description="دوره‌های فرزین را بر اساس پایه، درس و سطح آموزشی بررسی کن و مسیر مناسب خودت را انتخاب کن."
            />

        </x-layout.container>
    </x-layout.section>

    {{-- Search + Filters --}}
    <section class="border-y border-[var(--color-border)] bg-[var(--color-surface)]">
        <x-layout.container>

            <div class="flex flex-col gap-4 py-5 lg:flex-row lg:items-center lg:justify-between">

                {{-- Search --}}
                <div class="w-full lg:max-w-md">
                    <label for="course-search" class="sr-only">
                        جستجوی دوره
                    </label>

                    <div class="relative">
                        <svg
                            class="pointer-events-none absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-[var(--color-text-muted)]"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            aria-hidden="true"
                        >
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>

                        <input
                            id="course-search"
                            type="search"
                            placeholder="جستجوی دوره، درس یا مدرس..."
                            class="ui-input pr-10"
                        />
                    </div>
                </div>

                {{-- Filters --}}
                <div class="flex flex-col gap-3 sm:flex-row">

                    <x-ui.select
                        name="grade"
                        aria-label="فیلتر بر اساس پایه"
                    >
                        <option value="">همه پایه‌ها</option>
                        <option value="4">پایه چهارم</option>
                        <option value="5">پایه پنجم</option>
                        <option value="6">پایه ششم</option>
                        <option value="7">پایه هفتم</option>
                        <option value="8">پایه هشتم</option>
                        <option value="9">پایه نهم</option>
                    </x-ui.select>

                    <x-ui.select
                        name="subject"
                        aria-label="فیلتر بر اساس موضوع"
                    >
                        <option value="">همه موضوعات</option>
                        <option value="هوش">هوش</option>
                        <option value="ریاضی">ریاضی</option>
                        <option value="استعداد تحلیلی">استعداد تحلیلی</option>
                        <option value="آمادگی تیزهوشان">آمادگی تیزهوشان</option>
                    </x-ui.select>

                    <x-ui.select
                        name="level"
                        aria-label="فیلتر بر اساس سطح"
                    >
                        <option value="">همه سطوح</option>
                        <option value="مقدماتی">مقدماتی</option>
                        <option value="متوسط">متوسط</option>
                        <option value="پیشرفته">پیشرفته</option>
                    </x-ui.select>

                </div>
            </div>

        </x-layout.container>
    </section>

    {{-- Course List --}}
    <x-layout.section spacing="lg">
        <x-layout.container>

            {{-- Result heading --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                <div>
                    <h2 class="text-lg font-extrabold text-[var(--color-text-primary)]">
                        همه دوره‌ها
                    </h2>

                    <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                        {{ count($courses) }} دوره پیدا شد
                    </p>
                </div>

                <div class="w-full sm:w-48">
                    <x-ui.select
                        name="sort"
                        aria-label="مرتب‌سازی"
                    >
                        <option value="popular">
                            محبوب‌ترین
                        </option>

                        <option value="newest">
                            جدیدترین
                        </option>

                        <option value="price_asc">
                            ارزان‌ترین
                        </option>

                        <option value="price_desc">
                            گران‌ترین
                        </option>
                    </x-ui.select>
                </div>

            </div>

            {{-- Grid --}}
            <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

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
                        :old-price="$course['oldPrice']"
                        :discount="$course['discount']"
                        :featured="$course['featured']"
                        href="#"
                    />
                @endforeach

            </div>

            {{-- Empty State --}}
            @if(count($courses) === 0)
                <div class="mt-8">
                    <x-ui.empty-state
                        icon="search"
                        title="دوره‌ای پیدا نشد"
                        description="فیلترها یا عبارت جستجو را تغییر بده و دوباره امتحان کن."
                    />
                </div>
            @endif

            {{-- Pagination --}}
            @if(count($courses) > 0)
                <div class="mt-10">
                    <div class="flex justify-center">
                        {{-- Temporary UI pagination --}}
                        <nav
                            class="inline-flex items-center gap-1 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] p-1.5 shadow-[var(--shadow-xs)]"
                            aria-label="صفحه‌بندی دوره‌ها"
                            dir="rtl"
                        >
                            <button
                                type="button"
                                disabled
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm text-[var(--color-text-disabled)]"
                            >
                                قبلی
                            </button>

                            <button
                                type="button"
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg bg-[var(--color-brand-600)] px-2.5 text-sm font-bold text-white"
                                aria-current="page"
                            >
                                ۱
                            </button>

                            <button
                                type="button"
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-neutral-100)]"
                            >
                                ۲
                            </button>

                            <button
                                type="button"
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-neutral-100)]"
                            >
                                ۳
                            </button>

                            <button
                                type="button"
                                class="flex h-9 min-w-9 items-center justify-center rounded-lg px-2.5 text-sm font-medium text-[var(--color-text-secondary)] transition-colors hover:bg-[var(--color-neutral-100)]"
                            >
                                بعدی
                            </button>
                        </nav>
                    </div>
                </div>
            @endif

        </x-layout.container>
    </x-layout.section>

@endsection
