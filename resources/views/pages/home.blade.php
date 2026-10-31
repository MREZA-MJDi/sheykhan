@extends('layouts.app')

@section('title', 'شیخان | آموزش برای آینده')
@section('description', 'شیخان؛ مسیر یکپارچه آموزش، کلاس، تمرین، آزمون و رشد دانش‌آموزان.')

@section('content')
    @php
        $fa = function ($value) { return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]); };
    @endphp
{{-- Full-bleed hero uses the independent HomeHeroSlide source. --}}
    @include('pages.home.partials.hero')


    {{-- Academy story: permanent, honest value proposition even before dynamic catalog content is published. --}}
    <section id="academy-story" class="home-section home-academy-story" aria-labelledby="home-academy-story-title">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
            <div class="home-academy-story__grid">
                <div class="home-academy-story__copy">
                    <span class="home-academy-story__eyebrow"><i aria-hidden="true"></i> آکادمی شیخان · یادگیری با معنا</span>
                    <h2 id="home-academy-story-title">
                        هر قدم کوچک،
                        <em>یک جهش بزرگ در یادگیری.</em>
                    </h2>
                    <p>
                        یادگیری وقتی ماندگار می‌شود که دانش‌آموز فقط شنونده نباشد.
                        در شیخان، درس‌خواندن با فهمیدن شروع می‌شود، با تمرین جان می‌گیرد
                        و با بازخورد و پیگیری مسیر ادامه پیدا می‌کند.
                    </p>

                    <div class="home-academy-story__principles" aria-label="اصول مسیر یادگیری در شیخان">
                        <div class="home-academy-story__principle">
                            <span class="home-academy-story__principle-icon" aria-hidden="true">۱</span>
                            <div><strong>مفهوم را کشف کن</strong><small>یادگیری روشن، قدم‌به‌قدم و هدفمند</small></div>
                        </div>
                        <div class="home-academy-story__principle">
                            <span class="home-academy-story__principle-icon" aria-hidden="true">۲</span>
                            <div><strong>با تمرین مسلط شو</strong><small>درس را به مهارت حل مسئله تبدیل کن</small></div>
                        </div>
                        <div class="home-academy-story__principle">
                            <span class="home-academy-story__principle-icon" aria-hidden="true">۳</span>
                            <div><strong>پیشرفتت را ببین</strong><small>با تکلیف، آزمون و بازخورد مسیرت را دنبال کن</small></div>
                        </div>
                    </div>

                    <div class="home-academy-story__actions">
                        <a href="{{ route('courses.index') }}" class="home-academy-story__primary">
                            کشف مسیرهای آموزشی <span aria-hidden="true">←</span>
                        </a>
                        <a href="{{ route('teachers.index') }}" class="home-academy-story__secondary">
                            آشنایی با مدرس‌ها <span aria-hidden="true">↗</span>
                        </a>
                    </div>
                </div>

                <div class="home-academy-story__visual" aria-hidden="true">
                    <div class="home-academy-story__visual-orbit home-academy-story__visual-orbit--outer"></div>
                    <div class="home-academy-story__visual-orbit home-academy-story__visual-orbit--inner"></div>
                    <div class="home-academy-story__visual-spark home-academy-story__visual-spark--one">✦</div>
                    <div class="home-academy-story__visual-spark home-academy-story__visual-spark--two">✳</div>

                    <div class="home-learning-card">
                        <div class="home-learning-card__top">
                            <span class="home-learning-card__brand"><span>ش</span> شیخان</span>
                            <span class="home-learning-card__label">مسیر یادگیری</span>
                        </div>
                        <div class="home-learning-card__heading">
                            <div>
                                <small>از فهمیدن تا توانستن</small>
                                <strong>یادگیریِ ماندگار</strong>
                            </div>
                            <span class="home-learning-card__book" aria-hidden="true">
                                <svg viewBox="0 0 48 48" fill="none"><path d="M5 11.5c7-2.5 13-1.4 19 3.2v26c-6-4.6-12-5.7-19-3.2v-26Z" fill="#8B7DF2"/><path d="M43 11.5c-7-2.5-13-1.4-19 3.2v26c6-4.6 12-5.7 19-3.2v-26Z" fill="#5CCBB6"/><path d="M24 14.7v26" stroke="white" stroke-width="2"/></svg>
                            </span>
                        </div>
                        <div class="home-learning-steps">
                            <div class="home-learning-step home-learning-step--active">
                                <span>۰۱</span><div><strong>یاد بگیر</strong><small>مفهوم را بفهم</small></div><i>✓</i>
                            </div>
                            <div class="home-learning-step">
                                <span>۰۲</span><div><strong>تمرین کن</strong><small>دانسته‌ها را به کار ببر</small></div><i>↗</i>
                            </div>
                            <div class="home-learning-step">
                                <span>۰۳</span><div><strong>بازخورد بگیر</strong><small>قدم بعدی را بشناس</small></div><i>✦</i>
                            </div>
                        </div>
                        <div class="home-learning-card__footer">
                            <span><i></i> قدم‌به‌قدم، با همراهی</span>
                            <span>شیخان</span>
                        </div>
                    </div>

                    <div class="home-story-float home-story-float--top">
                        <span aria-hidden="true">✦</span><div><strong>فهم عمیق</strong><small>نه فقط حفظ کردن</small></div>
                    </div>
                    <div class="home-story-float home-story-float--bottom">
                        <span aria-hidden="true">✓</span><div><strong>مسیر روشن</strong><small>درس · تمرین · بازخورد</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Academy banners remain a separate content section below the Hero. --}}
    @if($homeBanners->isNotEmpty())
        <section class="home-section home-banner-stage" aria-label="بنرهای ویژه شیخان">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
            </div>
        </section>
    @endif

    {{-- Primary destinations: clear visual navigation after the academy introduction. --}}
    <section class="home-paths" aria-labelledby="home-paths-title">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
            <div class="home-paths__intro">
                <div>
                    <span>راه‌های کشف شیخان</span>
                    <h2 id="home-paths-title">قدم بعدی تو از کجاست؟</h2>
                </div>
                <p>برای پیدا کردن مسیر آموزشی، شناخت مدرس‌ها یا مطالعه نکته‌های کاربردی، از اینجا شروع کن.</p>
            </div>
            <nav class="home-path-grid" aria-label="دسترسی سریع به بخش‌های شیخان">
                <a href="{{ route('courses.index') }}">
                    <span class="home-path-card__index">۰۱</span><b>مسیرهای آموزشی</b><span>دوره‌ها و درس‌هایی که می‌توانی دنبال کنی.</span><i aria-hidden="true">←</i>
                </a>
                <a href="{{ route('teachers.index') }}">
                    <span class="home-path-card__index">۰۲</span><b>آشنایی با مدرس‌ها</b><span>تخصص‌ها و معرفی مدرس‌های منتشرشده.</span><i aria-hidden="true">←</i>
                </a>
                <a href="{{ route('blog.index') }}">
                    <span class="home-path-card__index">۰۳</span><b>مجله یادگیری</b><span>نکته‌ها و مطلب‌هایی برای یادگیری بهتر.</span><i aria-hidden="true">←</i>
                </a>
                <a href="{{ route('store.index') }}">
                    <span class="home-path-card__index">۰۴</span><b>فروشگاه آموزشی</b><span>منابع و محصولاتی که در دسترس قرار گرفته‌اند.</span><i aria-hidden="true">←</i>
                </a>
            </nav>
        </div>
    </section>

    {{-- Courses: keep the discovery section visible even before the first course is published. --}}
    <section id="featured-courses" class="home-section home-soft">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
                    <article class="home-course-card edu-card group flex h-full min-w-0 flex-col">
    <a href="{{ $course['href'] }}" class="block" aria-label="مشاهده دوره {{ $course['title'] }}">
        <div class="relative aspect-[16/9] overflow-hidden bg-[var(--color-slate-100)]">
            @if($course['image'])
                <img src="{{ $course['image'] }}" alt="{{ $course['title'] }}" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-[1.045]" loading="lazy" decoding="async">
                <span class="absolute inset-0 bg-gradient-to-t from-black/25 via-transparent to-transparent"></span>
            @else
                <div class="flex h-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(104,121,245,.20),transparent_38%),linear-gradient(135deg,#eef2ff,#f7fbff)] text-[var(--color-primary-600)]">
                    <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 5h16v14H4z"/><path d="m4 15 4-4 3 3 3-4 6 6"/></svg>
                </div>
            @endif
            <div class="absolute inset-x-4 top-4 flex items-start justify-between gap-3">
                @if($course['category'])<span class="home-card-badge"><span class="home-badge-dot"></span>{{ $course['category'] }}</span>@endif
                @if($course['price'] === 'رایگان')<span class="home-card-badge is-free">پیش‌نمایش رایگان</span>@endif
            </div>
        </div>
    </a>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3">
            <div class="flex min-w-0 flex-wrap gap-1.5">
                @foreach(array_slice(array_filter($course['grades'] ?? []), 0, 3) as $grade)
                    <span class="rounded-full bg-[var(--color-background-soft)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-text-tertiary)]">{{ $grade }}</span>
                @endforeach
                @if(empty($course['grades']) && $course['level'])
                    <span class="rounded-full bg-[var(--color-background-soft)] px-2.5 py-1 text-[11px] font-bold text-[var(--color-text-tertiary)]">سطح {{ $course['level'] }}</span>
                @endif
            </div>
            @if($course['price'] !== null)<span class="ms-auto shrink-0 text-sm font-black text-[var(--color-primary-600)]">{{ $course['price'] }}</span>@endif
        </div>
        <h3 class="mt-4 text-xl font-black leading-snug text-[var(--color-text)]"><a href="{{ $course['href'] }}" class="transition-colors hover:text-[var(--color-primary-600)]">{{ $course['title'] }}</a></h3>
        @if($course['description'])<p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-secondary)]">{{ $course['description'] }}</p>@endif
        @if($course['teacher'])
            <div class="mt-4 flex min-w-0 items-center gap-2 text-xs font-semibold text-[var(--color-text-muted)]">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[var(--color-primary-50)] text-[var(--color-primary-600)]" aria-hidden="true">مد</span>
                <span class="truncate">{{ $course['teacher'] }}</span>
            </div>
        @endif
        <div class="mt-auto pt-5"><div class="flex flex-wrap items-center gap-3 border-t border-[var(--color-border)] pt-4 text-xs font-semibold text-[var(--color-text-muted)]">
            @if($course['lessons'])<span class="home-meta-signal"><i aria-hidden="true">◫</i>{{ $course['lessons'] }} درس</span>@endif
            @if($course['duration'])<span class="home-meta-signal"><i aria-hidden="true">◷</i>{{ $course['duration'] }}</span>@endif
            <a href="{{ $course['href'] }}" class="ms-auto font-black text-[var(--color-primary-600)]">مشاهده دوره ←</a>
        </div></div>
    </div>
