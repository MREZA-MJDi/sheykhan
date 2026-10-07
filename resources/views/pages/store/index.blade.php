@extends('layouts.app')

@section('title', 'فروشگاه آموزشی | شیخان')
@section('description', 'کتاب، جزوه و آزمون‌های آموزشی شیخان.')

@section('content')
    <section class="public-store-page">
        <x-layout.section spacing="lg">
            <x-layout.container size="wide">
                <header class="mb-10 sm:mb-12">
                    <div class="max-w-3xl">
                        <span class="ui-eyebrow mb-4">فروشگاه آموزشی شیخان</span>

                        <h1 class="text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                            منابع یادگیری شیخان
                        </h1>

                        <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                            کتاب، جزوه و آزمون‌های آموزشی را بر اساس نیازت انتخاب کن و مسیر یادگیریت را کامل‌تر کن.
                        </p>
                    </div>
                </header>

                @if(count($categories))
                    <section aria-labelledby="store-categories-title">
                        <div class="mb-5">
                            <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                دسته‌بندی منابع
                            </span>

                            <h2 id="store-categories-title" class="mt-1 text-xl font-black text-[var(--color-text)] sm:text-2xl">
                                چه چیزی نیاز داری؟
                            </h2>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($categories as $index => $category)
                                <a
                                    href="{{ url('/store?category=' . urlencode($category['slug'])) }}"
                                    class="group relative overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-sm)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[var(--shadow-md)] sm:p-6"
                                >
                                    <div class="flex items-start justify-between gap-5">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-sm font-black text-[var(--color-primary-600)]">
                                            {{ AppSupportPersianUi::digits(str_pad($index + 1, 2, '0', STR_PAD_LEFT)) }}
                                        </span>

                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--color-border)] text-[var(--color-primary-600)] transition group-hover:-translate-x-1 group-hover:border-[var(--color-primary-200)] group-hover:bg-[var(--color-primary-50)]" aria-hidden="true">
                                            ←
                                        </span>
                                    </div>

                                    <h3 class="mt-6 text-lg font-black text-[var(--color-text)]">
                                        {{ $category['name'] }}
                                    </h3>

                                    <p class="mt-2 line-clamp-2 min-h-[3.5rem] text-sm leading-7 text-[var(--color-text-secondary)]">
                                        {{ $category['description'] ?: 'منابع آموزشی منتخب شیخان' }}
                                    </p>

                                    <span class="mt-5 inline-flex text-xs font-black text-[var(--color-primary-600)]">
                                        مشاهده محصولات
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="mt-14 sm:mt-16" aria-labelledby="store-products-title">
                    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <span class="text-xs font-black tracking-[.1em] text-[var(--color-primary-600)]">
                                محصولات
                            </span>

                            <h2 id="store-products-title" class="mt-1 text-2xl font-black tracking-tight text-[var(--color-text)] sm:text-3xl">
                                منابع آموزشی
                            </h2>
                        </div>

                        @if($products->total())
                            <span class="text-sm font-semibold text-[var(--color-text-muted)]">
                                {{ AppSupportPersianUi::digits($products->total()) }} محصول
                            </span>
                        @endif
                    </div>

                    @if($products->count())
                        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach($products as $product)
                                @php
                                    $media = $product->media?->first();
                                    $price = (float) ($product->sale_price ?? $product->price);
                                @endphp

                                <x-education.product-card
                                    :title="$product->title"
                                    :price="number_format($price, 0, '.', ',') . ' تومان'"
                                    :category="$product->category?->name"
                                    :image="$media?->url()"
                                    :description="$product->description ?? null"
                                    href="#"
                                />
                            @endforeach
                        </div>
                    @else
                        <x-ui.empty-state
                            title="محصولی برای نمایش وجود ندارد"
                            description="محصولات آموزشی پس از انتشار در این بخش نمایش داده خواهند شد."
                        />
                    @endif

                    @if($products->hasPages())
                        <div class="mt-10 border-t border-[var(--color-border)] pt-8">
                            <x-navigation.pagination :paginator="$products" />
                        </div>
                    @endif
                </section>
            </x-layout.container>
        </x-layout.section>
    </section>
@endsection