@extends('layouts.app')

@section('title', 'فروشگاه آموزشی | شیخان')
@section('description', 'کتاب، جزوه و آزمون‌های آموزشی شیخان.')

@section('content')
    <section class="public-store-page">
        <section class="relative py-16 sm:py-20 lg:py-28">
            <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 max-w-[1600px]">
                <header class="public-page-intro mb-10 sm:mb-12">
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

                @if(filled($activeCategory ?? null))
                    <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[var(--color-primary-200)] bg-[var(--color-primary-50)] px-4 py-3 sm:px-5" role="status" aria-live="polite">
                        <p class="text-sm font-semibold text-[var(--color-text)]">
                            فیلتر فعال:
                            <strong class="font-black text-[var(--color-primary-700)]">{{ data_get(collect($categories ?? [])->firstWhere('slug', $activeCategory), 'name', $activeCategory) }}</strong>
                        </p>
                        <a href="{{ route('store.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--color-primary-200)] bg-white px-4 py-2 text-xs font-black text-[var(--color-primary-700)] transition hover:bg-[var(--color-primary-100)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-primary-600)]">
                            پاک کردن فیلتر
                        </a>
                    </div>
                @endif

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
                                    href="{{ route('store.index', ['category' => $category['slug']]) }}"
                                    class="group relative overflow-hidden rounded-[1.5rem] border border-[var(--color-border)] bg-[var(--color-surface)] p-5 shadow-[var(--shadow-sm)] transition duration-300 hover:-translate-y-1 hover:border-[var(--color-primary-200)] hover:shadow-[var(--shadow-md)] sm:p-6"
                                >
                                    <div class="flex items-start justify-between gap-5">
                                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-sm font-black text-[var(--color-primary-600)]">
                                            {{ \App\Support\PersianUi::digits(str_pad($index + 1, 2, '0', STR_PAD_LEFT)) }}
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
                                {{ \App\Support\PersianUi::digits($products->total()) }} محصول
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

                                <article class="public-store-product-card edu-card group flex h-full min-w-0 flex-col">
                                    <a href="{{ route('store.product.show', $product) }}" aria-label="مشاهده {{ $product->title }}" class="block">
                                        <div class="relative aspect-[4/3] overflow-hidden bg-[var(--color-slate-100)]">
                                            @if($media?->url())
                                                <img src="{{ $media->url() }}" alt="{{ $product->title }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.045]" loading="lazy" decoding="async">
                                            @else
                                                <div class="flex h-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(104,121,245,.18),transparent_42%),var(--color-slate-100)] text-[var(--color-primary-600)]"><span class="text-4xl font-black">ش</span></div>
                                            @endif
                                            @if($product->category?->name)
                                                <span class="absolute right-3 top-3 rounded-full border border-white/30 bg-black/55 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">{{ $product->category->name }}</span>
                                            @endif
                                        </div>
                                    </a>
                                    <div class="flex flex-1 flex-col p-5">
                                        <h3 class="line-clamp-2 min-h-[3.5rem] text-base font-black leading-7 text-[var(--color-text)]"><a href="{{ route('store.product.show', $product) }}" class="transition-colors hover:text-[var(--color-primary-600)]">{{ $product->title }}</a></h3>
                                        @if($product->description)
                                            <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-secondary)]">{{ $product->description }}</p>
                                        @endif
                                        <div class="mt-auto flex items-end justify-between gap-4 border-t border-[var(--color-border)] pt-5 mt-5">
                                            <div><span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">قیمت</span><strong class="mt-1 block text-base font-black text-[var(--color-primary-600)]">{{ \App\Support\PersianUi::moneyInCurrency($price, $product->currency) }}</strong></div>
                                            <span class="grid h-10 w-10 place-items-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)]" aria-hidden="true">←</span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="public-empty-state" role="status">
        <span class="public-empty-state__icon" aria-hidden="true">⌕</span>
        <h3>محصولی برای نمایش وجود ندارد</h3>
        <p>محصولات آموزشی پس از انتشار در این بخش نمایش داده خواهند شد.</p>
    </div>
                    @endif

                    @if($products->hasPages())
                        <div class="mt-10 border-t border-[var(--color-border)] pt-8">
                            {{ $products->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </section>
    <section class="public-next-step public-next-step--store-index" aria-label="قدم بعدی">
    <div class="mx-auto w-full max-w-[1600px] px-4 sm:px-6 lg:px-8 xl:px-10">
        <div class="public-next-step__card">
            <div class="public-next-step__copy">
                <span>قدم بعدی</span>
                <h2>مسیر یادگیریت را ادامه بده</h2>
                <p>از دوره‌ها و مدرس‌ها دیدن کن و با توجه به نیازت، قدم بعدی را انتخاب کن.</p>
            </div>
            <div class="public-next-step__actions">
                <a class="public-next-step__button public-next-step__button--primary" href="/courses">مشاهده دوره‌ها <span aria-hidden="true">←</span></a>
                <a class="public-next-step__button public-next-step__button--secondary" href="/teachers">آشنایی با مدرس‌ها <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </div>
</section>
@endsection
