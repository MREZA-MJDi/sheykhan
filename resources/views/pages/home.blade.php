@extends('layouts.app')

@section('title', 'شیخان | آموزش، رشد، آینده')
@section('description', 'شیخان؛ پلتفرم یکپارچه آموزش آنلاین، کلاس، تمرین، آزمون و پیگیری پیشرفت.')

@section('content')
    @php
        $fa = fn ($value) => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        $heroCourse = $courseCards[0] ?? null;
    @endphp

    {{-- Hero --}}
    <section class="relative isolate overflow-hidden border-b border-[var(--color-border)] bg-[var(--color-background)]">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -right-36 -top-36 h-[28rem] w-[28rem] rounded-full bg-[var(--color-primary-100)] opacity-70 blur-3xl"></div>
            <div class="absolute -bottom-44 -left-36 h-[30rem] w-[30rem] rounded-full bg-[var(--color-accent-100)] opacity-40 blur-3xl"></div>
        </div>

        <x-layout.container size="wide">
            <div class="grid min-h-[min(760px,calc(100vh-5rem))] items-center gap-12 py-14 sm:py-20 lg:grid-cols-[1.05fr_0.95fr] lg:py-24">

                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3 py-1.5 text-xs font-bold text-[var(--color-primary-700)]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
                        محیط یکپارچه آموزش و یادگیری
                    </div>

                    <h1 class="mt-6 max-w-3xl text-balance text-4xl font-black tracking-tight text-[var(--color-text)] sm:text-5xl lg:text-6xl xl:text-7xl">
                        مسیر یادگیریت را
                        <span class="text-[var(--color-primary-600)]">خودت بساز.</span>
                    </h1>

                    <p class="mt-6 max-w-2xl text-pretty text-base leading-8 text-[var(--color-text-muted)] sm:text-lg">
                        دوره، کلاس آنلاین، تمرین، آزمون و پیشرفت آموزشی در یک تجربه یکپارچه برای دانش‌آموز،
                        مدرس، والد و آموزشگاه.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <x-ui.button href="{{ route('courses.index') }}" variant="primary" size="lg">
                            کشف دوره‌ها
                            <span aria-hidden="true">←</span>
                        </x-ui.button>

                        <x-ui.button href="{{ route('teachers.index') }}" variant="outline" size="lg">
                            آشنایی با مدرس‌ها
                        </x-ui.button>
                    </div>

                    <div class="mt-10 grid max-w-2xl grid-cols-3 divide-x divide-x-reverse divide-[var(--color-border)] border-y border-[var(--color-border)] py-5">
                        <x-education.stat-card
                            :value="$fa($stats['courses']) . '+'"
                            label="دوره منتشرشده"
                        />
                        <div class="px-4 sm:px-6">
                            <div class="text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl">
                                {{ $fa($stats['teachers']) }}+
                            </div>
                            <div class="mt-1 text-sm font-semibold text-[var(--color-text-muted)]">
                                مدرس تاییدشده
                            </div>
                        </div>
                        <div class="px-4 sm:px-6">
                            <div class="text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl">
                                {{ $fa($stats['students']) }}+
                            </div>
                            <div class="mt-1 text-sm font-semibold text-[var(--color-text-muted)]">
                                دانش‌آموز
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl">
                    <div class="absolute inset-x-10 top-10 h-72 rounded-full bg-[var(--color-primary-100)] blur-3xl"></div>

                    <div class="relative rounded-[2rem] border border-[var(--color-border)] bg-white p-3 shadow-[var(--shadow-xl)] sm:p-4">
                        <div class="overflow-hidden rounded-[1.5rem] bg-[var(--color-slate-900)] p-5 text-white sm:p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-xs text-white/60">شیخان</div>
                                    <div class="mt-1 text-lg font-bold">مسیر بعدی تو</div>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 font-black">
                                    ش
                                </div>
                            </div>

                            @if($heroCourse)
                                <div class="mt-6 overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                                    <div class="relative aspect-[16/8] overflow-hidden bg-white/10">
                                        @if($heroCourse['image'])
                                            <img
                                                src="{{ $heroCourse['image'] }}"
                                                alt="{{ $heroCourse['title'] }}"
                                                class="h-full w-full object-cover"
                                            >
                                        @else
                                            <div class="flex h-full items-center justify-center text-white/50">
                                                <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 18.5v-13Z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m7 16 3-3 2.5 2 2.5-4 2 3"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-5">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="text-xs text-white/60">{{ $heroCourse['category'] ?? 'آموزش' }}</span>
                                            <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-bold text-white/80">
                                                {{ $heroCourse['level'] ?: 'عمومی' }}
                                            </span>
                                        </div>

                                        <h2 class="mt-3 text-xl font-black">{{ $heroCourse['title'] }}</h2>

                                        <p class="mt-2 line-clamp-2 text-sm leading-7 text-white/60">
                                            {{ $heroCourse['description'] }}
                                        </p>

                                        <div class="mt-5 flex flex-wrap items-center gap-4 text-xs text-white/60">
                                            <span>{{ $heroCourse['lessons'] }} درس</span>
                                            <span>{{ $heroCourse['duration'] }}</span>
                                            <span class="ms-auto font-bold text-white">{{ $heroCourse['price'] }}</span>
                                        </div>

                                        <x-ui.button
                                            href="{{ $heroCourse['href'] }}"
                                            variant="secondary"
                                            size="md"
                                            block
                                            class="mt-5"
                                        >
                                            مشاهده دوره
                                        </x-ui.button>
                                    </div>
                                </div>
                            @else
                                <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-7 text-center">
                                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 font-black">ش</div>
                                    <h2 class="mt-4 text-xl font-bold">مسیر یادگیری آماده است</h2>
                                    <p class="mt-2 text-sm leading-7 text-white/60">
                                        اولین دوره‌ات را از کاتالوگ انتخاب کن.
                                    </p>
                                    <x-ui.button href="{{ route('courses.index') }}" variant="secondary" size="md" class="mt-5">
                                        مشاهده دوره‌ها
                                    </x-ui.button>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-2 p-1 pt-4 sm:gap-3 sm:p-2 sm:pt-4">
                            <div class="rounded-xl bg-[var(--color-slate-100)] px-3 py-4 text-center">
                                <div class="text-xs text-[var(--color-text-muted)]">آموزش</div>
                                <div class="mt-1 font-bold text-[var(--color-text)]">دوره</div>
                            </div>
                            <div class="rounded-xl bg-[var(--color-primary-50)] px-3 py-4 text-center">
                                <div class="text-xs text-[var(--color-text-muted)]">تعامل</div>
                                <div class="mt-1 font-bold text-[var(--color-text)]">کلاس</div>
                            </div>
                            <div class="rounded-xl bg-[var(--color-accent-50)] px-3 py-4 text-center">
                                <div class="text-xs text-[var(--color-text-muted)]">ارزیابی</div>
                                <div class="mt-1 font-bold text-[var(--color-text)]">آزمون</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </x-layout.container>
    </section>

    {{-- Featured courses --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <x-layout.section-heading
                eyebrow="یادگیری"
                title="دوره‌های منتخب"
                description="دوره‌های منتشرشده‌ای که همین حالا می‌توانی واردشان شوی."
                href="{{ route('courses.index') }}"
                link-label="مشاهده همه"
            />

            <div class="mt-10 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
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
                    <div class="sm:col-span-2 xl:col-span-3">
                        <x-ui.empty-state title="هنوز دوره‌ای منتشر نشده" description="به‌محض انتشار دوره، این بخش به‌صورت خودکار به‌روزرسانی می‌شود." />
                    </div>
                @endforelse
            </div>
        </x-layout.container>
    </x-layout.section>

    {{-- Live classes --}}
    @if(count($liveClassCards))
        <section class="border-y border-[var(--color-border)] bg-[var(--color-background-soft)]">
            <x-layout.section spacing="lg">
                <x-layout.container size="wide">
                    <x-layout.section-heading
                        eyebrow="تعامل زنده"
                        title="کلاس‌های آنلاین پیش‌رو"
                        description="جلسه‌های زمان‌بندی‌شده از داده واقعی کلاس‌های شیخان."
                    />

                    <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($liveClassCards as $class)
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
        </section>
    @endif

    {{-- Teachers --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <x-layout.section-heading
                eyebrow="مدرس‌ها"
                title="آدم‌های پشت تجربه یادگیری"
                description="مدرس‌های تاییدشده و فعال، با تخصص واقعی و محتوای آموزشی قابل پیگیری."
                href="{{ route('teachers.index') }}"
                link-label="همه مدرس‌ها"
            />

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
                    <div class="sm:col-span-2 xl:col-span-4">
                        <x-ui.empty-state title="هنوز مدرس تاییدشده‌ای نیست" description="مدرس‌های تاییدشده اینجا نمایش داده می‌شوند." />
                    </div>
                @endforelse
            </div>
        </x-layout.container>
    </x-layout.section>

    {{-- Product pillars --}}
    <section class="border-y border-[var(--color-border)] bg-[var(--color-background-soft)]">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <x-layout.section-heading
                    eyebrow="شیخان"
                    title="یک پلتفرم؛ چند نقش؛ یک مسیر یادگیری"
                    description="هر کاربر محیط خودش را دارد و داده‌های آموزشی در یک هسته مشترک مدیریت می‌شوند."
                    center
                />

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <x-education.feature-card
                        title="یادگیری آنلاین"
                        description="دوره، درس، ویدئو و محتوای آموزشی در یک مسیر ساختاریافته."
                        icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m9 6 9 6-9 6V6Z"/><rect x="3" y="4" width="18" height="16" rx="3"/></svg>'
                    />
                    <x-education.feature-card
                        title="کلاس و تعامل"
                        description="کلاس‌های آموزشگاهی، زمان‌بندی، حضور و کلاس زنده."
                        icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6.5A2.5 2.5 0 0 1 6.5 4H20v12H6.5A2.5 2.5 0 0 0 4 18.5v-12Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 18.5A2.5 2.5 0 0 1 6.5 16H20"/></svg>'
                    />
                    <x-education.feature-card
                        title="آزمون و تمرین"
                        description="تکلیف، آزمون، تلاش‌های دانش‌آموز و نتیجه قابل پیگیری."
                        icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 2.5 2.5L16 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 3h12a2 2 0 0 1 2 2v14l-3-2-3 2-3-2-3 2-2-2-2 2V5a2 2 0 0 1 2-2Z"/></svg>'
                    />
                    <x-education.feature-card
                        title="پایش پیشرفت"
                        description="والد، دانش‌آموز و مدرس هرکدام نمای مناسب نقش خودشان را دارند."
                        icon='<svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M4 19h16"/><path stroke-linecap="round" stroke-linejoin="round" d="m7 15 3-3 3 2 5-6"/></svg>'
                    />
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>

    {{-- Latest blog --}}
    @if($latestPosts->isNotEmpty())
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <x-layout.section-heading
                    eyebrow="محتوا"
                    title="تازه‌های وبلاگ"
                    description="مقالات منتشرشده از سیستم محتوایی شیخان."
                    href="{{ route('blog.index') }}"
                    link-label="همه مقالات"
                />

                <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($latestPosts as $post)
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

    {{-- Journey --}}
    <section class="overflow-hidden bg-[var(--color-slate-950)] text-white">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <div>
                        <span class="inline-flex rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-bold text-white/70">
                            مسیر یادگیری
                        </span>

                        <h2 class="mt-5 text-3xl font-black tracking-tight text-white sm:text-4xl">
                            یادگیری را از «دیدن»
                            به «پیشرفت» تبدیل کن.
                        </h2>

                        <p class="mt-5 max-w-xl text-sm leading-8 text-white/60 sm:text-base">
                            ساختار شیخان طوری طراحی شده که محتوا، کلاس، تمرین، آزمون و گزارش از هم جدا نیستند؛
                            هرکدام بخشی از یک مسیر واحد هستند.
                        </p>

                        <div class="mt-7">
                            <x-ui.button href="{{ route('courses.index') }}" variant="secondary" size="lg">
                                شروع از دوره‌ها
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([
                            ['number' => '۰۱', 'title' => 'کشف', 'description' => 'موضوع و سطح مناسب را پیدا کن.'],
                            ['number' => '۰۲', 'title' => 'یادگیری', 'description' => 'درس‌ها را طبق مسیر جلو ببر.'],
                            ['number' => '۰۳', 'title' => 'تمرین', 'description' => 'مهارتت را با فعالیت و آزمون محک بزن.'],
                            ['number' => '۰۴', 'title' => 'پیشرفت', 'description' => 'عملکردت را ببین و قدم بعدی را انتخاب کن.'],
                        ] as $step)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 sm:p-6">
                                <div class="text-xs font-bold text-white/40">{{ $step['number'] }}</div>
                                <h3 class="mt-3 text-lg font-bold text-white">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm leading-7 text-white/50">{{ $step['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-layout.container>
        </x-layout.section>
    </section>

    {{-- Final CTA --}}
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <div class="relative overflow-hidden rounded-[2rem] border border-[var(--color-primary-200)] bg-[var(--color-primary-600)] px-6 py-14 text-white sm:px-10 sm:py-16 lg:px-16">
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-20 h-72 w-72 rounded-full bg-black/10 blur-3xl"></div>

                <div class="relative grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div>
                        <div class="text-sm font-bold text-white/60">شروع مسیر</div>
                        <h2 class="mt-3 max-w-2xl text-3xl font-black sm:text-4xl">
                            آماده‌ای یادگیری را جدی‌تر شروع کنی؟
                        </h2>
                        <p class="mt-4 max-w-2xl text-sm leading-8 text-white/70 sm:text-base">
                            اولین قدم می‌تواند فقط انتخاب یک دوره مناسب باشد.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row lg:shrink-0">
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
