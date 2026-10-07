@extends('layouts.app')

@section('title', 'فروشگاه آموزشی | شیخان')
@section('description', 'کتاب، جزوه و آزمون‌های آموزشی شیخان.')

@section('content')
    <section class="public-store-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">

                {{-- Header --}}
                <header class="mb-10 sm:mb-12">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-3.5 py-2 text-xs font-black text-[var(--color-primary-700)]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-primary-600)]"></span>
                            فروشگاه آموزشی شیخان
                        </span>

                        <h1 class="mt-4 text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            منابع یادگیری شیخان
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            کتاب، جزوه و آزمون‌های آموزشی را بر اساس نیازت انتخاب کن و مسیر یادگیریت را کامل‌تر کن.
                        </p>
                    </div>
                </header>

                {{-- Categories --}}
                @if(count($categories))
                    <section aria-labelledby="store-categories-title">
                        <div class="mb-5 flex items-end justify-between gap-4">
                            <div>
                                <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                    دسته‌بندی منابع
                                </span>

                                <h2
                                    id="store-categories-title"
                                    class="mt-1 text-xl font-black text-[var(--color-text)] sm:text-2xl"
                                >
                                    چه چیزی نیاز داری؟
                                </h2>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($categories as $index => $category)
                                @php
                                    $categoryUrl = url('/store?category=' . urlencode($category['slug']));
                                    $categoryName = $category['name'] ?? 'دسته آموزشی';
                                    $categoryDescription = $category['description'] ?: 'منابع آموزشی منتخب شیخان';
                                @endphp

                                <a
                                    href="{{ $categoryUrl }}"
                                    class="group relative overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[0_8px_30px_rgba(15,23,42,.04)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[0_18px_45px_rgba(15,23,42,.10)] sm:p-6"
                                >
                                    <div class="flex items-start justify-between gap-5">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-sm font-black text-[var(--color-primary-600)]">
                                            {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                        </span>

                                        <span
                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--color-border)] text-[var(--color-primary-600)] transition duration-300 group-hover:-translate-x-1 group-hover:border-[var(--color-primary-200)] group-hover:bg-[var(--color-primary-50)]"
                                            aria-hidden="true"
                                        >
                                            ←
                                        </span>
                                    </div>

                                    <h3 class="mt-6 text-lg font-black text-[var(--color-text)]">
                                        {{ $categoryName }}
                                    </h3>

                                    <p class="mt-2 line-clamp-2 min-h-[3.5rem] text-sm leading-7 text-[var(--color-text-secondary)]">
                                        {{ $categoryDescription }}
                                    </p>

                                    <span class="mt-5 inline-flex text-xs font-black text-[var(--color-primary-600)]">
                                        مشاهده محصولات
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Products --}}
                <section
                    class="mt-14 sm:mt-16"
                    aria-labelledby="store-products-title"
                >
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                محصولات
                            </span>

                            <h2
                                id="store-products-title"
                                class="mt-1 text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl"
                            >
                                منابع آموزشی
                            </h2>
                        </div>

                        @if($products->total())
                            <span class="text-sm font-semibold text-[var(--color-text-muted)]">
                                {{ number_format($products->total()) }} محصول
                            </span>
                        @endif
                    </div>

                    @if($products->count())
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($products as $product)
                                @php
                                    $productMedia = $product->media?->first();
                                    $productImage = $productMedia?->url();
                                    $productPrice = (float) ($product->sale_price ?? $product->price);
                                    $categoryName = $product->category?->name;
                                @endphp

                                <article
                                    class="group flex h-full min-w-0 flex-col overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[0_8px_30px_rgba(15,23,42,.04)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[0_18px_45px_rgba(15,23,42,.10)]"
                                >
                                    {{-- Product image --}}
                                    <div class="relative aspect-[4/3] overflow-hidden bg-[var(--color-slate-100)]">
                                        @if($productImage)
                                            <img
                                                src="{{ $productImage }}"
                                                alt="{{ $product->title }}"
                                                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="flex h-full w-full items-center justify-center">
                                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-surface)] text-[var(--color-primary-600)] shadow-sm">
                                                    <svg
                                                        class="h-7 w-7"
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        aria-hidden="true"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V4H6.5A2.5 2.5 0 0 0 4 6.5v13Z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                        @endif

                                        @if($categoryName)
                                            <span class="absolute right-3 top-3 rounded-full border border-white/20 bg-black/55 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                                                {{ $categoryName }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Product body --}}
                                    <div class="flex flex-1 flex-col p-5">
                                        <h3 class="line-clamp-2 min-h-[3.5rem] text-base font-black leading-7 text-[var(--color-text)]">
                                            {{ $product->title }}
                                        </h3>

                                        @if($product->description ?? null)
                                            <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                                                {{ $product->description }}
                                            </p>
                                        @endif

                                        <div class="mt-auto flex items-end justify-between gap-4 border-t border-[var(--color-border)] pt-5">
                                            <div class="min-w-0">
                                                <span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">
                                                    قیمت
                                                </span>

                                                <strong class="mt-1 block truncate text-base font-black text-[var(--color-text)]">
                                                    {{ number_format($productPrice, 0, '.', ',') }}
                                                    <span class="text-xs font-bold text-[var(--color-text-muted)]">
                                                        تومان
                                                    </span>
                                                </strong>
                                            </div>

                                            <span
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)] transition duration-300 group-hover:-translate-x-1"
                                                aria-hidden="true"
                                            >
                                                ←
                                            </span>
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
                                    stroke-width="1.8"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m20 12-7.5 7.5L4 11V4h7l9 8Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M8 8h.01"
                                    />
                                </svg>
                            </div>

                            <h2 class="mt-5 text-xl font-black text-[var(--color-text)]">
                                محصولی برای نمایش وجود ندارد
                            </h2>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-7 text-[var(--color-text-secondary)]">
                                محصولات آموزشی پس از انتشار در این بخش نمایش داده خواهند شد.
                            </p>
                        </div>
                    @endif

                    {{-- Pagination --}}
                    @if($products->hasPages())
                        <div class="mt-10 border-t border-[var(--color-border)] pt-8">
                            {{ $products->links() }}
                        </div>
                    @endif
                </section>

            </x-layout.container>
        </x-layout.section>
    </section>
@endsection
