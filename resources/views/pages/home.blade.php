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
                        :grades="$course['grades'] ?? []"
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
                                <p>{{ \Illuminate\Support\Str::limit($teacher['bio'], 105) }}</p>
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

    {{-- Educational store --}}
    <section class="home-section">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>فروشگاه آموزشی</span>
                    <h2>منابعی که مسیر یادگیری را کامل می‌کنند.</h2>
                    <p>کتاب، جزوه و آزمون با همان زیرساخت فروش و دسترسی امن پلتفرم.</p>
                </div>
            </div>

            <div class="home-store-grid">
                @forelse($storeCategories as $index => $category)
                    <a href="{{ url('/store?category='.$category['slug']) }}" class="home-store-card">
                        <b>{{ $fa(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) }}</b>
                        <strong>{{ $category['name'] }}</strong>
                        <span>{{ $category['description'] ?: 'منابع آموزشی منتخب شیخان' }}</span>
                        <i>مشاهده محصولات ←</i>
                    </a>
                @empty
                    <div class="home-empty-state"><strong>دسته‌های فروشگاه هنوز فعال نشده‌اند.</strong></div>
                @endforelse
            </div>

            @if(count($productCards))
                <div class="home-product-strip">
                    @foreach($productCards as $product)
                        <article class="home-product-card">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy">
                            @endif
                            <span>{{ $product['category'] }}</span>
                            <h3>{{ $product['title'] }}</h3>
                            <strong>{{ $product['price'] }}</strong>
                        </article>
                    @endforeach
                </div>
            @endif
        </x-layout.container>
    </section>

    {{-- Academy editorial content --}}
    <section class="home-section home-soft">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>آکادمی شیخان</span>
                    <h2>محتوایی فراتر از کلاس.</h2>
                    <p>پنج مسیر محتوایی رسمی آکادمی برای والدین، دانش‌آموزان و علاقه‌مندان.</p>
                </div>
                <a href="{{ route('blog.index') }}">مقالات آکادمی <i>←</i></a>
            </div>

            <div class="home-academy-grid">
                @foreach([
                    'parents' => 'سخنی با اولیاء',
                    'students' => 'سخنی با دانش‌آموزان',
                    'gifted' => 'سلام تیزهوشان',
                    'foreign-resources' => 'نکاتی از سوالات منابع خارجی',
                    'question-designer' => 'اگر من طراح سوال بودم',
                ] as $slug => $title)
                    @php($items = $academyContentGroups[$slug] ?? [])
                    <article class="home-academy-card">
                        <span>{{ $title }}</span>
                        <h3>{{ count($items) ? 'جدیدترین محتوای این مسیر' : 'در حال آماده‌سازی' }}</h3>
                        @forelse($items as $item)
                            <div class="home-academy-item">
                                <b>{{ $item['type'] === 'video' ? 'ویدئو' : 'مقاله' }}</b>
                                <strong>{{ $item['title'] }}</strong>
                                @if($item['duration']) <small>{{ $item['duration'] }}</small> @endif
                            </div>
                        @empty
                            <p>محتوای رسمی این بخش پس از انتشار در اینجا نمایش داده می‌شود.</p>
                        @endforelse
                    </article>
                @endforeach
            </div>
        </x-layout.container>
    </section>

    {{-- Achievers --}}
    <section class="home-section home-trust">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>افتخارآفرینان شیخان</span>
                    <h2>نتیجه‌ای که دیده می‌شود.</h2>
                    <p>دانش‌آموزانی که مسیرشان به قبولی‌های ارزشمند رسیده است.</p>
                </div>
            </div>

            <div class="home-achievement-grid">
                @forelse($achievementCards as $achievement)
                    <article class="home-achievement-card">
                        @if($achievement['image'])
                            <img src="{{ $achievement['image'] }}" alt="{{ $achievement['name'] }}" loading="lazy">
                        @endif
                        <div>
                            <span>{{ $achievement['type'] }}</span>
                            <h3>{{ $achievement['name'] }}</h3>
                            @if($achievement['school']) <p>{{ $achievement['school'] }}</p> @endif
                            <small>{{ $achievement['title'] }}</small>
                        </div>
                    </article>
                @empty
                    <div class="home-empty-state"><strong>افتخارآفرینان هنوز ثبت نشده‌اند.</strong></div>
                @endforelse
            </div>
        </x-layout.container>
    </section>

    {{-- Testimonials --}}
    <section class="home-section home-dark">
        <x-layout.container size="wide">
            <div class="home-heading home-heading-dark">
                <div>
                    <span>تجربه خانواده‌ها</span>
                    <h2>اعتماد، از تجربه واقعی می‌آید.</h2>
                    <p>صدای والدین و دانش‌آموزان، همراه با رسانه‌های واقعی ثبت‌شده در پنل.</p>
                </div>
            </div>

            <div class="home-testimonial-grid">
                @forelse($testimonialCards as $testimonial)
                    <article class="home-testimonial-card">
                        @if($testimonial['image'])
                            <img src="{{ $testimonial['image'] }}" alt="" loading="lazy">
                        @endif
                        <p>«{{ $testimonial['text'] }}»</p>
                        <strong>{{ $testimonial['name'] }}</strong>
                        <span>{{ $testimonial['role'] === 'parent' ? 'والد دانش‌آموز' : 'دانش‌آموز' }}</span>
                    </article>
                @empty
                    <div class="home-empty-state"><strong>تجربه‌های ثبت‌شده هنوز آماده انتشار نیستند.</strong></div>
                @endforelse
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
