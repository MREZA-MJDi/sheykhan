@extends('layouts.app')

@section('title', 'اساتید فرزین')

@section('description', 'آشنایی با اساتید متخصص فرزین در حوزه آموزش و آمادگی آزمون‌های تیزهوشان.')

@php
    $teachers = [
        [
            'slug' => 'mohammad-ahmadi',
            'name' => 'دکتر محمد احمدی',
            'avatar' => asset('images/teachers/mohammad-ahmadi.jpg'),
            'specialty' => 'مدرس هوش و استعداد تحلیلی',
            'bio' => 'متخصص آموزش هوش و استعداد تحلیلی با تمرکز بر آموزش مفهومی و حل مسئله.',
            'experience' => 12,
            'coursesCount' => 8,
            'studentsCount' => '۲٬۴۰۰',
            'rating' => '4.9',
            'verified' => true,
        ],
        [
            'slug' => 'ali-rezaei',
            'name' => 'استاد علی رضایی',
            'avatar' => asset('images/teachers/ali-rezaei.jpg'),
            'specialty' => 'مدرس استعداد منطقی',
            'bio' => 'مدرس دوره‌های آمادگی تیزهوشان با تمرکز بر تکنیک‌های سرعت و دقت.',
            'experience' => 9,
            'coursesCount' => 6,
            'studentsCount' => '۱٬۸۰۰',
            'rating' => '4.8',
            'verified' => true,
        ],
        [
            'slug' => 'sara-mohammadi',
            'name' => 'استاد سارا محمدی',
            'avatar' => asset('images/teachers/sara-mohammadi.jpg'),
            'specialty' => 'مدرس هوش کلامی',
            'bio' => 'مدرس هوش کلامی و منطقی با رویکرد تمرین‌محور و حل سؤال.',
            'experience' => 7,
            'coursesCount' => 5,
            'studentsCount' => '۱٬۲۰۰',
            'rating' => '4.9',
            'verified' => true,
        ],
        [
            'slug' => 'reza-karimi',
            'name' => 'استاد رضا کریمی',
            'avatar' => asset('images/teachers/reza-karimi.jpg'),
            'specialty' => 'مدرس ریاضی تیزهوشان',
            'bio' => 'مدرس ریاضی با تمرکز بر تست‌های سطح بالا و تکنیک‌های حل سریع.',
            'experience' => 10,
            'coursesCount' => 7,
            'studentsCount' => '۱٬۶۰۰',
            'rating' => '4.8',
            'verified' => true,
        ],
        [
            'slug' => 'mehdi-moradi',
            'name' => 'استاد مهدی مرادی',
            'avatar' => asset('images/teachers/mehdi-moradi.jpg'),
            'specialty' => 'مدرس آمادگی تیزهوشان',
            'bio' => 'فعال در حوزه برنامه‌ریزی آموزشی و آمادگی آزمون‌های استعدادهای درخشان.',
            'experience' => 11,
            'coursesCount' => 9,
            'studentsCount' => '۲٬۱۰۰',
            'rating' => '4.9',
            'verified' => true,
        ],
        [
            'slug' => 'negar-ahmadi',
            'name' => 'استاد نگار احمدی',
            'avatar' => asset('images/teachers/negar-ahmadi.jpg'),
            'specialty' => 'مدرس هوش تصویری',
            'bio' => 'متخصص آموزش هوش تصویری، تجسمی و حل الگوهای تصویری.',
            'experience' => 7,
            'coursesCount' => 5,
            'studentsCount' => '۱٬۱۰۰',
            'rating' => '4.7',
            'verified' => false,
        ],
    ];
@endphp