</article>
                @empty
                    <div class="home-empty-state">
                        <strong>هنوز دوره‌ای منتشر نشده است.</strong>
                        <span>به‌محض انتشار، دوره‌ها در این بخش نمایش داده می‌شوند.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </section>


    {{-- Keep the journal discovery area visible when no post has been published yet. --}}
    {{-- Latest articles --}}
    <section id="academy-journal" class="home-section home-blog">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
        </div>
    </section>


    {{-- Why Sheykhan --}}
    <section id="learning-experience" class="home-section home-dark">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
        </div>
    </section>


    @include('pages.home.partials.learning-workflow')

    {{-- Teachers: an empty directory still offers a clear next step. --}}
    <section id="teachers-section" class="home-section">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
                    <article class="home-teacher-card teacher-card edu-card group h-full min-w-0">
    <a href="{{ $teacher['href'] }}" class="teacher-card__media" aria-label="مشاهده پروفایل {{ $teacher['name'] }}">
        @if($teacher['avatar'])<img src="{{ $teacher['avatar'] }}" alt="تصویر {{ $teacher['name'] }}" class="teacher-card__photo" loading="lazy" decoding="async">
        @else<span class="teacher-card__fallback" aria-hidden="true">{{ mb_substr(trim((string) $teacher['name']),0,1) }}</span>@endif
        <span class="teacher-card__media-overlay" aria-hidden="true"></span>
        <span class="teacher-card__media-action">پروفایل مدرس <span aria-hidden="true">↗</span></span>
    </a>
    <div class="teacher-card__body">
        <div class="teacher-card__identity">
            <h3 class="teacher-card__name"><a href="{{ $teacher['href'] }}">{{ $teacher['name'] }}</a></h3>
            @if(filled($teacher['role']))<p class="teacher-card__role">{{ $teacher['role'] }}</p>@endif
        </div>
        @if(filled($teacher['bio']))<p class="teacher-card__bio">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $teacher['bio'])),155) }}</p>@endif
        <div class="teacher-card__footer">
            <span class="teacher-card__course-count"><span aria-hidden="true">▤</span>{{ \App\Support\PersianUi::digits((int) ($teacher['courses'] ?? 0)) }} دوره آموزشی</span>
            <a href="{{ $teacher['href'] }}" class="teacher-card__action">دیدن پروفایل <span aria-hidden="true">←</span></a>
        </div>
    </div>
