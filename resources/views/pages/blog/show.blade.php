@extends('layouts.app')

@section('title', $post->title . ' | شیخان')
@section('description', $post->excerpt ?: 'مقاله ' . $post->title . ' در شیخان.')

@section('content')
    <x-layout.section spacing="lg">
        <x-layout.container size="2xl">
            <article class="mx-auto max-w-4xl">
                <div class="flex flex-wrap items-center gap-3 text-sm text-[var(--color-text-muted)]">
                    @if($post->category)
                        <span class="font-bold text-[var(--color-primary-600)]">{{ $post->category->name }}</span>
                    @endif
                    @if($post->published_at)
                        <span>•</span>
                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('Y/m/d') }}</time>
                    @endif
                    @if($post->author)
                        <span>•</span>
                        <span>{{ $post->author->name }}</span>
                    @endif
                </div>

                <h1 class="mt-5 text-3xl font-black leading-tight sm:text-5xl">
                    {{ $post->title }}
                </h1>

                @if($post->excerpt)
                    <p class="mt-5 text-lg leading-8 text-[var(--color-text-muted)]">
                        {{ $post->excerpt }}
                    </p>
                @endif

                @if($post->media->first()?->url())
                    <img
                        src="{{ $post->media->first()->url() }}"
                        alt="{{ $post->title }}"
                        class="mt-10 aspect-[16/8] w-full rounded-3xl object-cover"
                    >
                @endif

                <div class="mt-10 rounded-3xl border border-[var(--color-border)] bg-white p-6 sm:p-10">
                    <div class="max-w-none leading-8 text-[var(--color-text-secondary)]">
                        {!! nl2br(e($post->content)) !!}
                    </div>
                </div>

                @if($post->tags->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                            <span class="rounded-full bg-[var(--color-slate-100)] px-3 py-1 text-xs font-medium text-[var(--color-text-muted)]">
                                #{{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </article>
        </x-layout.container>
    </x-layout.section>
@endsection
