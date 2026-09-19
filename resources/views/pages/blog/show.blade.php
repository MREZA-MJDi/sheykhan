@extends('layouts.app')

@section('title', $post->title . ' | شیخان')
@section('description', $post->excerpt ?: 'مقاله ' . $post->title . ' در شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <article class="mx-auto max-w-4xl">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-[var(--color-primary-600)]">
                    <span aria-hidden="true">→</span>
                    بازگشت به مقالات
                </a>

                <div class="mt-8 flex flex-wrap items-center gap-3 text-sm text-[var(--color-text-muted)]">
                    @if($post->category)
                        <span class="rounded-full bg-[var(--color-primary-50)] px-3 py-1.5 font-bold text-[var(--color-primary-700)]">
                            {{ $post->category->name }}
                        </span>
                    @endif

                    @if($post->published_at)
                        <time datetime="{{ $post->published_at->toDateString() }}">
                            {{ $post->published_at->format('Y/m/d') }}
                        </time>
                    @endif

                    @if($post->author)
                        <span>•</span>
                        <span>{{ $post->author->name }}</span>
                    @endif
                </div>

                <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-5xl">
                    {{ $post->title }}
                </h1>

                @if($post->excerpt)
                    <p class="mt-5 text-lg leading-9 text-[var(--color-text-muted)]">
                        {{ $post->excerpt }}
                    </p>
                @endif

                @if($post->media->first()?->url())
                    <div class="mt-10 overflow-hidden rounded-3xl border border-[var(--color-border)] bg-white shadow-sm">
                        <img
                            src="{{ $post->media->first()->url() }}"
                            alt="{{ $post->title }}"
                            class="aspect-[16/8] w-full object-cover"
                        >
                    </div>
                @endif

                <div class="mt-10 rounded-3xl border border-[var(--color-border)] bg-white p-6 shadow-sm sm:p-10">
                    <div class="max-w-none text-base leading-9 text-[var(--color-text-secondary)] sm:text-lg">
                        {!! nl2br(e($post->content)) !!}
                    </div>

                    @if($post->tags->isNotEmpty())
                        <div class="mt-10 border-t border-[var(--color-border)] pt-6">
                            <div class="flex flex-wrap gap-2">
                                @foreach($post->tags as $tag)
                                    <span class="rounded-full bg-[var(--color-slate-100)] px-3 py-1.5 text-xs font-medium text-[var(--color-text-muted)]">
                                        #{{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </article>
        </x-layout.container>
    </x-layout.section>
@endsection