@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}
    <x-layout.section
        spacing="default"
        class="pb-6"
    >
        <x-layout.container>

            <x-layout.page-header
                eyebrow="تیم آموزشی"
                title="اساتید فرزین"
                description="با مدرسین متخصص فرزین آشنا شوید و بر اساس تخصص، تجربه و امتیاز، مدرس مناسب مسیر یادگیری خودتان را پیدا کنید."
            />

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        SEARCH / FILTERS
    ========================================================== --}}
    <section class="border-y border-[var(--color-border)] bg-[var(--color-surface)]">
        <x-layout.container>

            <form
                method="GET"
                action="{{ route('teachers.index') }}"
                class="flex flex-col gap-4 py-5 lg:flex-row lg:items-center lg:justify-between"
            >

                {{-- Search --}}
                <div class="w-full lg:max-w-md">
                    <label
                        for="teacher-search"
                        class="sr-only"
                    >
                        جستجوی مدرس
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
                            id="teacher-search"
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="جستجوی نام یا تخصص مدرس..."
                            autocomplete="off"
                            class="ui-input pr-10"
                        />

                    </div>
                </div>


                {{-- Filters --}}
                <div class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">

                    <div class="min-w-0 sm:min-w-44">
                        <x-ui.select
                            name="specialty"
                            aria-label="فیلتر بر اساس تخصص"
                        >
                            <option value="">
                                همه تخصص‌ها
                            </option>

                            <option
                                value="هوش"
                                @selected(request('specialty') === 'هوش')
                            >
                            هوش
                            </option>

                            <option
                                value="استعداد تحلیلی"
                                @selected(request('specialty') === 'استعداد تحلیلی')
                            >
                            استعداد تحلیلی
                            </option>

                            <option
                                value="ریاضی"
                                @selected(request('specialty') === 'ریاضی')
                            >
                            ریاضی
                            </option>

                            <option
                                value="آمادگی تیزهوشان"
                                @selected(request('specialty') === 'آمادگی تیزهوشان')
                            >
                            آمادگی تیزهوشان
                            </option>
                        </x-ui.select>
                    </div>


                    <div class="min-w-0 sm:min-w-44">
                        <x-ui.select
                            name="sort"
                            aria-label="مرتب‌سازی مدرسین"
                        >
                            <option
                                value="popular"
                                @selected(request('sort', 'popular') === 'popular')
                            >
                            محبوب‌ترین
                            </option>

                            <option
                                value="rating"
                                @selected(request('sort') === 'rating')
                            >
                            بالاترین امتیاز
                            </option>

                            <option
                                value="experience"
                                @selected(request('sort') === 'experience')
                            >
                            بیشترین تجربه
                            </option>
                        </x-ui.select>
                    </div>


                    <x-ui.button
                        type="submit"
                        variant="secondary"
                        class="shrink-0"
                    >
                        اعمال فیلتر
                    </x-ui.button>

                </div>

            </form>

        </x-layout.container>
    </section>


    {{-- =========================================================
        TEACHERS
    ========================================================== --}}
    <x-layout.section spacing="lg">
        <x-layout.container>

            <div class="flex items-center justify-between gap-4">

                <div>
                    <h2 class="text-lg font-extrabold text-[var(--color-text-primary)]">
                        مدرسین
                    </h2>

                    <p class="mt-1 text-sm text-[var(--color-text-muted)]">
                        {{ count($teachers) }} مدرس
                    </p>
                </div>

            </div>


            {{-- Grid --}}
            @if(count($teachers) > 0)

                <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

                    @foreach($teachers as $teacher)

                        <x-education.teacher-card
                            :name="$teacher['name']"
                            :avatar="$teacher['avatar']"
                            :avatar-alt="'تصویر ' . $teacher['name']"
                            :specialty="$teacher['specialty']"
                            :bio="$teacher['bio']"
                            :experience="$teacher['experience']"
                            :courses-count="$teacher['coursesCount']"
                            :students-count="$teacher['studentsCount']"
                            :rating="$teacher['rating']"
                            :verified="$teacher['verified']"
                            :href="route('teachers.show', $teacher['slug'])"
                        />

                    @endforeach

                </div>

            @else

                {{-- Empty State --}}
                <div class="mt-8">

                    <x-ui.empty-state
                        title="مدرسی پیدا نشد"
                        description="عبارت جستجو یا فیلترها را تغییر بده و دوباره امتحان کن."
                        action="پاک کردن فیلترها"
                        action-href="{{ route('teachers.index') }}"
                    />

                </div>

            @endif

        </x-layout.container>
    </x-layout.section>


    {{-- =========================================================
        CTA
    ========================================================== --}}
    <x-layout.section
        spacing="lg"
        class="bg-[var(--color-surface)]"
    >
        <x-layout.container>

            <div class="rounded-3xl border border-[var(--color-brand-100)] bg-[var(--color-brand-50)] px-6 py-10 sm:px-10">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                    <div class="max-w-2xl">

                        <span class="text-xs font-bold text-[var(--color-brand-700)]">
                            نمی‌دونی کدوم دوره مناسبته؟
                        </span>

                        <h2 class="mt-2 text-2xl font-black text-[var(--color-text-primary)]">
                            دوره مناسب مسیرت را پیدا کن
                        </h2>

                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                            دوره‌ها را بر اساس پایه، موضوع و سطح بررسی کن و گزینه مناسب مسیر یادگیریت را پیدا کن.
                        </p>

                    </div>

                    <div class="shrink-0">

                        <x-ui.button
                            :href="route('courses.index')"
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

                    </div>

                </div>

            </div>

        </x-layout.container>
    </x-layout.section>

@endsection

