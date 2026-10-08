@extends('layouts.app')

@section('title', ($seoMeta?->title ?: $content->title) . ' | ' . $academy->name)
@section('description', $seoMeta?->description ?: ($content->excerpt ?: 'محتوای آموزشی ' . $academy->name))

@section('content')
@php($cover = $content->media->first()?->url())
<article class="public-academy-content-page">
    <x-layout.section spacing="lg">
        <x-layout.container size="wide">
            <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-[var(--color-text-muted)]">
                <a href="{{ route('home') }}" class="hover:text-[var(--color-primary-600)]">خانه</a>
                <span>/</span>
                <span>{{ $academy->name }}</span>
                <span>/</span>
                <span class="font-bold text-[var(--color-text)]">{{ $content->title }}</span>
            </nav>

            <header class="mx-auto mt-8 max-w-4xl">
                @if($content->category)
                    <span class="inline-flex rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3 py-1.5 text-xs font-black text-[var(--color-primary-700)]">
                        {{ $content->category->title }}
                    </span>
                @endif
                <p class="mt-4 text-xs font-black text-[var(--color-primary-600)]">{{ $academy->name }}</p>
                <h1 class="mt-2 text-3xl font-black leading-[1.45] text-[var(--color-text)] sm:text-5xl">{{ $content->title }}</h1>
                @if($content->excerpt)
                    <p class="mt-5 text-base leading-9 text-[var(--color-text-secondary)] sm:text-lg">{{ $content->excerpt }}</p>
                @endif
            </header>

            @if($cover)
                <figure class="mx-auto mt-9 max-w-5xl overflow-hidden rounded-[1.75rem] border border-[var(--color-border)] bg-[var(--color-slate-100)] shadow-[0_18px_55px_rgba(15,23,42,.08)]">
                    <img src="{{ $cover }}" alt="{{ $content->title }}" class="aspect-[16/8] w-full object-cover" fetchpriority="high">
                </figure>
            @endif

            <div class="mx-auto mt-9 max-w-4xl overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_8px_30px_rgba(15,23,42,.04)]">
                <div class="px-5 py-7 sm:px-8 sm:py-10 lg:px-10">
                    @if($content->body)
                        <div class="text-[15px] leading-[2.25] text-[var(--color-text-secondary)] sm:text-base">
                            {!! nl2br(e($content->body)) !!}
                        </div>
                    @else
                        <div class="rounded-2xl bg-[var(--color-slate-50)] p-5 text-sm text-[var(--color-text-muted)]">
                            این محتوا هنوز متن نهایی ندارد.
                        </div>
                    @endif
                </div>
            </div>
        </x-layout.container>
    </x-layout.section>
</article>
@endsection