</article>
                @empty
                    <div class="home-empty-state"><strong>هنوز مدرس عمومی ثبت نشده است.</strong></div>
                @endforelse
            </div>
        </div>
    </section>


    {{-- Live classes --}}
    <section id="upcoming-classes" class="home-section home-soft">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
                <div class="home-heading">
                    <div>
                        <span>کلاس‌های پیش‌رو</span>
                        <h2>جلسه بعدی را از دست نده.</h2>
                    </div>
                </div>
                <div class="home-live-grid">
                    @forelse($liveClassCards as $class)
                        @php
                            $liveStatusClass = match($class['status']) {
                                'در حال برگزاری' => 'is-live',
                                'به‌زودی', 'برنامه‌ریزی‌شده' => 'is-upcoming',
                                default => 'is-neutral',
                            };
                        @endphp
                        <article class="home-live-card">
                            <div class="home-live-card__top"><span class="home-live-card__mark" aria-hidden="true">◷</span><span class="home-live-status {{ $liveStatusClass }}">{{ $class['status'] }}</span></div>
                            @if($class['course'])<span class="home-live-card__course">{{ $class['course'] }}</span>@endif
                            <h3><a href="{{ $class['href'] }}">{{ $class['title'] }}</a></h3>
                            <div class="home-live-card__details"><div><span>تاریخ</span><strong>{{ $class['date'] ?: '—' }}</strong></div><div><span>ساعت</span><strong>{{ $class['time'] ?: '—' }}</strong></div></div>
                            @if($class['teacher'])<p class="home-live-card__teacher">مدرس: {{ $class['teacher'] }}</p>@endif
                            <a class="home-live-card__action" href="{{ $class['href'] }}">مشاهده کلاس <span aria-hidden="true">←</span></a>
                        </article>
                    @empty
                        <div class="home-empty-state"><strong>در حال حاضر کلاس آینده‌ای برای نمایش منتشر نشده است.</strong><span>برنامه کلاس‌ها پس از ثبت و انتشار توسط آموزشگاه در این بخش قرار می‌گیرد.</span><a href="{{ route('courses.index') }}">مشاهده دوره‌ها ←</a></div>
                    @endforelse
                </div>
            </div>
        </section>

    {{-- Educational store --}}
    <section id="educational-store" class="home-section">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
        </div>
    </section>

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
            ->map(function ($title, $slug) use ($academyContentGroups) {
                return [
                    'slug' => $slug,
                    'title' => $title,
                    'items' => $academyContentGroups[$slug] ?? [],
                ];
            })
            ->filter(function ($path) {
                return count($path['items']) > 0;
            })
            ->values();
    @endphp
    @if($publicAcademyPaths->isNotEmpty())
        <section id="academy-content" class="home-section home-soft">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
            </div>
        </section>
    @endif

    {{-- Achievers --}}
    @if(count($achievementCards))
    <section id="academy-achievements" class="home-section home-trust">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
                            <img src="{{ $achievement['image'] }}" alt="{{ $achievement['name'] }}" loading="lazy" decoding="async">
                        @else
                            <div class="home-achievement-placeholder" aria-hidden="true">ش</div>
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
        </div>
    </section>
    @endif

    {{-- Testimonials --}}
    @if(count($testimonialCards))
    <section id="family-stories" class="home-section home-dark">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
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
        </div>
    </section>
    @endif

    {{-- CTA --}}
    <section id="start-learning" class="home-section home-start-learning">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
            <div class="home-cta">
                <div>
                    <span>قدم بعدی</span>
                    <h2>مسیر یادگیریت را از همین امروز شروع کن.</h2>
                    <p>دوره‌ها را ببین و اولین قدم را ساده بردار.</p>
                </div>
                <div class="home-actions">
                    <a href="{{ route('courses.index') }}" class="home-final-action home-final-action--primary">مشاهده دوره‌ها</a>
                    <a href="{{ route('teachers.index') }}" class="home-final-action home-final-action--secondary">اساتید</a>
                </div>
            </div>
        </div>
    </section>
@endsection
