@props([
    'title' => 'محصول آموزشی',
    'price' => null,
    'category' => null,
    'image' => null,
    'description' => null,
    'href' => '#',
])

<article class="edu-card group flex h-full min-w-0 flex-col">
    <a
        href="{{ $href }}"
        class="block"
        aria-label="مشاهده {{ $title }}"
    >
        <div class="relative aspect-[4/3] overflow-hidden bg-[var(--color-slate-100)]">
            @if($image)
                <img
                    src="{{ $image }}"
                    alt="{{ $title }}"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.045]"
                    loading="lazy"
                >
            @else
                <div class="flex h-full w-full items-center justify-center bg-[radial-gradient(circle_at_30%_20%,rgba(104,121,245,.18),transparent_42%),var(--color-slate-100)] text-[var(--color-primary-600)]">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m20 12-7.5 7.5L4 11V4h7l9 8Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 8h.01"/>
                    </svg>
                </div>
            @endif

            @if($category)
                <span class="absolute right-3 top-3 rounded-full border border-white/30 bg-black/55 px-3 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                    {{ $category }}
                </span>
            @endif
        </div>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="line-clamp-2 min-h-[3.5rem] text-base font-black leading-7 text-[var(--color-text)]">
            <a href="{{ $href }}" class="transition-colors hover:text-[var(--color-primary-600)]">
                {{ $title }}
            </a>
        </h3>

        @if($description)
            <p class="mt-2 line-clamp-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                {{ $description }}
            </p>
        @endif

        <div class="mt-auto flex items-end justify-between gap-4 border-t border-[var(--color-border)] pt-5">
            <div>
                <span class="block text-[11px] font-semibold text-[var(--color-text-muted)]">
                    قیمت
                </span>

                <strong class="mt-1 block text-base font-black text-[var(--color-primary-600)]">
                    {{ $price ?: 'تماس بگیرید' }}
                </strong>
            </div>

            <span
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--color-primary-50)] text-[var(--color-primary-600)] transition group-hover:-translate-x-1"
                aria-hidden="true"
            >
                ←
            </span>
        </div>
    </div>
</article>