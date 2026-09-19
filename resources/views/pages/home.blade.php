@extends('layouts.app')

@section('title', 'شیخان | مسیر یادگیری برای آینده')
@section('description', 'شیخان؛ پلتفرم یکپارچه آموزش آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت برای دانش‌آموز، مدرس، والد و آموزشگاه.')

@section('content')
    @php
        $fa = fn ($value) => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        $heroCourse = $courseCards[0] ?? null;

        $paths = [
            [
                'title' => 'یادگیری آنلاین',
                'description' => 'دوره و درس را مرحله‌به‌مرحله جلو ببر و مسیرت را خودت بساز.',
                'href' => route('courses.index'),
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="4" width="18" height="15" rx="3"/><path d="m10 9 5 2.5-5 2.5V9Z"/><path d="M8 22h8"/></svg>',
            ],
            [
                'title' => 'کلاس و تعامل',
                'description' => 'کلاس‌های آموزشی و جلسه‌های آنلاین را در یک محیط منظم دنبال کن.',
                'href' => route('courses.index'),
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v13H6.5A2.5 2.5 0 0 0 4 19.5v-13Z"/><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M8 8h8M8 11h5"/></svg>',
            ],
            [
                'title' => 'تمرین و آزمون',
                'description' => 'یادگیری را با تکلیف، آزمون و بازخورد به نتیجه قابل اندازه‌گیری تبدیل کن.',
                'href' => route('courses.index'),
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M7 3h10a2 2 0 0 1 2 2v16H5V5a2 2 0 0 1 2-2Z"/><path d="m8.5 12 2 2 5-5"/><path d="M8 7h8"/></svg>',
            ],
            [
                'title' => 'پیشرفت آموزشی',
                'description' => 'دانش‌آموز، والد و مدرس هرکدام تصویر روشن‌تری از مسیر یادگیری دارند.',
                'href' => route('teachers.index'),
                'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V5M4 19h16"/><path d="m7 15 3-3 3 2 5-6"/></svg>',
            ],
        ];
    @endphp

    {{-- Hero --}}
    <section class="home-noise relative overflow-hidden">
        <x-layout.container size="wide">
            <div class="home-hero-grid grid items-center gap-12 py-14 sm:py-20 lg:grid-cols-[1fr_0.92fr] lg:gap-16 lg:py-24">
                <div class="max-w-3xl home-reveal" data-delay="1">
                    <span class="inline-flex items-center gap-2 rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3.5 py-2 text-xs font-bold text-[var(--color-primary-700)]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
                        یک مسیر، برای تمام نقش‌های یادگیری
                    </span>

                    <h1 class="mt-7 max-w-3xl text-4xl font-black leading-[1.15] tracking-tight text-[var(--color-text)] sm:text-5xl lg:text-[4.25rem]">
                        یادگیری‌ای که فقط
                        <span class="text-[var(--color-primary-600)]">شروع نمی‌شود؛</span>
                        جلو می‌رود.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg">
                        شیخان، فضای یکپارچه‌ای برای دوره، کلاس، تمرین، آزمون و پیگیری پیشرفت است؛
                        تا دانش‌آموز، مدرس، والد و آموزشگاه هرکدام تجربه‌ای متناسب با نقش خود داشته باشند.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button href="{{ route('courses.index') }}" variant="primary" size="xl">
                            کشف دوره‌ها
                            <span aria-hidden="true">←</span>
                        </x-ui.button>

                        <x-ui.button href="{{ route('teachers.index') }}" variant="outline" size="xl">
                            آشنایی با مدرس‌ها
                        </x-ui.button>
                    </div>

                    <div class="mt-10 grid max-w-2xl grid-cols-3 gap-3">
                        <div class="home-stat rounded-2xl border border-[var(--color-border)] bg-white/80 p-4 sm:p-5">
                            <div class="text-2xl font-black text-[var(--color-text)] sm:text-3xl">{{ $fa($stats['courses']) }}+</div>
                            <div class="mt-1 text-xs font-semibold text-[var(--color-text-muted)] sm:text-sm">دوره منتشرشده</div>
                        </div>
                        <div class="home-stat rounded-2xl border border-[var(--color-border)] bg-white/80 p-4 sm:p-5">
                            <div class="text-2xl font-black text-[var(--color-text)] sm:text-3xl">{{ $fa($stats['teachers']) }}+</div>
                            <div class="mt-1 text-xs font-semibold text-[var(--color-text-muted)] sm:text-sm">مدرس تاییدشده</div>
                        </div>
                        <div class="home-stat rounded-2xl border border-[var(--color-border)] bg-white/80 p-4 sm:p-5">
                            <div class="text-2xl font-black text-[var(--color-text)] sm:text-3xl">{{ $fa($stats['students']) }}+</div>
                            <div class="mt-1 text-xs font-semibold text-[var(--color-text-muted)] sm:text-sm">دانش‌آموز</div>
                        </div>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl home-reveal" data-delay="2">
                    <div class="pointer-events-none absolute -inset-8 rounded-[3rem] bg-[radial-gradient(circle_at_center,rgba(104,121,245,.18),transparent_68%)] blur-2xl"></div>

                    <div class="home-hero-card relative p-4 text-white sm:p-5 lg:p-6">
                        <span class="home-hero-ring right-[-6rem] top-[-5rem]"></span>

                        <div class="relative">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-[11px] font-medium text-white/45">شیخان / مسیر یادگیری</div>
                                    <div class="mt-1 text-lg font-bold sm:text-xl">امروز چه چیزی جلو می‌رود؟</div>
                                </div>
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-sm font-black shadow-inner">
                                    ش
                                </div>
                            </div>

                            @if($heroCourse)
                                <div class="mt-7 overflow-hidden rounded-[1.5rem] border border-white/10 bg-white/[0.07]">
                                    <div class="relative aspect-[16/8] overflow-hidden bg-white/5">
                                        @if($heroCourse['image'])
                                            <img src="{{ $heroCourse['image'] }}" alt="{{ $heroCourse['title'] }}" class="h-full w-full object-cover opacity-90" loading="eager">
                                            <div class="absolute inset-0 bg-gradient-to-t from-[#0d1223] via-transparent to-transparent"></div>
                                        @else
                                            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(104,121,245,.42),transparent_35%),linear-gradient(135deg,#111a35,#1f2a5d)]"></div>
                                        @endif
                                        <div class="absolute inset-x-4 bottom-4 flex items-end justify-between gap-4">
                                            <div>
                                                <div class="text-[11px] text-white/55">{{ $heroCourse['category'] ?? 'دوره آموزشی' }}</div>
                                                <div class="mt-1 max-w-sm text-lg font-black sm:text-xl">{{ $heroCourse['title'] }}</div>
                                            </div>
                                            @if($heroCourse['level'])
                                                <span class="rounded-full border border-white/15 bg-black/20 px-3 py-1.5 text-[11px] font-bold text-white/80 backdrop-blur">
                                                    {{ $heroCourse['level'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-5">
                                        <div class="grid grid-cols-3 gap-3">
                                            <div class="rounded-2xl bg-white/5 p-3">
                                                <div class="text-[10px] text-white/40">درس‌ها</div>
                                                <div class="mt-1 text-sm font-bold">{{ $heroCourse['lessons'] }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-white/5 p-3">
                                                <div class="text-[10px] text-white/40">زمان</div>
                                                <div class="mt-1 text-sm font-bold">{{ $heroCourse['duration'] }}</div>
                                            </div>
                                            <div class="rounded-2xl bg-white/5 p-3">
                                                <div class="text-[10px] text-white/40">قیمت</div>
                                                <div class="mt-1 text-sm font-bold">{{ $heroCourse['price'] }}</div>
                                            </div>
                                        </div>

                                        <x-ui.button href="{{ $heroCourse['href'] }}" variant="secondary" size="lg" block class="mt-4">
                                            ورود به دوره
                                            <span aria-hidden="true">←</span>
                                        </x-ui.button>
                                    </div>
                                </div>
                            @else
                                <div class="mt-7 rounded-[1.5rem] border border-white/10 bg-white/[0.07] p-7">
                                    <div class="text-sm font-bold">اولین مسیرت را بساز.</div>
                                    <p class="mt-2 text-sm leading-7 text-white/55">
                                        از بین دوره‌های شیخان موضوعی را انتخاب کن و یادگیری را شروع کن.
                                    </p>
                                    <x-ui.button href="{{ route('courses.index') }}" variant="secondary" size="lg" class="mt-5">
                                        مشاهده دوره‌ها
                                    </x-ui.button>
                                </div>
                            @endif

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-3 text-center">
                                    <div class="text-[10px] text-white/40">یادگیری</div>
                                    <div class="mt-1 text-xs font-bold">دوره</div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-3 text-center">
                                    <div class="text-[10px] text-white/40">تعامل</div>
                                    <div class="mt-1 text-xs font-bold">کلاس</div>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.05] p-3 text-center">
                                    <div class="text-[10px] text-white/40">ارزیابی</div>
                                    <div class="mt-1 text-xs font-bold">آزمون</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    {{-- Learning paths --}}
    <section class="home-trust-strip">
        <x-layout.container size="wide">
            <div class="grid gap-0 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($paths as $path)
                    <a href="{{ $path['href'] }}" class="group flex min-h-28 items-center gap-4 border-b border-[rgba(83,98,223,.08)] px-1 py-5 sm:border-l sm:px-5 lg:min-h-24 lg:border-b-0 lg:first:border-r-0">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-[var(--color-primary-600)] shadow-sm transition group-hover:-translate-y-0.5 group-hover:shadow-md">
                            {!! $path['icon'] !!}
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-[var(--color-text)]">{{ $path['title'] }}</span>
                            <span class="mt-1 block text-xs leading-6 text-[var(--color-text-muted)]">{{ $path['description'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </x-layout.container>
    </section>

    {{-- How it works --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <x-layout.section-heading
                eyebrow="چطور کار می‌کند؟"
                title="از انتخاب تا پیشرفت، همه‌چیز در یک مسیر"
                description="شیخان فقط محل دیدن محتوا نیست؛ هر مرحله از یادگیری به مرحله بعدی متصل می‌شود."
                center
            />

            <div class="relative mt-14 grid gap-10 md:grid-cols-3">
                <div class="home-step-line"></div>

                @foreach([
                    ['number' => '۰۱', 'title' => 'مسیرت را پیدا کن', 'description' => 'دوره، کلاس یا موضوع آموزشی مناسب خودت را از بین محتوای منتشرشده انتخاب کن.'],
                    ['number' => '۰۲', 'title' => 'یاد بگیر و تمرین کن', 'description' => 'درس‌ها را جلو ببر، در کلاس‌ها حاضر شو و با تمرین و آزمون دانسته‌ها را محک بزن.'],
                    ['number' => '۰۳', 'title' => 'پیشرفتت را ببین', 'description' => 'نتیجه فعالیت‌ها و قدم‌های بعدی را در فضای متناسب با نقش خودت دنبال کن.'],
                ] as $step)
                    <div class="relative text-center home-reveal" data-delay="{{ $loop->iteration }}">
                        <div class="home-step-number mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[var(--color-primary-200)] bg-white text-sm font-black text-[var(--color-primary-700)]">
                            {{ $step['number'] }}
                        </div>
                        <h3 class="mt-6 text-xl font-black text-[var(--color-text)]">{{ $step['title'] }}</h3>
                        <p class="mx-auto mt-3 max-w-sm text-sm leading-7 text-[var(--color-text-secondary)]">{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </x-layout.container>
    </x-layout.section>

    {{-- Courses --}}
    <section class="home-muted-section">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <x-layout.section-heading
                    eyebrow="دوره‌ها"
                    title="موضوع مورد علاقه‌ات را پیدا کن"
                    description="دوره‌های منتشرشده با اطلاعات واقعی از سیستم آموزشی شیخان."
                    href="{{ route('courses.index') }}"
                    link-label="مشاهده همه دوره‌ها"
                />

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse($courseCards as $course)
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
                        <div class="md:col-span-2 xl:col-span-3">
                            <x-ui.empty-state title="هنوز دوره‌ای منتشر نشده" description="به‌محض انتشار دوره، این بخش به‌صورت خودکار به‌روزرسانی می‌شود." />
                        </div>
                    @endforelse
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>

    {{-- Live classes --}}
    @if(count($liveClassCards))
        <x-layout.section spacing="lg" class="home-section-divider">
            <x-layout.container size="wide">
                <x-layout.section-heading
                    eyebrow="جلسه‌های زنده"
                    title="کلاس آنلاین بعدی را از دست نده"
                    description="جلسه‌های زمان‌بندی‌شده بر اساس داده‌های واقعی کلاس‌های شیخان."
                />

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($liveClassCards as $class)
                        <x-education.live-class-card
                            :title="$class['title']"
                            :course="$class['course']"
                            :teacher="$class['teacher']"
                            :date="$class['date']"
                            :time="$class['time']"
                            :status="$class['status']"
                            :href="$class['href']"
                        />
                    @endforeach
                </div>
            </x-layout.container>
        </x-layout.section>
    @endif

    {{-- Teachers --}}
    <x-layout.section spacing="lg" class="home-section-divider">
        <x-layout.container size="wide">
            <div class="grid gap-10 lg:grid-cols-[.72fr_1.28fr] lg:items-end">
                <x-layout.section-heading
                    eyebrow="مدرس‌ها"
                    title="پشت هر مسیر، یک آدم واقعی است."
                    description="مدرس‌های تاییدشده و فعال شیخان را بشناس و مسیرهای آموزشی‌شان را ببین."
                />
                <div class="flex lg:justify-end">
                    <x-ui.button href="{{ route('teachers.index') }}" variant="outline" size="lg">
                        همه مدرس‌ها
                        <span aria-hidden="true">←</span>
                    </x-ui.button>
                </div>
            </div>

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @forelse($teacherCards as $teacher)
                    <x-education.teacher-card
                        :name="$teacher['name']"
                        :role="$teacher['role']"
                        :avatar="$teacher['avatar']"
                        :bio="$teacher['bio']"
                        :courses="$teacher['courses']"
                        :href="route('teachers.index')"
                    />
                @empty
                    <div class="sm:col-span-2 xl:col-span-4">
                        <x-ui.empty-state title="هنوز مدرس تاییدشده‌ای نیست" description="مدرس‌های تاییدشده اینجا نمایش داده می‌شوند." />
                    </div>
                @endforelse
            </div>
        </x-layout.container>
    </x-layout.section>

    {{-- Roles --}}
    <section class="home-dark-section text-white">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <div class="relative grid gap-12 lg:grid-cols-[.8fr_1.2fr] lg:items-center">
                    <div class="home-reveal" data-delay="1">
                        <span class="inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-bold text-white/65">
                            برای هر نقش، یک تجربه
                        </span>

                        <h2 class="mt-5 max-w-xl text-3xl font-black leading-tight text-white sm:text-4xl">
                            یک هسته آموزشی،
                            چهار تجربه متفاوت.
                        </h2>

                        <p class="mt-5 max-w-xl text-sm leading-8 text-white/55 sm:text-base">
                            ساختار شیخان اجازه می‌دهد آموزشگاه، مدرس، دانش‌آموز و والد بدون قاطی‌شدن مسئولیت‌ها،
                            از محیط مخصوص خودشان استفاده کنند.
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-education.feature-card
                            title="مدیریت آموزشگاه"
                            description="مدیریت دوره‌ها، کلاس‌ها، مدرس‌ها و ساختار آموزشی در یک محیط مستقل."
                            icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 10 12 4l8 6"/><path d="M6 9v10h12V9"/><path d="M9 19v-6h6v6"/></svg>'
                            class="border-white/10 bg-white/[.06] text-white shadow-none hover:border-white/15 hover:bg-white/[.08]"
                        />
                        <x-education.feature-card
                            title="پنل مدرس"
                            description="ساخت درس، مدیریت کلاس و پیگیری تکلیف، آزمون و حضور دانش‌آموزان."
                            icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="7" r="3"/><path d="M5 20c.8-4 3-6 7-6s6.2 2 7 6"/><path d="M16.5 4.5 19 7l-2.5 2.5"/></svg>'
                            class="border-white/10 bg-white/[.06] text-white shadow-none hover:border-white/15 hover:bg-white/[.08]"
                        />
                        <x-education.feature-card
                            title="پنل دانش‌آموز"
                            description="مسیر یادگیری، کلاس‌ها، تمرین‌ها و آزمون‌ها در یک فضای شخصی."
                            icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 8 4v6c0 4.4-3.1 6.9-8 8-4.9-1.1-8-3.6-8-8V7l8-4Z"/><path d="m9 12 2 2 4-4"/></svg>'
                            class="border-white/10 bg-white/[.06] text-white shadow-none hover:border-white/15 hover:bg-white/[.08]"
                        />
                        <x-education.feature-card
                            title="پنل والد"
                            description="دیدن وضعیت فرزند، کلاس‌ها، عملکرد و نشانه‌های مهم پیشرفت تحصیلی."
                            icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="3"/><path d="M3.5 20c.6-3.4 2.4-5 5.5-5s4.9 1.6 5.5 5"/><path d="M16 11a3 3 0 1 0-2.2-5"/><path d="M16.5 15c2.2.2 3.6 1.8 4 5"/></svg>'
                            class="border-white/10 bg-white/[.06] text-white shadow-none hover:border-white/15 hover:bg-white/[.08]"
                        />
                    </div>
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>

    {{-- Blog --}}
    @if($latestPosts->isNotEmpty())
        <x-layout.section spacing="lg" class="home-section-divider">
            <x-layout.container size="wide">
                <x-layout.section-heading
                    eyebrow="مرکز محتوا"
                    title="فکر کن، یاد بگیر، بهتر شو."
                    description="مقاله‌ها و محتوای منتشرشده از سیستم محتوایی شیخان."
                    href="{{ route('blog.index') }}"
                    link-label="مشاهده همه مقالات"
                />

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($latestPosts as $post)
                        <x-education.post-card
                            :title="$post->title"
                            :excerpt="$post->excerpt"
                            :category="$post->category?->name"
                            :date="$post->published_at?->format('Y/m/d')"
                            :image="$post->media->first()?->url()"
                            :href="route('blog.show', $post->slug)"
                        />
                    @endforeach
                </div>
            </x-layout.container>
        </x-layout.section>
    @endif

    {{-- CTA --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <div class="home-cta rounded-[2rem] px-6 py-12 text-white sm:px-10 sm:py-14 lg:px-16">
                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div class="text-sm font-bold text-white/60">قدم بعدی</div>
                        <h2 class="mt-3 max-w-2xl text-3xl font-black leading-tight sm:text-4xl">
                            مسیر یادگیریت را از همین امروز شروع کن.
                        </h2>
                        <p class="mt-4 max-w-2xl text-sm leading-8 text-white/70 sm:text-base">
                            یک دوره مناسب پیدا کن، مدرس‌ها را ببین و اولین قدم را ساده بردار.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <x-ui.button href="{{ route('courses.index') }}" variant="secondary" size="lg">
                            مشاهده دوره‌ها
                        </x-ui.button>
                        <x-ui.button href="{{ route('teachers.index') }}" variant="ghost" size="lg" class="bg-white/10 text-white hover:bg-white/15 hover:text-white">
                            دیدن مدرس‌ها
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
