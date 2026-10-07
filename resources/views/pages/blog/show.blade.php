@extends('layouts.app')

@section('title', $post->title . ' | شیخان')
@section('description', $post->excerpt ?: 'مقاله ' . $post->title . ' در شیخان.')

@section('content')
    @php
        $postImage = $post->media?->first()?->url();
        $category = $post->category;
        $author = $post->author;

        $publishedDate = $post->published_at
            ? \App\Support\PersianUi::digits($post->published_at->format('Y/m/d'))
            : null;

        $readingText = null;

        if ($post->content) {
            $wordCount = str_word_count(strip_tags((string) $post->content));

            if ($wordCount > 0) {
                $readingMinutes = max(1, (int) ceil($wordCount / 180));
                $readingText = \App\Support\PersianUi::digits($readingMinutes) . ' دقیقه مطالعه';
            }
        }
    @endphp

    <section class="public-blog-post-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">

                {{-- Breadcrumb --}}
                <nav
                    class="flex flex-wrap items-center gap-2 text-sm text-[var(--color-text-muted)]"
                    aria-label="مسیر صفحه"
                >
                    <a
                        href="{{ url('/') }}"
                        class="transition-colors hover:text-[var(--color-primary-600)]"
                    >
                        خانه
                    </a>

                    <span aria-hidden="true">/</span>

                    <a
                        href="{{ route('blog.index') }}"
                        class="transition-colors hover:text-[var(--color-primary-600)]"
                    >
                        مقالات
                    </a>

                    @if($category)
                        <span aria-hidden="true">/</span>

                        <span class="font-semibold text-[var(--color-text)]">
                            {{ $category->name }}
                        </span>
                    @endif
                </nav>

                {{-- Article --}}
                <article class="mx-auto mt-8 max-w-5xl">

                    {{-- Article header --}}
                    <header>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($category)
                                <span class="inline-flex items-center rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-700)]">
                                    {{ $category->name }}
                                </span>
                            @endif

                            @if($publishedDate)
                                <time
                                    datetime="{{ $post->published_at->toDateString() }}"
                                    class="inline-flex items-center rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3.5 py-2 text-xs font-bold text-[var(--color-text-muted)]"
                                >
                                    {{ $publishedDate }}
                                </time>
                            @endif

                            @if($readingText)
                                <span class="inline-flex items-center rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3.5 py-2 text-xs font-bold text-[var(--color-text-muted)]">
                                    {{ $readingText }}
                                </span>
                            @endif
                        </div>

                        <h1 class="mt-6 max-w-4xl text-3xl font-black leading-[1.4] tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            {{ $post->title }}
                        </h1>

                        @if($post->excerpt)
                            <p class="mt-5 max-w-3xl text-base leading-8 text-[var(--color-text-secondary)] sm:text-lg sm:leading-9">
                                {{ $post->excerpt }}
                            </p>
                        @endif

                        @if($author)
                            <div class="mt-7 flex items-center gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-sm font-black text-[var(--color-primary-600)]">
                                    {{ mb_substr(trim((string) $author->name), 0, 1) }}
                                </div>

                                <div class="min-w-0">
                                    <span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">
                                        نویسنده
                                    </span>

                                    <strong class="block truncate text-sm font-black text-[var(--color-text)]">
                                        {{ $author->name }}
                                    </strong>
                                </div>
                            </div>
                        @endif
                    </header>

                    {{-- Featured image --}}
                    @if($postImage)
                        <figure class="mt-10 overflow-hidden rounded-[1.75rem] border border-[var(--color-border)] bg-[var(--color-slate-100)] shadow-[0_18px_55px_rgba(15,23,42,.08)]">
                            <img
                                src="{{ $postImage }}"
                                alt="{{ $post->title }}"
                                class="aspect-[16/8] w-full object-cover"
                                fetchpriority="high"
                            >
                        </figure>
                    @endif

                    {{-- Content layout --}}
                    <div class="mt-10 grid gap-8 lg:grid-cols-[minmax(0,1fr)_250px] lg:items-start">

                        {{-- Main content --}}
                        <div class="min-w-0">
                            <div class="overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_8px_30px_rgba(15,23,42,.04)]">
                                <div class="px-5 py-7 sm:px-8 sm:py-10 lg:px-10">
                                    <div class="max-w-none text-[15px] leading-[2.15] text-[var(--color-text-secondary)] sm:text-base sm:leading-[2.2]">
                                        {!! nl2br(e($post->content)) !!}
                                    </div>

                                    @if($post->tags->isNotEmpty())
                                        <div class="mt-10 border-t border-[var(--color-border)] pt-7">
                                            <div class="mb-3 text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                                برچسب‌ها
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                @foreach($post->tags as $tag)
                                                    <span class="rounded-full border border-[var(--color-border)] bg-[var(--color-slate-50)] px-3.5 py-2 text-xs font-bold text-[var(--color-text-muted)]">
                                                        #{{ $tag->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Back to blog --}}
                            <div class="mt-6">
                                <a
                                    href="{{ route('blog.index') }}"
                                    class="group inline-flex min-h-11 items-center gap-2 rounded-xl border border-[var(--color-border)] bg-[var(--color-surface)] px-4 py-2.5 text-sm font-black text-[var(--color-text-muted)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]"
                                >
                                    <span
                                        aria-hidden="true"
                                        class="transition-transform duration-200 group-hover:translate-x-1"
                                    >
                                        →
                                    </span>

                                    <span>بازگشت به مقالات</span>
                                </a>
                            </div>
                        </div>

                        {{-- Sidebar --}}
                        <aside class="space-y-4 lg:sticky lg:top-24">

                            {{-- Article summary --}}
                            <div class="rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[0_8px_30px_rgba(15,23,42,.04)] sm:p-6">
                                <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                    اطلاعات مقاله
                                </span>

                                <h2 class="mt-2 text-lg font-black text-[var(--color-text)]">
                                    {{ $category?->name ?: 'مقاله شیخان' }}
                                </h2>

                                <dl class="mt-5 divide-y divide-[var(--color-border)]">
                                    @if($author)
                                        <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                                            <dt class="text-[var(--color-text-muted)]">
                                                نویسنده
                                            </dt>

                                            <dd class="text-left font-bold text-[var(--color-text)]">
                                                {{ $author->name }}
                                            </dd>
                                        </div>
                                    @endif

                                    @if($publishedDate)
                                        <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                                            <dt class="text-[var(--color-text-muted)]">
                                                انتشار
                                            </dt>

                                            <dd class="font-bold text-[var(--color-text)]">
                                                {{ $publishedDate }}
                                            </dd>
                                        </div>
                                    @endif

                                    @if($readingText)
                                        <div class="flex items-center justify-between gap-4 py-3.5 text-sm">
                                            <dt class="text-[var(--color-text-muted)]">
                                                مطالعه
                                            </dt>

                                            <dd class="font-bold text-[var(--color-text)]">
                                                {{ $readingText }}
                                            </dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            {{-- Back card --}}
                            <a
                                href="{{ route('blog.index') }}"
                                class="group block rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-slate-50)] p-5 transition hover:-translate-y-0.5 hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] sm:p-6"
                            >
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <span class="block text-xs font-bold text-[var(--color-text-muted)]">
                                            مرکز محتوا
                                        </span>

                                        <strong class="mt-1 block text-sm font-black text-[var(--color-text)]">
                                            مطالعه مقالات بیشتر
                                        </strong>
                                    </div>

                                    <span
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-surface)] text-[var(--color-primary-600)] shadow-sm transition group-hover:-translate-x-1"
                                        aria-hidden="true"
                                    >
                                        ←
                                    </span>
                                </div>
                            </a>

                        </aside>
                    </div>
                </article>

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
