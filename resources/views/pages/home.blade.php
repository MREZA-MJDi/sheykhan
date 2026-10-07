@extends('layouts.app')

@section('title', 'شیخان | آموزش برای آینده')
@section('description', 'شیخان؛ مسیر یکپارچه آموزش، کلاس، تمرین، آزمون و رشد دانش‌آموزان.')

@section('content')
    @php
        $fa = fn ($value) => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $heroCourse = $courseCards[0] ?? null;
    @endphp

    {{-- Hero --}}
    <section class="home-hero">
        <x-layout.container size="wide">
            <div class="home-hero-grid">
                <div class="home-hero-copy home-reveal">
                    <span class="home-eyebrow"><i></i> آکادمی شیخان</span>

                    <h1>
                        آموزش خوب،
                        <span>مسیر روشن‌تری</span>
                        برای آینده می‌سازد.
                    </h1>

                    <p>
                        دوره، کلاس، تمرین و آزمون در یک مسیر منظم؛
                        برای دانش‌آموزی که می‌خواهد فقط درس نخواند، بلکه پیشرفت خودش را ببیند.
                    </p>

                    <div class="home-actions">
                        <x-ui.button href="{{ route('courses.index') }}" variant="primary" size="xl">
                            شروع مسیر یادگیری <span aria-hidden="true">←</span>
                        </x-ui.button>
                        <x-ui.button href="{{ route('teachers.index') }}" variant="outline" size="xl">
                            آشنایی با اساتید
                        </x-ui.button>
                    </div>

                    <div class="home-proof">
                        <div><strong>{{ $fa($stats['courses']) }}+</strong><span>دوره</span></div>
                        <div><strong>{{ $fa($stats['teachers']) }}+</strong><span>مدرس</span></div>
                        <div><strong>{{ $fa($stats['students']) }}+</strong><span>دانش‌آموز</span></div>
                    </div>
                </div>

                <div class="home-hero-visual home-reveal" data-delay="2">
                    <div class="home-hero-glow"></div>
                    <div class="home-hero-panel">
                        <div class="home-panel-top">
                            <div>
                                <small>شیخان / مسیر یادگیری</small>
                                <strong>امروز یک قدم جلوتر</strong>
                            </div>
                            <span class="home-mark">ش</span>
                        </div>

                        @if($heroCourse)
                            <a href="{{ $heroCourse['href'] }}" class="home-feature-course">
                                <div class="home-feature-image">
                                    @if($heroCourse['image'])
                                        <img src="{{ $heroCourse['image'] }}" alt="{{ $heroCourse['title'] }}" fetchpriority="high">
                                    @else
                                        <div class="home-feature-fallback"></div>
                                    @endif
                                    <div class="home-feature-overlay"></div>
                                    <div class="home-feature-label">{{ $heroCourse['category'] ?: 'دوره آموزشی' }}</div>
                                </div>
                                <div class="home-feature-body">
                                    <div>
                                        <small>دوره منتخب</small>
                                        <h2>{{ $heroCourse['title'] }}</h2>
                                    </div>
                                    <span class="home-arrow">←</span>
                                </div>
                                <div class="home-feature-meta">
                                    <span>{{ $heroCourse['lessons'] }} درس</span>
                                    <span>{{ $heroCourse['duration'] }}</span>
                                    <span>{{ $heroCourse['price'] }}</span>
                                </div>
                            </a>
                        @else
                            <div class="home-empty-hero">
                                <strong>مسیر یادگیریت را شروع کن</strong>
                                <p>دوره‌های منتشرشده شیخان به‌محض آماده‌شدن اینجا نمایش داده می‌شوند.</p>
                                <a href="{{ route('courses.index') }}">مشاهده دوره‌ها ←</a>
                            </div>
                        @endif

                        <div class="home-hero-bottom">
                            <span><b>01</b> یادگیری</span>
                            <span><b>02</b> تمرین</span>
                            <span><b>03</b> پیشرفت</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-layout.container>
    </section>

    {{-- Trust / paths --}}
    <section class="home-paths">
        <x-layout.container size="wide">
            <div class="home-path-grid">
                <a href="{{ route('courses.index') }}"><b>دوره‌ها</b><span>مسیرهای آموزشی منظم و قابل پیگیری</span><i>←</i></a>
                <a href="{{ route('courses.index') }}"><b>کلاس‌ها</b><span>آموزش و تعامل در فضای یکپارچه</span><i>←</i></a>
                <a href="{{ route('courses.index') }}"><b>تمرین و آزمون</b><span>یادگیری را به نتیجه قابل سنجش تبدیل کن</span><i>←</i></a>
                <a href="{{ route('teachers.index') }}"><b>اساتید</b><span>آشنایی با مدرس‌های تأییدشده شیخان</span><i>←</i></a>
            </div>
        </x-layout.container>
    </section>

    {{-- Courses --}}
    <section class="home-section home-soft">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>دوره‌های منتخب</span>
                    <h2>از همین‌جا مسیرت را پیدا کن.</h2>
                    <p>دوره‌های منتشرشده از آموزشگاه‌های فعال شیخان.</p>
                </div>
                <a href="{{ route('courses.index') }}">همه دوره‌ها <i>←</i></a>
            </div>

            <div class="home-course-grid">
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
                    <div class="home-empty-state">
                        <strong>هنوز دوره‌ای منتشر نشده است.</strong>
                        <span>به‌محض انتشار، دوره‌ها در این بخش نمایش داده می‌شوند.</span>
                    </div>
                @endforelse
            </div>
        </x-layout.container>
    </section>

    {{-- Why Sheykhan --}}
    <section class="home-section home-dark">
        <x-layout.container size="wide">
            <div class="home-why">
                <div class="home-why-copy">
                    <span>چرا شیخان؟</span>
                    <h2>یک پلتفرم؛<br><em>چهار تجربه آموزشی.</em></h2>
                    <p>
                        دانش‌آموز، مدرس، والد و آموزشگاه هرکدام محیط مخصوص خودشان را دارند،
                        اما همه در یک مسیر آموزشی به هم متصل‌اند.
                    </p>
                </div>
                <div class="home-role-grid">
                    <article><b>۰۱</b><strong>دانش‌آموز</strong><p>دوره، کلاس، تمرین و پیشرفت در فضای شخصی.</p></article>
                    <article><b>۰۲</b><strong>مدرس</strong><p>ساخت و مدیریت آموزش و ارتباط با دانش‌آموزان.</p></article>
                    <article><b>۰۳</b><strong>والد</strong><p>تصویر روشن‌تر از مسیر و عملکرد فرزند.</p></article>
                    <article><b>۰۴</b><strong>آموزشگاه</strong><p>مدیریت دوره، کلاس، مدرس و ساختار آموزشی.</p></article>
                </div>
            </div>
        </x-layout.container>
    </section>

    {{-- Teachers --}}
    <section class="home-section">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>اساتید</span>
                    <h2>آدم‌های خوب، آموزش خوب می‌سازند.</h2>
                    <p>مدرس‌های تأییدشده و فعال شیخان.</p>
                </div>
                <a href="{{ route('teachers.index') }}">همه اساتید <i>←</i></a>
            </div>

            <div class="home-teacher-grid">
                @forelse($teacherCards as $teacher)
                    <article class="home-teacher-card">
                        <div class="home-teacher-avatar">
                            @if($teacher['avatar'])
                                <img src="{{ $teacher['avatar'] }}" alt="{{ $teacher['name'] }}" loading="lazy">
                            @else
                                <span>{{ mb_substr($teacher['name'], 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <span>{{ $teacher['role'] }}</span>
                            <h3>{{ $teacher['name'] }}</h3>
                            @if($teacher['bio'])
                                <p>{{ IlluminateSupportStr::limit($teacher['bio'], 105) }}</p>
                            @endif
                            <small>{{ $fa($teacher['courses']) }} دوره منتشرشده</small>
                        </div>
                    </article>
                @empty
                    <div class="home-empty-state"><strong>هنوز مدرس عمومی ثبت نشده است.</strong></div>
                @endforelse
            </div>
        </x-layout.container>
    </section>

    {{-- Live classes --}}
    @if(count($liveClassCards))
        <section class="home-section home-soft">
            <x-layout.container size="wide">
                <div class="home-heading">
                    <div>
                        <span>کلاس‌های پیش‌رو</span>
                        <h2>جلسه بعدی را از دست نده.</h2>
                    </div>
                </div>
                <div class="home-live-grid">
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
        </section>
    @endif

    {{-- Public product destinations: structure only, no fake DB data --}}
    <section class="home-section">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>فروشگاه آموزشی</span>
                    <h2>ابزارهای یادگیری، کنار مسیر تو.</h2>
                    <p>ساختار فروشگاه برای کتاب، جزوه و آزمون؛ بدون نمایش داده ساختگی.</p>
                </div>
            </div>
            <div class="home-store-grid">
                <a href="#" class="home-store-card"><b>۰۱</b><strong>کتاب و ترجمه</strong><span>منابع آموزشی و کتاب‌های منتخب</span><i>به‌زودی ←</i></a>
                <a href="#" class="home-store-card"><b>۰۲</b><strong>جزوات</strong><span>جزوه و محتوای تکمیلی کلاس‌ها</span><i>به‌زودی ←</i></a>
                <a href="#" class="home-store-card"><b>۰۳</b><strong>آزمون</strong><span>آزمون‌ها و بسته‌های ارزیابی آموزشی</span><i>به‌زودی ←</i></a>
            </div>
        </x-layout.container>
    </section>

    {{-- Academy content --}}
    @if($latestPosts->isNotEmpty())
        <section class="home-section home-soft">
            <x-layout.container size="wide">
                <div class="home-heading">
                    <div>
                        <span>آکادمی</span>
                        <h2>محتوایی فراتر از کلاس.</h2>
                        <p>مقاله‌ها و محتوای آموزشی منتشرشده در شیخان.</p>
                    </div>
                    <a href="{{ route('blog.index') }}">مرکز محتوا <i>←</i></a>
                </div>
                <div class="home-post-grid">
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
        </section>
    @endif

    {{-- Required public trust slots: no fabricated testimonials/achievers --}}
    <section class="home-section home-trust">
        <x-layout.container size="wide">
            <div class="home-trust-grid">
                <article>
                    <span>افتخارآفرینان</span>
                    <h2>موفقیت دانش‌آموزان، بخشی از داستان شیخان است.</h2>
                    <p>این بخش بعد از آماده‌شدن داده واقعی قبولی‌ها با نام دانش‌آموز و مدرسه تکمیل می‌شود.</p>
                </article>
                <article>
                    <span>تجربه خانواده‌ها</span>
                    <h2>صدای والدین و دانش‌آموزان، واقعی و قابل اعتماد.</h2>
                    <p>تصویر، صوت و ویدئوی رضایت‌ها پس از آماده‌شدن منبع واقعی در همین ساختار نمایش داده خواهد شد.</p>
                </article>
            </div>
        </x-layout.container>
    </section>

    {{-- CTA --}}
    <section class="home-section">
        <x-layout.container size="wide">
            <div class="home-cta">
                <div>
                    <span>قدم بعدی</span>
                    <h2>مسیر یادگیریت را از همین امروز شروع کن.</h2>
                    <p>دوره‌ها را ببین و اولین قدم را ساده بردار.</p>
                </div>
                <div class="home-actions">
                    <x-ui.button href="{{ route('courses.index') }}" variant="secondary" size="lg">مشاهده دوره‌ها</x-ui.button>
                    <x-ui.button href="{{ route('teachers.index') }}" variant="ghost" size="lg" class="bg-white/10 text-white hover:bg-white/15 hover:text-white">اساتید</x-ui.button>
                </div>
            </div>
        </x-layout.container>
    </section>
@endsection
