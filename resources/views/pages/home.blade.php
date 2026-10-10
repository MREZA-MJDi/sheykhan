@extends('layouts.app')

@section('title', 'شیخان | آموزش برای آینده')
@section('description', 'شیخان؛ مسیر یکپارچه آموزش، کلاس، تمرین، آزمون و رشد دانش‌آموزان.')

@section('content')
    @php
        $fa = fn ($value) => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp
{{-- Full-bleed editorial home hero; academy-managed banners remain a separate section below. --}}
    @include('components.branding.home-meraki-hero')

    @if($homeBanners->isNotEmpty())
        <section class="home-section home-banner-stage" aria-label="بنرهای ویژه شیخان">
            <x-layout.container size="wide">
                <div class="home-heading home-banner-heading">
                    <div>
                        <span>ویژه شیخان</span>
                        <h2>چیزی تازه برای مسیر یادگیری.</h2>
                        <p>بنرهای به‌روزشده آموزشگاه‌ها را ببین و مستقیم وارد محتوای مرتبط شو.</p>
                    </div>
                </div>

                <div class="home-banner-grid">
                    @foreach($homeBanners as $banner)
                        <article class="home-banner-card">
                            <div class="home-banner-media">
                                <img
                                    src="{{ $banner['image'] }}"
                                    alt="{{ $banner['title'] ?: 'بنر شیخان' }}"
                                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                    style="object-position: {{ $banner['cropX'] }}% {{ $banner['cropY'] }}%;"
                                >
                                <span class="home-banner-academy">{{ $banner['academy'] ?: 'شیخان' }}</span>
                            </div>
                            <div class="home-banner-body">
                                <div>
                                    @if($banner['title'])
                                        <h3>{{ $banner['title'] }}</h3>
                                    @endif
                                    @if($banner['description'])
                                        <p>{{ $banner['description'] }}</p>
                                    @endif
                                </div>

                                @if($banner['ctaUrl'] && $banner['ctaLabel'])
                                    <a href="{{ $banner['ctaUrl'] }}" class="home-banner-link">{{ $banner['ctaLabel'] }} <span aria-hidden="true">←</span></a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </x-layout.container>
        </section>
    @endif

    {{-- Trust / paths --}}
    <section class="home-paths">
        <x-layout.container size="wide">
            <div class="home-path-grid">
                <a href="{{ route('courses.index') }}"><b>دوره‌ها</b><span>مسیرهای آموزشی منظم و قابل پیگیری</span><i>←</i></a>
                <a href="{{ route('teachers.index') }}"><b>مدرس‌ها</b><span>اساتید تأییدشده و مسیرهای تخصصی آموزش</span><i>←</i></a>
                <a href="{{ route('blog.index') }}"><b>مجله شیخان</b><span>مقاله‌ها و محتوای کاربردی برای یادگیری بهتر</span><i>←</i></a>
                <a href="{{ route('store.index') }}"><b>فروشگاه</b><span>منابع آموزشی و محصولات منتشرشده شیخان</span><i>←</i></a>
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

    {{-- Latest articles --}}
    <section class="home-section home-blog">
        <x-layout.container size="wide">
            <div class="home-heading">
                <div>
                    <span>مجله شیخان</span>
                    <h2>محتوا را فقط نخوان؛ از آن چیزی یاد بگیر.</h2>
                    <p>یادداشت‌ها و مطالب منتشرشده برای دانش‌آموز، والد و مسیر یادگیری بهتر.</p>
                </div>
                <a href="{{ route('blog.index') }}">رفتن به مجله <i>←</i></a>
            </div>

            <div class="home-post-grid">
                @forelse($latestPosts as $post)
                    @php
                        $postImage = $post->media?->first()?->url();
                        $postDate = $post->published_at;
                        $postCategory = $post->category?->name;
                    @endphp

                    <article class="home-post-card">
                        <a href="{{ route('blog.show', $post->slug) }}" class="home-post-media" aria-label="مطالعه {{ $post->title }}">
                            @if($postImage)
                                <img src="{{ $postImage }}" alt="{{ $post->title }}" loading="lazy">
                            @else
                                <div class="home-post-placeholder" aria-hidden="true">
                                    <span>ش</span>
                                </div>
                            @endif
                            <span class="home-post-index">{{ $loop->iteration < 10 ? '۰' . $loop->iteration : $fa($loop->iteration) }}</span>
                        </a>

                        <div class="home-post-body">
                            <div class="home-post-meta">
                                @if($postCategory)<span>{{ $postCategory }}</span>@endif
                                @if($postDate)<time datetime="{{ $postDate->toDateString() }}">{{ \App\Support\PersianUi::date($postDate) }}</time>@endif
                            </div>

                            <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>

                            @if($post->excerpt)
                                <p>{{ \Illuminate\Support\Str::limit($post->excerpt, 125) }}</p>
                            @endif

                            <a href="{{ route('blog.show', $post->slug) }}" class="home-post-link">
                                <span>مطالعه مطلب</span>
                                <i aria-hidden="true">←</i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="home-empty-state">
                        <strong>هنوز مطلبی منتشر نشده است.</strong>
                        <span>به‌محض انتشار، جدیدترین مطالب اینجا دیده می‌شوند.</span>
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
                <div class="home-role-grid home-feature-grid">
                    <article class="home-feature-card" data-feature-index="01">
                        <span class="home-feature-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 32 32" fill="none"><path d="M4 12.5 16 6l12 6.5L16 19 4 12.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 16v6.2c4.3 3.4 9.7 3.4 14 0V16M28 13v7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </span>
                        <div class="home-feature-card__code"><b>۰۱</b><span>LEARN</span></div>
                        <strong>دانش‌آموز</strong>
                        <p>دوره، کلاس، تکلیف، آزمون و پیشرفت در یک فضای شخصی.</p>
                        <span class="home-feature-card__note">کارتابل یادگیری <i aria-hidden="true">↗</i></span>
                    </article>
                    <article class="home-feature-card" data-feature-index="02">
                        <span class="home-feature-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 32 32" fill="none"><rect x="5" y="5.5" width="22" height="15" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M11 26.5h10M16 20.5v6M9 10h8m-8 4h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </span>
                        <div class="home-feature-card__code"><b>۰۲</b><span>TEACH</span></div>
                        <strong>مدرس</strong>
                        <p>مدیریت کلاس، حضور و غیاب، تکلیف، آزمون و تصحیح.</p>
                        <span class="home-feature-card__note">فضای تدریس <i aria-hidden="true">↗</i></span>
                    </article>
                    <article class="home-feature-card" data-feature-index="03">
                        <span class="home-feature-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 32 32" fill="none"><circle cx="12" cy="11" r="4" stroke="currentColor" stroke-width="1.6"/><circle cx="23" cy="13" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M4.5 26c.7-5 3.1-7.6 7.5-7.6s6.8 2.6 7.5 7.6M20 20c3.9-.3 6.1 1.7 7 5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                        </span>
                        <div class="home-feature-card__code"><b>۰۳</b><span>FOLLOW</span></div>
                        <strong>والد</strong>
                        <p>دیدی روشن‌تر از مسیر آموزشی و عملکرد فرزند.</p>
                        <span class="home-feature-card__note">نمای پیگیری <i aria-hidden="true">↗</i></span>
                    </article>
                    <article class="home-feature-card" data-feature-index="04">
                        <span class="home-feature-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 32 32" fill="none"><rect x="5" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="18" y="5" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="5" y="18" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.6"/><rect x="18" y="18" width="9" height="9" rx="1.5" stroke="currentColor" stroke-width="1.6"/></svg>
                        </span>
                        <div class="home-feature-card__code"><b>۰۴</b><span>MANAGE</span></div>
                        <strong>آموزشگاه</strong>
                        <p>مدیریت افراد، کلاس‌ها، محتوا، دوره‌ها و گزارش‌ها.</p>
                        <span class="home-feature-card__note">مرکز مدیریت <i aria-hidden="true">↗</i></span>
                    </article>
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
                    <x-education.teacher-card
                        :name="$teacher['name']"
                        :role="$teacher['role']"
                        :avatar="$teacher['avatar']"
                        :bio="$teacher['bio']"
                        :courses="$teacher['courses']"
                        :href="$teacher['href']"
                    />
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
    @if(count($storeCategories) || count($productCards))
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
                        <a href="{{ $product['href'] }}" class="home-product-card">
                            @if($product['image'])
                                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy">
                            @else
                                <span class="home-product-placeholder" aria-hidden="true">ش</span>
                            @endif
                            <span>{{ $product['category'] ?: 'منبع آموزشی' }}</span>
                            <h3>{{ $product['title'] }}</h3>
                            <strong>{{ $product['price'] }}</strong>
                            <span class="home-product-link">مشاهده جزئیات <i aria-hidden="true">←</i></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-layout.container>
    </section>
    @endif

    {{-- Academy editorial content: show only content paths that have public material. --}}
    @php
        $academyPathTitles = [
            'parents' => 'سخنی با اولیاء',
            'students' => 'سخنی با دانش‌آموزان',
            'gifted' => 'سلام تیزهوشان',
            'foreign-resources' => 'نکاتی از سوالات منابع خارجی',
            'question-designer' => 'اگر من طراح سوال بودم',
        ];
        $publicAcademyPaths = collect($academyPathTitles)
            ->map(fn ($title, $slug) => [
                'slug' => $slug,
                'title' => $title,
                'items' => $academyContentGroups[$slug] ?? [],
            ])
            ->filter(fn ($path) => count($path['items']) > 0)
            ->values();
    @endphp
    @if($publicAcademyPaths->isNotEmpty())
        <section id="academy-content" class="home-section home-soft">
            <x-layout.container size="wide">
                <div class="home-heading">
                    <div>
                        <span>آکادمی شیخان</span>
                        <h2>محتوایی فراتر از کلاس.</h2>
                        <p>{{ $fa($publicAcademyPaths->count()) }} مسیر محتوایی فعال؛ مقاله‌ها و ویدئوهایی که همین حالا قابل مشاهده‌اند.</p>
                    </div>
                    <a href="{{ route('blog.index') }}">مطالب آموزشی بیشتر <i>←</i></a>
                </div>

                <div class="home-academy-grid">
                    @foreach($publicAcademyPaths as $path)
                        <article class="home-academy-card">
                            <span>{{ $path['title'] }}</span>
                            <h3>محتوای منتشرشده</h3>
                            @foreach($path['items'] as $item)
                                @if($item['href'])
                                    <a href="{{ $item['href'] }}" class="home-academy-item">
                                        <b>{{ $item['type'] === 'video' ? 'ویدئو' : 'مقاله' }}</b>
                                        <strong>{{ $item['title'] }}</strong>
                                        @if($item['duration'])<small>{{ $item['duration'] }}</small>@endif
                                    </a>
                                @else
                                    <div class="home-academy-item">
                                        <b>{{ $item['type'] === 'video' ? 'ویدئو' : 'مقاله' }}</b>
                                        <strong>{{ $item['title'] }}</strong>
                                        @if($item['duration'])<small>{{ $item['duration'] }}</small>@endif
                                    </div>
                                @endif
                            @endforeach
                        </article>
                    @endforeach
                </div>
            </x-layout.container>
        </section>
    @endif

    {{-- Achievers --}}
    @if(count($achievementCards))
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
    @endif

    {{-- Testimonials --}}
    @if(count($testimonialCards))
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
                        @if($testimonial['audio'])
                            <audio class="mt-4 w-full" controls preload="none">
                                <source src="{{ $testimonial['audio'] }}" type="audio/mpeg">
                                پخش صوت در مرورگر شما پشتیبانی نمی‌شود.
                            </audio>
                        @endif
                        @if($testimonial['video'])
                            <video class="mt-4 aspect-video w-full rounded-xl object-cover" controls playsinline preload="none">
                                <source src="{{ $testimonial['video'] }}" type="video/mp4">
                                پخش ویدئو در مرورگر شما پشتیبانی نمی‌شود.
                            </video>
                        @endif
                        <strong>{{ $testimonial['name'] }}</strong>
                        <span>{{ $testimonial['role'] === 'parent' ? 'والد دانش‌آموز' : 'دانش‌آموز' }}</span>
                    </article>
                @empty
                    <div class="home-empty-state"><strong>تجربه‌های ثبت‌شده هنوز آماده انتشار نیستند.</strong></div>
                @endforelse
            </div>
        </x-layout.container>
    </section>
    @endif

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
