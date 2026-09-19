@extends('layouts.app')

@section('title', 'شیخان | مسیر یادگیری تو')
@section('description', 'شیخان؛ محیط یکپارچه آموزش آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت.')

@section('content')
    @php
        $fa = fn ($value) => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp

    <section class="relative overflow-hidden border-b border-[var(--color-border)] bg-white">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-[var(--color-primary-100)] blur-3xl opacity-70"></div>
            <div class="absolute -left-32 bottom-0 h-80 w-80 rounded-full bg-[var(--color-accent-100)] blur-3xl opacity-50"></div>
        </div>

        <x-layout.container size="2xl">
            <div class="relative grid min-h-[620px] items-center gap-12 py-16 lg:grid-cols-2 lg:py-24">
                <div class="max-w-2xl">
                    <x-ui.badge variant="primary">آموزش هوشمند برای آینده بهتر</x-ui.badge>

                    <h1 class="mt-6 text-balance text-4xl font-black tracking-tight text-[var(--color-text)] sm:text-5xl lg:text-6xl">
                        یادگیری،
                        <span class="text-[var(--color-primary-600)]">مسیر رشد</span>
                        توست.
                    </h1>

                    <p class="mt-6 max-w-xl text-pretty text-base leading-8 text-[var(--color-text-muted)] sm:text-lg">
                        شیخان یک محیط آموزشی یکپارچه برای دانش‌آموزان، مدرس‌ها،
                        والدین و مدیران آموزشگاه است؛ جایی برای یادگیری، مدیریت و رشد واقعی.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button href="{{ route('courses.index') }}" variant="primary" size="lg">
                            مشاهده دوره‌ها
                        </x-ui.button>

                        <x-ui.button
                            href="{{ Route::has('register') ? route('register') : route('courses.index') }}"
                            variant="outline"
                            size="lg"
                        >
                            شروع یادگیری
                        </x-ui.button>
                    </div>

                    <div class="mt-10 grid max-w-xl grid-cols-3 gap-4 border-t border-[var(--color-border)] pt-8">
                        <div>
                            <div class="text-2xl font-black text-[var(--color-text)]">{{ $fa($stats['courses']) }}+</div>
                            <div class="mt-1 text-sm text-[var(--color-text-muted)]">دوره آموزشی</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-[var(--color-text)]">{{ $fa($stats['teachers']) }}+</div>
                            <div class="mt-1 text-sm text-[var(--color-text-muted)]">مدرس متخصص</div>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-[var(--color-text)]">{{ $fa($stats['students']) }}+</div>
                            <div class="mt-1 text-sm text-[var(--color-text-muted)]">دانش‌آموز</div>
                        </div>
                    </div>
                </div>

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
                                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10">ش</div>
                                </div>

                                <div class="mt-8 rounded-2xl bg-white/10 p-5 backdrop-blur">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-300">پیشرفت دوره</span>
                                        <span class="text-sm font-bold">۷۲٪</span>
                                    </div>
                                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                        <div class="h-full w-[72%] rounded-full bg-white"></div>
                                    </div>
                                    <div class="mt-4 text-lg font-bold">مسیر یادگیری وب</div>
                                    <div class="mt-1 text-sm text-slate-300">در کنار کلاس، تمرین و آزمون</div>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div class="rounded-2xl bg-white/10 p-4">
                                        <div class="text-xs text-slate-300">کلاس بعدی</div>
                                        <div class="mt-2 font-bold">بر اساس برنامه تو</div>
                                    </div>
                                    <div class="rounded-2xl bg-white/10 p-4">
                                        <div class="text-xs text-slate-300">آزمون بعدی</div>
                                        <div class="mt-2 font-bold">در پنل یادگیری</div>
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
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-success-500)]/10 text-[var(--color-success-600)]">✓</div>
                                <div>
                                    <div class="text-xs text-[var(--color-text-muted)]">وضعیت یادگیری</div>
                                    <div class="mt-1 font-bold">قدم‌به‌قدم همراهتیم</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <x-layout.page-header
                    title="دوره‌های منتخب"
                    description="دوره‌های منتشرشده را مستقیم از سیستم آموزشی شیخان ببین."
                />
                <x-ui.button href="{{ route('courses.index') }}" variant="ghost" size="sm">
                    مشاهده همه دوره‌ها ←
                </x-ui.button>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($courseCards as $course)
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
                        :href="$course['href']"
                    />
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-[var(--color-border)] bg-white p-10 text-center text-[var(--color-text-muted)]">
                        هنوز دوره منتشرشده‌ای برای نمایش وجود ندارد.
                    </div>
                @endforelse
            </div>
        </x-layout.container>
    </x-layout.section>

    <section class="border-y border-[var(--color-border)] bg-white">
        <x-layout.section spacing="lg">
            <x-layout.container size="2xl">
                <x-layout.page-header
                    title="کلاس‌های آنلاین"
                    description="کلاس‌های زنده و برنامه‌ریزی‌شده بر اساس داده واقعی سیستم."
                />

                <div class="mt-10 grid gap-5 lg:grid-cols-3">
                    @forelse ($liveClassCards as $class)
                        <x-education.live-class-card
                            :title="$class['title']"
                            :course="$class['course']"
                            :teacher="$class['teacher']"
                            :date="$class['date']"
                            :time="$class['time']"
                            :status="$class['status']"
                            :href="$class['href']"
                        />
                    @empty
                        <div class="lg:col-span-3 rounded-2xl border border-dashed border-[var(--color-border)] p-10 text-center text-[var(--color-text-muted)]">
                            در حال حاضر کلاس آنلاین فعالی برای نمایش وجود ندارد.
                        </div>
                    @endforelse
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>

    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                <x-layout.page-header
                    title="مدرس‌های شیخان"
                    description="مدرس‌های تاییدشده و فعال پلتفرم."
                />
                <x-ui.button href="{{ route('teachers.index') }}" variant="ghost" size="sm">
                    همه مدرس‌ها ←
                </x-ui.button>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($teacherCards as $teacher)
                    <x-education.teacher-card
                        :name="$teacher['name']"
                        :role="$teacher['role']"
                        :avatar="$teacher['avatar']"
                        :bio="$teacher['bio']"
                        :courses="$teacher['courses']"
                        href="{{ route('teachers.index') }}"
                    />
                @empty
                    <div class="sm:col-span-2 xl:col-span-4 rounded-2xl border border-dashed border-[var(--color-border)] p-10 text-center text-[var(--color-text-muted)]">
                        هنوز مدرس تاییدشده‌ای برای نمایش وجود ندارد.
                    </div>
                @endforelse
            </div>
        </x-layout.container>
    </x-layout.section>

    @if(count($latestPosts))
        <section class="border-y border-[var(--color-border)] bg-white">
            <x-layout.section spacing="lg">
                <x-layout.container size="2xl">
                    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
                        <x-layout.page-header
                            title="آخرین مقالات"
                            description="مطالب تازه منتشرشده از بخش محتوای شیخان."
                        />
                        <x-ui.button href="{{ route('blog.index') }}" variant="ghost" size="sm">
                            همه مقالات ←
                        </x-ui.button>
                    </div>

                    <div class="mt-10 grid gap-5 md:grid-cols-3">
                        @foreach ($latestPosts as $post)
                            <article class="fz-surface-interactive overflow-hidden">
                                @if($post->media->first()?->url())
                                    <img
                                        src="{{ $post->media->first()->url() }}"
                                        alt="{{ $post->title }}"
                                        class="aspect-[16/9] w-full object-cover"
                                        loading="lazy"
                                    >
                                @endif

                                <div class="p-5">
                                    @if($post->category)
                                        <span class="text-xs font-semibold text-[var(--color-primary-600)]">{{ $post->category->name }}</span>
                                    @endif
                                    <h3 class="mt-2 text-lg font-bold">
                                        <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[var(--color-primary-600)]">
                                            {{ $post->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-2 line-clamp-3 text-sm leading-7 text-[var(--color-text-muted)]">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </x-layout.container>
            </x-layout.section>
        </section>
    @endif

    <section class="overflow-hidden bg-[var(--color-slate-900)] text-white">
        <x-layout.section spacing="lg">
            <x-layout.container size="2xl">
                <div class="grid gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                    <div>
                        <x-ui.badge variant="accent">مسیر یادگیری</x-ui.badge>
                        <h2 class="mt-5 text-3xl font-black sm:text-4xl">
                            از شروع تا تسلط،
                            <br>
                            قدم‌به‌قدم کنارت هستیم.
                        </h2>
                        <p class="mt-5 max-w-xl leading-8 text-slate-300">
                            شیخان فقط مجموعه‌ای از ویدئوها نیست؛ مسیر یادگیری، کلاس، تمرین،
                            آزمون و پیشرفت در یک محیط یکپارچه قرار می‌گیرند.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            ['number' => '۰۱', 'title' => 'انتخاب مسیر', 'description' => 'موضوع و سطح مناسب خودت را پیدا کن.'],
                            ['number' => '۰۲', 'title' => 'یادگیری', 'description' => 'جلسات آموزشی را طبق برنامه جلو ببر.'],
                            ['number' => '۰۳', 'title' => 'تمرین و آزمون', 'description' => 'آموخته‌هایت را با تمرین و آزمون محک بزن.'],
                            ['number' => '۰۴', 'title' => 'رشد و پیشرفت', 'description' => 'پیشرفتت را ببین و مسیر بعدی را انتخاب کن.'],
                        ] as $step)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur">
                                <div class="text-sm font-bold text-slate-400">{{ $step['number'] }}</div>
                                <h3 class="mt-4 text-xl font-bold text-white">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-400">{{ $step['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>

    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <div class="mx-auto max-w-2xl text-center">
                <x-ui.badge variant="neutral">یک محیط آموزشی کامل</x-ui.badge>
                <h2 class="mt-5 text-3xl font-black sm:text-4xl">همه‌چیز برای یک تجربه آموزشی بهتر</h2>
                <p class="mt-4 leading-8 text-[var(--color-text-muted)]">
                    از کلاس آنلاین و ویدئو گرفته تا آزمون، تکلیف و گزارش پیشرفت.
                </p>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['icon' => '▶', 'title' => 'آموزش آنلاین', 'description' => 'دسترسی به جلسات آموزشی، ویدئوها و محتوای دوره.'],
                    ['icon' => '✓', 'title' => 'آزمون و تمرین', 'description' => 'تمرین‌ها و آزمون‌های منظم برای سنجش یادگیری.'],
                    ['icon' => '↗', 'title' => 'پیگیری پیشرفت', 'description' => 'مشاهده روند یادگیری و عملکرد در طول مسیر.'],
                    ['icon' => '◉', 'title' => 'ارتباط با مدرس', 'description' => 'ارتباط با مدرس و دریافت بازخورد آموزشی.'],
                ] as $feature)
                    <div class="fz-surface-interactive p-6">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[var(--color-primary-50)] font-bold text-[var(--color-primary-600)]">
                            {{ $feature['icon'] }}
                        </div>
                        <h3 class="mt-5 text-lg font-bold">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-7 text-[var(--color-text-muted)]">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-layout.container>
    </x-layout.section>

    <section class="px-4 pb-16 sm:px-6 lg:px-8 lg:pb-24">
        <div class="container-site overflow-hidden rounded-[2rem] bg-[var(--color-primary-600)]">
            <div class="relative px-6 py-14 text-center text-white sm:px-12 sm:py-20">
                <div class="absolute -right-20 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative mx-auto max-w-2xl">
                    <h2 class="text-3xl font-black sm:text-4xl">آماده‌ای مسیر یادگیریت رو شروع کنی؟</h2>
                    <p class="mt-5 leading-8 text-blue-100">
                        وارد شیخان شو و مسیر آموزشی مناسب خودت را پیدا کن.
                    </p>

                    <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <x-ui.button href="{{ route('courses.index') }}" variant="secondary" size="lg">
                            مشاهده دوره‌ها
                        </x-ui.button>
                        <x-ui.button
                            href="{{ Route::has('register') ? route('register') : route('courses.index') }}"
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
