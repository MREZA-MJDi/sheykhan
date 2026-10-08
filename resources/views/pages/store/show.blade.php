@extends('layouts.app')

@section('title', $product->title . ' | فروشگاه شیخان')
@section('description', $product->subtitle ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 155))

@section('content')
    @php
        $media = $product->media;
        $cover = $media->first()?->url();
        $price = $product->sale_price ?? $product->price;
    @endphp

    <section class="public-product-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <nav aria-label="مسیر صفحه" class="flex flex-wrap items-center gap-2 text-xs text-[var(--color-text-muted)]">
                    <a href="{{ route('home') }}" class="transition hover:text-[var(--color-primary-600)]">خانه</a>
                    <span>/</span>
                    <a href="{{ route('store.index') }}" class="transition hover:text-[var(--color-primary-600)]">فروشگاه</a>
                    @if($product->category)
                        <span>/</span>
                        <a href="{{ route('store.index', ['category' => $product->category->slug]) }}" class="transition hover:text-[var(--color-primary-600)]">
                            {{ $product->category->name }}
                        </a>
                    @endif
                    <span>/</span>
                    <span class="font-bold text-[var(--color-text)]">{{ $product->title }}</span>
                </nav>

                <article class="mx-auto mt-8 grid max-w-6xl gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(320px,.92fr)] lg:items-start">
                    <div class="min-w-0 overflow-hidden rounded-[2rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_22px_70px_rgba(15,23,42,.08)]">
                        <div class="aspect-[16/10] overflow-hidden bg-[var(--color-slate-100)]">
                            @if($cover)
                                <img src="{{ $cover }}" alt="{{ $product->title }}" class="h-full w-full object-cover" fetchpriority="high">
                            @else
                                <div class="grid h-full place-items-center bg-[radial-gradient(circle_at_22%_18%,rgba(86,103,232,.18),transparent_35%),linear-gradient(135deg,#f2f5ff,#f7faf9)]">
                                    <span class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white text-2xl font-black text-[var(--color-primary-600)] shadow-[var(--shadow-sm)]">ش</span>
                                </div>
                            @endif
                        </div>

                        @if($media->count() > 1)
                            <div class="grid grid-cols-4 gap-2 border-t border-[var(--color-border)] bg-[var(--color-background-soft)] p-3">
                                @foreach($media->take(4) as $item)
                                    <img src="{{ $item->url() }}" alt="" class="aspect-square w-full rounded-xl object-cover" loading="lazy">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($product->category)
                                <span class="inline-flex rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3 py-1.5 text-xs font-black text-[var(--color-primary-700)]">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                            <span class="inline-flex rounded-full border border-[var(--color-border)] bg-[var(--color-surface)] px-3 py-1.5 text-xs font-bold text-[var(--color-text-muted)]">
                                {{ $product->product_type }}
                            </span>
                        </div>

                        <h1 class="mt-5 text-3xl font-black leading-[1.45] tracking-tight text-[var(--color-text)] sm:text-4xl">
                            {{ $product->title }}
                        </h1>

                        @if($product->subtitle)
                            <p class="mt-4 text-base leading-8 text-[var(--color-text-secondary)]">{{ $product->subtitle }}</p>
                        @endif

                        <div class="mt-7 rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-background-soft)] p-5 sm:p-6">
                            <span class="text-xs font-bold text-[var(--color-text-muted)]">قیمت</span>
                            <strong class="mt-1 block text-2xl font-black text-[var(--color-primary-600)]">
                                {{ AppSupportPersianUi::money($price) }}
                            </strong>

                            <div class="mt-4 flex flex-wrap gap-2 text-xs font-bold text-[var(--color-text-muted)]">
                                <span class="rounded-full bg-white px-3 py-1.5">{{ $product->delivery_type === 'download' ? 'تحویل دیجیتال' : 'تحویل توسط شیخان' }}</span>
                                <span class="rounded-full bg-white px-3 py-1.5">انتشار رسمی شیخان</span>
                            </div>
                        </div>

                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('store.index', ['category' => $product->category?->slug]) }}" class="inline-flex min-h-12 flex-1 items-center justify-center rounded-2xl bg-[var(--color-primary-600)] px-5 text-sm font-black text-white shadow-[var(--shadow-sm)] transition hover:-translate-y-0.5 hover:bg-[var(--color-primary-700)]">
                                مشاهده منابع مشابه
                            </a>
                            <a href="{{ route('store.index') }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] px-5 text-sm font-black text-[var(--color-text-muted)] transition hover:border-[var(--color-primary-200)] hover:bg-[var(--color-primary-50)] hover:text-[var(--color-primary-700)]">
                                بازگشت به فروشگاه
                            </a>
                        </div>

                        @if($product->description)
                            <div class="mt-8 rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-sm)] sm:p-7">
                                <span class="ui-eyebrow">درباره محصول</span>
                                <div class="mt-4 whitespace-pre-line text-[15px] leading-[2.2] text-[var(--color-text-secondary)]">
                                    {{ $product->description }}
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
