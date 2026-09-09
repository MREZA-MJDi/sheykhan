@props([
'href' => null,
'title' => null,
'description' => null,
'author' => null,
'image' => null,
'imageAlt' => '',
'date' => null,
'meta' => null,
'variant' => 'default',
])

@php
    $baseClasses = '
        block
        rounded-xl
        border
        border-[var(--color-border)]
        bg-[var(--color-surface)]
        p-4
        shadow-[var(--shadow-xs)]
        transition-all
        duration-200
        sm:p-6
    ';

    $variants = [
        'default' => '
            hover:border-[var(--color-border-strong)]
            hover:shadow-[var(--shadow-md)]
        ',
        'interactive' => '
            hover:-translate-y-0.5
            hover:border-[var(--color-brand-200)]
            hover:shadow-[var(--shadow-md)]
        ',
        'plain' => '
            shadow-none
        ',
    ];

    $classes = collect([
        $baseClasses,
        $variants[$variant] ?? $variants['default'],
        $attributes->get('class'),
    ])->filter()->implode(' ');
@endphp

@if($href)
    <a
        href="{{ $href }}"
        {{ $attributes->except('class')->merge(['class' => $classes]) }}
        dir="rtl"
    >
        @else
            <div
                {{ $attributes->except('class')->merge(['class' => $classes]) }}
                dir="rtl"
            >
                @endif

                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6">

                    {{-- Image --}}
                    @if($image)
                        <div class="shrink-0 sm:order-first">
                            <img
                                src="{{ $image }}"
                                alt="{{ $imageAlt }}"
                                loading="lazy"
                                class="h-16 w-16 rounded-full object-cover ring-1 ring-[var(--color-border)] sm:h-[4.5rem] sm:w-[4.5rem]"
                            >
                        </div>
                    @endif

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">

                        @if($title)
                            <h3 class="text-lg font-bold leading-8 text-pretty text-[var(--color-text-primary)]">
                                {{ $title }}
                            </h3>
                        @endif

                        @if($author)
                            <p class="mt-1 text-sm font-medium text-[var(--color-text-muted)]">
                                {{ $author }}
                            </p>
                        @endif

                        @if($description)
                            <p class="mt-4 line-clamp-3 text-sm leading-7 text-pretty text-[var(--color-text-secondary)]">
                                {{ $description }}
                            </p>
                        @endif

                        {{-- Custom content --}}
                        @if($slot->isNotEmpty())
                            <div class="mt-4">
                                {{ $slot }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Meta --}}
                @if($date || $meta)
                    <dl class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-3 border-t border-[var(--color-border)] pt-4">

                        @if($date)
                            <div class="flex items-center gap-2">
                                <dt class="text-[var(--color-text-muted)]">
                                    <span class="sr-only">تاریخ انتشار</span>

                                    <svg
                                        aria-hidden="true"
                                        class="h-4.5 w-4.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25"></path>
                                        <path d="M3 18.75A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75M3 11.25h18"></path>
                                    </svg>
                                </dt>

                                <dd class="text-xs font-medium text-[var(--color-text-muted)]">
                                    {{ $date }}
                                </dd>
                            </div>
                        @endif

                        @if($meta)
                            <div class="flex min-w-0 items-center gap-2">
                                <dt class="text-[var(--color-text-muted)]">
                                    <span class="sr-only">اطلاعات تکمیلی</span>

                                    <svg
                                        aria-hidden="true"
                                        class="h-4.5 w-4.5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    >
                                        <path d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292"></path>
                                        <path d="M12 6.042A8.966 8.966 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18c-2.305 0-4.408.867-6 2.292m0-14.25v14.25"></path>
                                    </svg>
                                </dt>

                                <dd class="truncate text-xs font-medium text-[var(--color-text-muted)]">
                                    {{ $meta }}
                                </dd>
                            </div>
                        @endif

                    </dl>
        @endif

        @if($href)
    </a>
    @else
    </div>
@endif
