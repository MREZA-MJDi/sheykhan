@extends('layouts.app')

@section('title', 'آکادمی شیخان | مسیر یادگیری هوشمند')
@section('description', 'کلاس‌های هدفمند، مدرس‌های همراه و مسیر آموزشی روشن؛ همه‌چیز برای قدم بعدی یادگیری آماده است.')

@section('content')
    <div class="sh-home-page">
        <section class="sh-home-hero" aria-labelledby="sh-home-title">
            <div class="sh-home-hero__copy">
                <span class="sh-eyebrow"><i></i> آکادمی شیخان · مسیر یادگیری هوشمند</span>
                <h1 id="sh-home-title">یادگیری را به <em>پیشرفت</em> تبدیل کن.</h1>
                <p>کلاس‌های هدفمند، مدرس‌های همراه و مسیر آموزشی روشن؛ همه‌چیز برای قدم بعدی تو آماده است.</p>
                <div class="sh-home-hero__actions">
                    <a class="sh-button sh-button--primary" href="{{ route('courses.index') }}">شروع یادگیری <span>←</span></a>
                    <a class="sh-button sh-button--quiet" href="{{ route('login') }}">ورود به حساب</a>
                </div>
                <div class="sh-home-hero__proof">
                    <span class="sh-proof-mark">ش</span>
                    <span><strong>یادگیری با برنامه</strong><small>از اولین جلسه تا نتیجه‌ای که می‌سازی</small></span>
                </div>
            </div>
            <div class="sh-home-hero__visual" aria-label="تصویرسازی مسیر پیشرفت آموزشی">
                <div class="sh-orbit sh-orbit--one"></div>
                <div class="sh-orbit sh-orbit--two"></div>
                <div class="sh-hero-note sh-hero-note--top"><span>✦</span><div><strong>قدم‌به‌قدم</strong><small>تا رسیدن به هدف</small></div></div>
                <div class="sh-hero-core"><span class="sh-core-star">✳</span><strong>یاد بگیر</strong><small>تمرین کن · رشد کن</small><div class="sh-core-bars"><i></i><i></i><i></i><i></i><i></i></div></div>
                <div class="sh-hero-note sh-hero-note--bottom"><span>✓</span><div><strong>پیشرفت واقعی</strong><small>با پیگیری مسیر یادگیری</small></div></div>
                <span class="sh-hero-spark sh-hero-spark--a">✳</span><span class="sh-hero-spark sh-hero-spark--b">✦</span>
            </div>
        </section>

        @include('pages.home.partials.free-challenge')

        <section class="sh-home-intro" aria-label="ویژگی‌های آموزش در شیخان">
            <div><span>01</span><strong>مسیر مشخص</strong><small>بدانی امروز چه بخوانی و قدم بعدی چیست.</small></div>
            <div><span>02</span><strong>همراهی مدرس</strong><small>در طول یادگیری تنها نمی‌مانی.</small></div>
            <div><span>03</span><strong>دیدن پیشرفت</strong><small>نتیجه تمرین و تلاش خودت را دنبال کن.</small></div>
        </section>

        <section class="sh-home-section" id="courses" aria-labelledby="sh-courses-title">
            <div class="sh-section-head">
                <div>
                    <span class="sh-eyebrow">برای شروع انتخاب کن</span>
                    <h2 id="sh-courses-title">دوره‌هایی برای قدم بعدی تو</h2>
                    <p>دوره‌های منتشرشده آموزشگاه را ببین و مسیر مناسب خودت را پیدا کن.</p>
                </div>
                <a class="sh-text-link" href="{{ route('courses.index') }}">همه دوره‌ها <span>←</span></a>
            </div>
            <div class="sh-course-grid">
                @forelse($courseCards as $course)
                    <article class="sh-course-card">
                        <div class="sh-course-card__art sh-course-card__art--{{ $loop->iteration % 3 }}{{ filled($course['image'] ?? null) ? ' has-image' : '' }}">
                            @if(filled($course['image'] ?? null))
                                <img class="sh-course-card__art-image" src="{{ $course['image'] }}" alt="{{ $course['title'] }}" loading="lazy">
                            @else
                                <b aria-hidden="true">✳</b>
                            @endif
                            <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <small>{{ $course['category'] ?: 'آموزش تخصصی' }}</small>
                        </div>
                        <div class="sh-course-card__body">
                            <div class="sh-course-meta">
                                <span>{{ !empty($course['grades']) ? implode('، ', $course['grades']) : 'دوره آموزشی' }}</span>
                                <span>{{ $course['price'] }}</span>
                            </div>
                            <h3>{{ $course['title'] }}</h3>
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags((string) ($course['description'] ?? '')), 105) }}</p>
                            <div class="sh-course-foot">
                                <span>{{ $course['teacher'] ?: 'تیم آموزشی شیخان' }} · {{ $course['lessons'] }} درس</span>
                                <a href="{{ $course['href'] }}">جزئیات دوره <b>←</b></a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="sh-empty-state">
                        <strong>دوره‌های جدید در راه‌اند.</strong>
                        <span>برای اطلاع از شروع ثبت‌نام، دوره‌های منتشرشده را دنبال کن.</span>
                        <a href="{{ route('courses.index') }}">مشاهده دوره‌ها</a>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="sh-home-journey">
            <div class="sh-journey-shape" aria-hidden="true"><span>ش</span></div>
            <div>
                <span class="sh-eyebrow">از همین‌جا شروع می‌شود</span>
                <h2>قرار نیست فقط درس بخوانی؛ قرار است جلو بروی.</h2>
                <p>با ورود به فضای شخصی خودت، کلاس‌ها، تکالیف، منابع و نتیجه آزمون‌ها را در یک مسیر منظم دنبال می‌کنی.</p>
            </div>
            <a class="sh-button sh-button--light" href="{{ auth()->check() ? route('dashboard') : route('login') }}">ورود به فضای یادگیری <span>←</span></a>
        </section>

        @if($latestPosts->isNotEmpty())
            <section class="sh-home-section sh-home-articles" id="articles" aria-labelledby="sh-articles-title">
                <div class="sh-section-head">
                    <div>
                        <span class="sh-eyebrow">دانش را ادامه بده</span>
                        <h2 id="sh-articles-title">از مجله شیخان بخوان</h2>
                        <p>نکته‌ها و مطالبی که به مسیر یادگیری کمک می‌کنند.</p>
                    </div>
                    <a class="sh-text-link" href="{{ route('blog.index') }}">همه مقاله‌ها <span>←</span></a>
                </div>
                <div class="sh-article-grid">
                    @foreach($latestPosts as $post)
                        <article class="sh-article-card">
                            @if($post->media->isNotEmpty())
                                <a class="sh-article-card__image" href="{{ route('blog.show', $post->slug) }}">
                                    <img src="{{ $post->media->first()->url() }}" alt="{{ $post->title }}" loading="lazy">
                                </a>
                            @else
                                <a class="sh-article-card__image sh-article-card__image--empty" href="{{ route('blog.show', $post->slug) }}"><span>✳</span></a>
                            @endif
                            <div>
                                <time datetime="{{ $post->published_at?->toAtomString() }}">{{ \App\Support\PersianUi::date($post->published_at) }}</time>
                                <h3><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></h3>
                                <p>{{ $post->excerpt }}</p>
                                <a class="sh-text-link" href="{{ route('blog.show', $post->slug) }}">ادامه مطلب ←</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
