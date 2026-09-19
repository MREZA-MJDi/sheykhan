@extends('layouts.app')

@section('title', 'مقالات | شیخان')
@section('description', 'مقالات آموزشی و مطالب منتشرشده در شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <x-layout.page-header
                title="مقالات شیخان"
                description="مطالب تازه آموزشی و محتوای کاربردی برای یادگیری بهتر."
            />

            <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($posts as $post)
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
                            <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                                @if($post->category)
                                    <span class="font-semibold text-[var(--color-primary-600)]">
                                        {{ $post->category->name }}
                                    </span>
                                    <span>•</span>
                                @endif
                                <time datetime="{{ $post->published_at?->toDateString() }}">
                                    {{ $post->published_at?->format('Y/m/d') }}
                                </time>
                            </div>

                            <h2 class="mt-3 text-xl font-bold">
                                <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[var(--color-primary-600)]">
                                    {{ $post->title }}
                                </a>
                            </h2>

                            <p class="mt-3 line-clamp-3 text-sm leading-7 text-[var(--color-text-muted)]">
                                {{ $post->excerpt }}
                            </p>

                            <a
                                href="{{ route('blog.show', $post->slug) }}"
                                class="mt-5 inline-flex text-sm font-bold text-[var(--color-primary-600)]"
                            >
                                مطالعه مقاله ←
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-[var(--color-border)] p-12 text-center text-[var(--color-text-muted)]">
                        هنوز مقاله منتشرشده‌ای برای نمایش وجود ندارد.
                    </div>
                @endforelse
            </div>

            <div class="mt-10">
                <x-navigation.pagination :paginator="$posts" />
            </div>
        </x-layout.container>
    </x-layout.section>
@endsection
