@extends('layouts.app')

@section('title', 'فرزین | مسیر یادگیری تو')

@section('content')

    {{-- ============================================================
        HERO
    ============================================================ --}}
    <section class="relative overflow-hidden border-b border-[var(--color-border)] bg-white">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-[var(--color-primary-100)] blur-3xl opacity-70"></div>
            <div class="absolute -left-32 bottom-0 h-80 w-80 rounded-full bg-[var(--color-accent-100)] blur-3xl opacity-50"></div>
        </div>

        <x-layout.container size="2xl">
            <div class="relative grid min-h-[620px] items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">

                {{-- Hero content --}}
                <div class="max-w-2xl">
                    <x-ui.badge variant="primary">
                        آموزش هوشمند برای آینده بهتر
                    </x-ui.badge>

                    <h1 class="mt-6 text-balance text-4xl font-black tracking-tight text-[var(--color-text)] sm:text-5xl lg:text-6xl">
                        یادگیری،
                        <span class="text-[var(--color-primary-600)]">مسیر رشد</span>
                        توست.
                    </h1>

                    <p class="mt-6 max-w-xl text-pretty text-base leading-8 text-[var(--color-text-muted)] sm:text-lg">
                        فرزین یک محیط آموزشی یکپارچه برای دانش‌آموزان، مدرس‌ها،
                        والدین و مدیران آموزشگاه است؛ جایی برای یادگیری،
                        مدیریت و رشد واقعی.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button
                            href="/courses"
                            variant="primary"
                            size="lg"
                        >
                            مشاهده دوره‌ها
                        </x-ui.button>

                        <x-ui.button
                            href="/register"
                            variant="outline"
                            size="lg"
                        >
                            شروع یادگیری
                        </x-ui.button>
                    </div>

                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-[var(--color-border)] pt-8">
                        <div>
                            <div class="text-2xl font-black text-[var(--color-text)]">۱۲۰+</div>
                            <div class="mt-1 text-sm text-[var(--color-text-muted)]">دوره آموزشی</div>
                        </div>

                        <div>
                            <div class="text-2xl font-black text-[var(--color-text)]">۴۰+</div>
                            <div class="mt-1 text-sm text-[var(--color-text-muted)]">مدرس متخصص</div>
                        </div>

                        <div>
                            <div class="text-2xl font-black text-[var(--color-text)]">۲۴/۷</div>
                            <div class="mt-1 text-sm text-[var(--color-text-muted)]">دسترسی به آموزش</div>
                        </div>
                    </div>
                </div>

                {{-- Hero visual --}}
                <div class="relative hidden lg:block">
                    <div class="relative mx-auto max-w-lg">

                        <div class="absolute -right-6 top-12 h-72 w-72 rounded-full bg-[var(--color-primary-100)] blur-3xl"></div>

                        <div class="relative overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-white p-4 shadow-[var(--shadow-xl)]">

                            <div class="rounded-[1.5rem] bg-[var(--color-slate-900)] p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-slate-300">داشبورد یادگیری</div>
                                        <div class="mt-1 text-xl font-bold">سلام، خوش اومدی 👋</div>
                                    </div>

                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10">
                                        ف
                                    </div>
                                </div>

                                <div class="mt-8 rounded-2xl bg-white/10 p-5 backdrop-blur">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-300">
                                            پیشرفت دوره
                                        </span>

                                        <span class="text-sm font-bold">
                                            ۷۲٪
                                        </span>
                                    </div>

                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full w-[72%] rounded-full bg-white"></div>
                                    </div>

                                    <div class="mt-4 text-lg font-bold">
                                        برنامه‌نویسی وب
                                    </div>

                                    <div class="mt-1 text-sm text-slate-300">
                                        ۱۸ جلسه از ۲۵ جلسه
                                    </div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div class="rounded-2xl bg-white/10 p-4">
                                        <div class="text-xs text-slate-300">کلاس بعدی</div>
                                        <div class="mt-2 font-bold">امروز، ۱۸:۰۰</div>
                                    </div>

                                    <div class="rounded-2xl bg-white/10 p-4">
                                        <div class="text-xs text-slate-300">آزمون بعدی</div>
                                        <div class="mt-2 font-bold">شنبه</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div class="h-20 rounded-xl bg-[var(--color-slate-100)]"></div>
                                <div class="h-20 rounded-xl bg-[var(--color-primary-50)]"></div>
                                <div class="h-20 rounded-xl bg-[var(--color-accent-50)]"></div>
                            </div>
                        </div>

                        <div class="absolute -bottom-6 -right-8 hidden rounded-2xl border border-[var(--color-border)] bg-white p-4 shadow-[var(--shadow-lg)] sm:block">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-success-500)]/10 text-[var(--color-success-600)]">
                                    ✓
                                </div>

                                <div>
                                    <div class="text-xs text-[var(--color-text-muted)]">
                                        وضعیت یادگیری
                                    </div>

                                    <div class="mt-1 font-bold">
                                        عالی پیش می‌ری!
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </x-layout.container>
    </section>


    {{-- ============================================================
        FEATURED COURSES
    ============================================================ --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">

            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <x-layout.page-header
                    title="دوره‌های منتخب"
                    description="چند مسیر آموزشی که می‌توانند شروع خوبی برای یادگیری باشند."
                />

                <x-ui.button
                    href="/courses"
                    variant="ghost"
                    size="sm"
                >
                    مشاهده همه دوره‌ها ←
                </x-ui.button>
            </div>

            @php
                $courses = [
                    [
                        'title' => 'برنامه‌نویسی وب از صفر',
                        'description' => 'مسیر کامل یادگیری توسعه وب برای شروع حرفه‌ای.',
                        'category' => 'برنامه‌نویسی',
                        'teacher' => 'علی رضایی',
                        'lessons' => 25,
                        'duration' => '۱۲ ساعت',
                        'price' => '۲,۴۰۰,۰۰۰',
                        'level' => 'مقدماتی',
                        'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'title' => 'ریاضی کنکور حرفه‌ای',
                        'description' => 'حل مسئله، تکنیک‌های تستی و آمادگی کنکور.',
                        'category' => 'ریاضی',
                        'teacher' => 'سارا احمدی',
                        'lessons' => 32,
                        'duration' => '۱۸ ساعت',
                        'price' => '۱,۹۰۰,۰۰۰',
                        'level' => 'پیشرفته',
                        'image' => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?auto=format&fit=crop&w=900&q=80',
                    ],
                    [
                        'title' => 'زبان انگلیسی کاربردی',
                        'description' => 'تقویت مکالمه و مهارت‌های کاربردی زبان انگلیسی.',
                        'category' => 'زبان',
                        'teacher' => 'محمد کریمی',
                        'lessons' => 20,
                        'duration' => '۱۰ ساعت',
                        'price' => '۱,۵۰۰,۰۰۰',
                        'level' => 'متوسط',
                        'image' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=900&q=80',
                    ],
                ];
            @endphp

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($courses as $course)
                    <x-education.course-card
                        :title="$course['title']"
                        :description="$course['description']"
                        :category="$course['category']"
                        :teacher="$course['teacher']"
                        :lessons="$course['lessons']"
                        :duration="$course['duration']"
                        :price="$course['price']"
                        :level="$course['level']"
                        :image="$course['image']"
                        href="/courses"
                    />
                @endforeach
            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- ============================================================
        LIVE CLASSES
    ============================================================ --}}
    <section class="border-y border-[var(--color-border)] bg-white">
        <x-layout.section spacing="lg">
            <x-layout.container size="2xl">

                <x-layout.page-header
                    title="کلاس‌های آنلاین"
                    description="کلاس‌های زنده را از دست نده و با مدرس و همکلاسی‌ها در ارتباط باش."
                />

                @php
                    $liveClasses = [
                        [
                            'title' => 'حل تست ریاضی',
                            'course' => 'ریاضی کنکور حرفه‌ای',
                            'teacher' => 'سارا احمدی',
                            'date' => 'امروز',
                            'time' => '۱۸:۰۰',
                            'students' => 24,
                            'status' => 'live',
                        ],
                        [
                            'title' => 'پروژه عملی Laravel',
                            'course' => 'برنامه‌نویسی وب از صفر',
                            'teacher' => 'علی رضایی',
                            'date' => 'فردا',
                            'time' => '۱۹:۳۰',
                            'students' => 18,
                            'status' => 'upcoming',
                        ],
                        [
                            'title' => 'Speaking Club',
                            'course' => 'زبان انگلیسی کاربردی',
                            'teacher' => 'محمد کریمی',
                            'date' => 'شنبه',
                            'time' => '۱۷:۰۰',
                            'students' => 42,
                            'status' => 'upcoming',
                        ],
                    ];
                @endphp

                <div class="mt-10 grid gap-5 lg:grid-cols-3">
                    @foreach ($liveClasses as $class)
                        <x-education.live-class-card
                            :title="$class['title']"
                            :course="$class['course']"
                            :teacher="$class['teacher']"
                            :date="$class['date']"
                            :time="$class['time']"
                            :students="$class['students']"
                            :status="$class['status']"
                        />
                    @endforeach
                </div>

            </x-layout.container>
        </x-layout.section>
    </section>


    {{-- ============================================================
        TEACHERS
    ============================================================ --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">

            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <x-layout.page-header
                    title="مدرس‌های فرزین"
                    description="با مدرس‌هایی یاد بگیر که تجربه و تخصص را با آموزش ترکیب کرده‌اند."
                />

                <x-ui.button
                    href="/teachers"
                    variant="ghost"
                    size="sm"
                >
                    همه مدرس‌ها ←
                </x-ui.button>
            </div>

            @php
                $teachers = [
                    [
                        'name' => 'علی رضایی',
                        'role' => 'مدرس برنامه‌نویسی',
                        'bio' => 'توسعه‌دهنده و مدرس با تمرکز بر توسعه وب.',
                        'courses' => 8,
                    ],
                    [
                        'name' => 'سارا احمدی',
                        'role' => 'مدرس ریاضی',
                        'bio' => 'مدرس ریاضی و متخصص آموزش کنکور.',
                        'courses' => 6,
                    ],
                    [
                        'name' => 'محمد کریمی',
                        'role' => 'مدرس زبان انگلیسی',
                        'bio' => 'مدرس زبان با تمرکز بر مکالمه و مهارت‌های کاربردی.',
                        'courses' => 5,
                    ],
                    [
                        'name' => 'نگار محمدی',
                        'role' => 'مدرس علوم',
                        'bio' => 'مدرس علوم تجربی و طراحی مسیرهای یادگیری.',
                        'courses' => 7,
                    ],
                ];
            @endphp

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($teachers as $teacher)
                    <x-education.teacher-card
                        :name="$teacher['name']"
                        :role="$teacher['role']"
                        :bio="$teacher['bio']"
                        :courses="$teacher['courses']"
                        href="/teachers"
                    />
                @endforeach
            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- ============================================================
        LEARNING PATH
    ============================================================ --}}
    <section class="overflow-hidden bg-[var(--color-slate-900)] text-white">
        <x-layout.section spacing="lg">
            <x-layout.container size="2xl">

                <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">

                    <div>
                        <x-ui.badge variant="accent">
                            مسیر یادگیری
                        </x-ui.badge>

                        <h2 class="mt-5 text-3xl font-black sm:text-4xl">
                            از شروع تا تسلط،
                            <br>
                            قدم‌به‌قدم کنارت هستیم.
                        </h2>

                        <p class="mt-5 max-w-xl leading-8 text-slate-300">
                            فرزین فقط مجموعه‌ای از ویدئوها نیست.
                            مسیر یادگیری، کلاس، تمرین، آزمون و پیشرفت
                            همه در یک محیط یکپارچه قرار می‌گیرند.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">

                        @php
                            $steps = [
                                [
                                    'number' => '۰۱',
                                    'title' => 'انتخاب مسیر',
                                    'description' => 'موضوع و سطح مناسب خودت را پیدا کن.',
                                ],
                                [
                                    'number' => '۰۲',
                                    'title' => 'یادگیری',
                                    'description' => 'جلسات آموزشی را طبق برنامه جلو ببر.',
                                ],
                                [
                                    'number' => '۰۳',
                                    'title' => 'تمرین و آزمون',
                                    'description' => 'آموخته‌هایت را با تمرین و آزمون محک بزن.',
                                ],
                                [
                                    'number' => '۰۴',
                                    'title' => 'رشد و پیشرفت',
                                    'description' => 'پیشرفتت را ببین و مسیر بعدی را انتخاب کن.',
                                ],
                            ];
                        @endphp

                        @foreach ($steps as $step)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                                <div class="text-sm font-bold text-slate-400">
                                    {{ $step['number'] }}
                                </div>

                                <h3 class="mt-4 text-xl font-bold text-white">
                                    {{ $step['title'] }}
                                </h3>

                                <p class="mt-2 text-sm leading-7 text-slate-400">
                                    {{ $step['description'] }}
                                </p>
                            </div>
                        @endforeach

                    </div>

                </div>

            </x-layout.container>
        </x-layout.section>
    </section>


    {{-- ============================================================
        PLATFORM FEATURES
    ============================================================ --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">

            <div class="mx-auto max-w-2xl text-center">
                <x-ui.badge variant="neutral">
                    یک محیط آموزشی کامل
                </x-ui.badge>

                <h2 class="mt-5 text-3xl font-black sm:text-4xl">
                    همه‌چیز برای یک تجربه آموزشی بهتر
                </h2>

                <p class="mt-4 leading-8 text-[var(--color-text-muted)]">
                    از کلاس آنلاین و ویدئو گرفته تا آزمون، تکلیف و گزارش پیشرفت.
                </p>
            </div>

            @php
                $features = [
                    [
                        'icon' => '▶',
                        'title' => 'آموزش آنلاین',
                        'description' => 'دسترسی به جلسات آموزشی، ویدئوها و محتوای دوره.',
                    ],
                    [
                        'icon' => '✓',
                        'title' => 'آزمون و تمرین',
                        'description' => 'تمرین‌ها و آزمون‌های منظم برای سنجش یادگیری.',
                    ],
                    [
                        'icon' => '↗',
                        'title' => 'پیگیری پیشرفت',
                        'description' => 'مشاهده روند یادگیری و عملکرد در طول مسیر.',
                    ],
                    [
                        'icon' => '◉',
                        'title' => 'ارتباط با مدرس',
                        'description' => 'ارتباط مستقیم و دریافت بازخورد آموزشی.',
                    ],
                ];
            @endphp

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($features as $feature)
                    <div class="fz-surface-interactive p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--color-primary-50)] font-bold text-[var(--color-primary-600)]">
                            {{ $feature['icon'] }}
                        </div>

                        <h3 class="mt-5 text-lg font-bold">
                            {{ $feature['title'] }}
                        </h3>

                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-muted)]">
                            {{ $feature['description'] }}
                        </p>
                    </div>
                @endforeach
            </div>

        </x-layout.container>
    </x-layout.section>


    {{-- ============================================================
        FINAL CTA
    ============================================================ --}}
    <section class="px-4 pb-16 sm:px-6 lg:px-8 lg:pb-24">
        <div class="container-farzin overflow-hidden rounded-[2rem] bg-[var(--color-primary-600)]">
            <div class="relative px-6 py-14 text-center text-white sm:px-12 sm:py-20">

                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative mx-auto max-w-2xl">
                    <h2 class="text-3xl font-black sm:text-4xl">
                        آماده‌ای مسیر یادگیریت رو شروع کنی؟
                    </h2>

                    <p class="mt-5 leading-8 text-blue-100">
                        وارد فرزین شو و مسیر آموزشی مناسب خودت را پیدا کن.
                    </p>

                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <x-ui.button
                            href="/courses"
                            variant="secondary"
                            size="lg"
                        >
                            مشاهده دوره‌ها
                        </x-ui.button>

                        <x-ui.button
                            href="/register"
                            variant="ghost"
                            size="lg"
                        >
                            ساخت حساب کاربری
                        </x-ui.button>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
