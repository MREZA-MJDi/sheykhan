@extends('layouts.app')

@section('title', 'مقالات | شیخان')
@section('description', 'مقالات آموزشی منتشرشده در شیخان.')

@section('content')
    <section class="public-blog-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">

                {{-- Header --}}
                <header class="public-page-intro public-page-intro--editorial mb-10 sm:mb-12">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-700)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
                            مرکز محتوای شیخان
                        </span>

                        <h1 class="mt-4 text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            مقالات شیخان
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            مطالب آموزشی و کاربردی برای ساختن عادت‌های بهتر، یادگیری مؤثرتر و داشتن مسیر روشن‌تر.
                        </p>
                    </div>

                    @if($posts->total())
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span class="inline-flex items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 text-sm font-semibold text-[var(--color-text-muted)]">
                                <strong class="font-black text-[var(--color-text)]">
                                    {{ \App\Support\PersianUi::digits($posts->total()) }}
                                </strong>
                                مقاله منتشرشده
                            </span>

                            @if($posts->hasPages())
                                <span class="text-sm text-[var(--color-text-subtle)]">
                                    صفحه
                                    {{ \App\Support\PersianUi::digits($posts->currentPage()) }}
                                    از
                                    {{ \App\Support\PersianUi::digits($posts->lastPage()) }}
                                </span>
                            @endif
                        </div>
                    @endif
                </header>

                {{-- Posts --}}
                <section aria-labelledby="blog-posts-title">
                    <div class="mb-6 flex items-end justify-between gap-4">
                        <div>
                            <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                آخرین مطالب
                            </span>

                            <h2
                                id="blog-posts-title"
                                class="mt-1 text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl"
                            >
                                تازه‌ترین مقالات
                            </h2>
                        </div>

                        @if($posts->total())
                            <span class="hidden text-sm font-semibold text-[var(--color-text-muted)] sm:block">
                                {{ \App\Support\PersianUi::digits($posts->total()) }}
                                مطلب
                            </span>
                        @endif
                    </div>

                    @if($posts->count())
                        <div class="public-blog-grid grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($posts as $post)
                                @php
                                    $postImage = $post->media?->first()?->url();
                                    $postCategory = $post->category?->name;
                                    $postDate = $post->published_at
                                        ? \App\Support\PersianUi::date($post->published_at)
                                        : null;
                                    $postExcerpt = $post->excerpt ?: \Illuminate\Support\Str::limit(
                                        strip_tags((string) $post->content),
                                        140
                                    );
                                @endphp

                                <article
                                    class="public-blog-card {{ $loop->first && $posts->count() > 1 ? 'public-blog-card--featured' : '' }} group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_8px_30px_rgba(15,23,42,.04)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[0_18px_45px_rgba(15,23,42,.10)]"
                                >
                                    {{-- Image --}}
                                    <a
                                        href="{{ route('blog.show', $post->slug) }}"
                                        class="public-blog-card__media relative block aspect-[16/10] overflow-hidden bg-[var(--color-slate-100)]"
                                        aria-label="مطالعه {{ $post->title }}"
                                    >
                                        @if($postImage)
                                            <img
                                                src="{{ $postImage }}"
                                                alt="{{ $post->title }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(104,121,245,.18),transparent_42%),var(--color-slate-100)] text-[var(--color-primary-600)]">
                                                <svg
                                                    class="h-10 w-10"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.7"
                                                    aria-hidden="true"
                                                >
                                                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                                                    <path stroke-linecap="round" d="m7 15 3-3 2.5 2.5L15 12l2 3"/>
                                                    <circle cx="9" cy="9" r="1"/>
                                                </svg>
                                            </div>
                                        @endif

                                        @if($postCategory)
                                            <span class="absolute right-4 top-4 rounded-full border border-white/20 bg-black/55 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                                                {{ $postCategory }}
                                            </span>
                                        @endif
                                    </a>

                                    {{-- Body --}}
                                    <div class="public-blog-card__body flex flex-1 flex-col p-5 sm:p-6">
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[var(--color-text-muted)]">
                                            @if($postCategory)
                                                <span class="font-bold text-[var(--color-primary-600)]">
                                                    {{ $postCategory }}
                                                </span>
                                            @endif

                                            @if($postCategory && $postDate)
                                                <span aria-hidden="true">•</span>
                                            @endif

                                            @if($postDate)
                                                <time datetime="{{ $post->published_at?->toDateString() }}">
                                                    {{ \App\Support\PersianUi::digits($postDate) }}
                                                </time>
                                            @endif
                                        </div>

                                        <h2 class="mt-4 line-clamp-2 text-lg font-black leading-8 text-[var(--color-text)] sm:text-xl">
                                            <a
                                                href="{{ route('blog.show', $post->slug) }}"
                                                class="transition-colors hover:text-[var(--color-primary-600)]"
                                            >
                                                {{ $post->title }}
                                            </a>
                                        </h2>

                                        @if($postExcerpt)
                                            <p class="mt-3 line-clamp-3 text-sm leading-7 text-[var(--color-text-secondary)]">
                                                {{ $postExcerpt }}
                                            </p>
                                        @endif

                                        <div class="mt-auto flex items-center justify-between gap-4 border-t border-[var(--color-border)] pt-5">
                                            <span class="text-xs font-semibold text-[var(--color-text-muted)]">
                                                مطالعه مقاله
                                            </span>

                                            <a
                                                href="{{ route('blog.show', $post->slug) }}"
                                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)] transition duration-200 group-hover:-translate-x-1 group-hover:bg-[var(--color-primary-100)]"
                                                aria-label="مطالعه {{ $post->title }}"
                                            >
                                                <span aria-hidden="true">←</span>
                                            </a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-[1.5rem] border border-dashed border-[var(--color-border)] bg-[var(--color-slate-50)] px-6 py-14 text-center sm:px-10">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]">
                                <svg
                                    class="h-8 w-8"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 4h12v16H6z"
                                    />
                                    <path stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/>
                                </svg>
                            </div>

                            <h2 class="mt-5 text-xl font-black text-[var(--color-text)]">
                                هنوز مقاله‌ای منتشر نشده
                            </h2>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--color-text-secondary)]">
                                مقالات جدید شیخان بعد از انتشار در این بخش قرار می‌گیرند.
                            </p>
                        </div>
                    @endif
                </section>

                {{-- Pagination --}}
                @if($posts->hasPages())
                    <div class="mt-10 border-t border-[var(--color-border)] pt-8 sm:mt-12">
                        <x-navigation.pagination :paginator="$posts" />
                    </div>
                @endif

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
