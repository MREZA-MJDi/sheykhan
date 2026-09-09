@props([
'title',
'description' => null,
'image' => null,
'imageAlt' => null,
'teacher' => null,
'grade' => null,
'subject' => null,
'level' => null,
'sessions' => null,
'duration' => null,
'price' => null,
'oldPrice' => null,
'discount' => null,
'href' => '#',
'progress' => null,
'status' => null,
'featured' => false,
])

@php
    $levelVariant = match ($level) {
        'مقدماتی' => 'success',
        'متوسط' => 'info',
        'پیشرفته' => 'warning',
        default => 'brand',
    };
@endphp

<article
    {{ $attributes->merge([
        'class' => '
            group
            flex
            h-full
            flex-col
            overflow-hidden
            rounded-2xl
            border
            border-[var(--color-border)]
            bg-[var(--color-surface)]
            shadow-[var(--shadow-xs)]
            transition-all
            duration-200
            hover:-translate-y-1
            hover:border-[var(--color-brand-200)]
            hover:shadow-[var(--shadow-md)]
        ',
    ]) }}
    dir="rtl"
>
    {{-- Image / Header --}}
    <a
        href="{{ $href }}"
        class="relative block overflow-hidden bg-[var(--color-neutral-100)]"
        aria-label="{{ $title }}"
    >
        @if($image)
            <img
                src="{{ $image }}"
                alt="{{ $imageAlt ?: $title }}"
                loading="lazy"
                class="aspect-[16/10] w-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
            >
        @else
            <div class="flex aspect-[16/10] items-center justify-center bg-[var(--color-brand-50)]">
                <svg
                    class="h-12 w-12 text-[var(--color-brand-300)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                    <path d="m8 14 2.5-2.5 2 2 1.5-1.5 2.5 2.5" />
                </svg>
            </div>
        @endif

        {{-- Featured --}}
        @if($featured)
            <div class="absolute right-4 top-4">
                <x-ui.badge variant="warning" size="sm">
                    ویژه
                </x-ui.badge>
            </div>
        @endif

        {{-- Discount --}}
        @if($discount)
            <div class="absolute left-4 top-4">
                <x-ui.badge variant="danger" size="sm">
                    {{ $discount }}٪ تخفیف
                </x-ui.badge>
            </div>
        @endif
    </a>

    {{-- Content --}}
    <div class="flex flex-1 flex-col p-5">

        {{-- Meta --}}
        <div class="flex flex-wrap items-center gap-2">
            @if($grade)
                <x-ui.badge
                    variant="brand"
                    size="sm"
                >
                    {{ $grade }}
                </x-ui.badge>
            @endif

            @if($subject)
                <x-ui.badge
                    variant="neutral"
                    size="sm"
                >
                    {{ $subject }}
                </x-ui.badge>
            @endif

            @if($level)
                <x-ui.badge
                    :variant="$levelVariant"
                    size="sm"
                >
                    {{ $level }}
                </x-ui.badge>
            @endif
        </div>

        {{-- Title --}}
        <a
            href="{{ $href }}"
            class="mt-4 block"
        >
            <h3
                class="line-clamp-2 text-lg font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]"
            >
                {{ $title }}
            </h3>
        </a>

        {{-- Description --}}
        @if($description)
            <p class="mt-2 line-clamp-2 text-sm leading-6 text-[var(--color-text-secondary)]">
                {{ $description }}
            </p>
        @endif

        {{-- Teacher --}}
        @if($teacher)
            <div class="mt-5 flex items-center gap-3">
                @if(is_array($teacher))
                    <x-ui.avatar
                        :src="$teacher['avatar'] ?? null"
                        :name="$teacher['name'] ?? null"
                        size="sm"
                    />

                    <div class="min-w-0">
                        <p class="text-xs text-[var(--color-text-muted)]">
                            مدرس
                        </p>

                        <p class="truncate text-sm font-semibold text-[var(--color-text-primary)]">
                            {{ $teacher['name'] ?? '' }}
                        </p>
                    </div>
                @else
                    <x-ui.avatar
                        :name="$teacher"
                        size="sm"
                    />

                    <div class="min-w-0">
                        <p class="text-xs text-[var(--color-text-muted)]">
                            مدرس
                        </p>

                        <p class="truncate text-sm font-semibold text-[var(--color-text-primary)]">
                            {{ $teacher }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Course facts --}}
        @if($sessions || $duration)
            <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-[var(--color-border)] pt-4">
                @if($sessions)
                    <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path d="M5.25 5.25A2.25 2.25 0 0 1 7.5 3h9A2.25 2.25 0 0 1 18.75 5.25v13.5A2.25 2.25 0 0 1 16.5 21h-9a2.25 2.25 0 0 1-2.25-2.25V5.25Z" />
                            <path d="M8.25 7.5h7.5M8.25 11h7.5" />
                        </svg>

                        <span>{{ $sessions }} جلسه</span>
                    </div>
                @endif

                @if($duration)
                    <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="8.75" />
                            <path d="M12 7.5v5l3 1.75" />
                        </svg>

                        <span>{{ $duration }}</span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Progress --}}
        @if($progress !== null)
            <div class="mt-5">
                <x-ui.progress
                    :value="$progress"
                    label="پیشرفت دوره"
                    size="sm"
                />
            </div>
        @endif

        {{-- Footer --}}
        <div class="mt-auto pt-6">
            <div class="flex items-end justify-between gap-4">

                {{-- Price --}}
                <div class="min-w-0">
                    @if($oldPrice)
                        <div class="text-xs text-[var(--color-text-muted)] line-through">
                            {{ $oldPrice }}
                        </div>
                    @endif

                    @if($price)
                        <div class="mt-1 text-base font-extrabold text-[var(--color-text-primary)]">
                            {{ $price }}
                        </div>
                    @else
                        <div class="text-sm font-semibold text-[var(--color-success-700)]">
                            رایگان
                        </div>
                    @endif
                </div>

                {{-- CTA --}}
                <x-ui.button
                    :href="$href"
                    size="sm"
                >
                    {{ $progress !== null ? 'ادامه یادگیری' : 'مشاهده دوره' }}

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </x-ui.button>
            </div>
        </div>
    </div>
</article>
