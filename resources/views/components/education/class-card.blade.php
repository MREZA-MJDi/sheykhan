@props([
'title',
'teacher' => null,
'subject' => null,
'grade' => null,
'date' => null,
'startTime' => null,
'endTime' => null,
'duration' => null,
'status' => 'upcoming',
'classroom' => null,
'href' => '#',
])

@php
    $statusConfig = [
        'live' => [
            'label' => 'در حال برگزاری',
            'variant' => 'danger',
        ],
        'upcoming' => [
            'label' => 'پیش رو',
            'variant' => 'brand',
        ],
        'completed' => [
            'label' => 'برگزار شده',
            'variant' => 'success',
        ],
        'cancelled' => [
            'label' => 'لغو شده',
            'variant' => 'neutral',
        ],
    ];

    $currentStatus = $statusConfig[$status] ?? $statusConfig['upcoming'];
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
            hover:-translate-y-0.5
            hover:border-[var(--color-brand-200)]
            hover:shadow-[var(--shadow-md)]
        ',
    ]) }}
    dir="rtl"
>
    {{-- Header --}}
    <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border)] p-5">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                @if($subject)
                    <x-ui.badge
                        variant="neutral"
                        size="sm"
                    >
                        {{ $subject }}
                    </x-ui.badge>
                @endif

                @if($grade)
                    <x-ui.badge
                        variant="brand"
                        size="sm"
                    >
                        {{ $grade }}
                    </x-ui.badge>
                @endif
            </div>

            <a
                href="{{ $href }}"
                class="mt-3 block"
            >
                <h3 class="line-clamp-2 text-lg font-extrabold leading-8 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                    {{ $title }}
                </h3>
            </a>
        </div>

        <x-ui.badge
            :variant="$currentStatus['variant']"
            size="sm"
        >
            @if($status === 'live')
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-current"></span>
            @endif

            {{ $currentStatus['label'] }}
        </x-ui.badge>
    </div>

    {{-- Main content --}}
    <div class="flex flex-1 flex-col p-5">

        {{-- Teacher --}}
        @if($teacher)
            <div class="flex items-center gap-3">
                <x-ui.avatar
                    :name="is_array($teacher) ? ($teacher['name'] ?? null) : $teacher"
                    :src="is_array($teacher) ? ($teacher['avatar'] ?? null) : null"
                    size="sm"
                />

                <div class="min-w-0">
                    <p class="text-xs text-[var(--color-text-muted)]">
                        مدرس
                    </p>

                    <p class="truncate text-sm font-semibold text-[var(--color-text-primary)]">
                        {{ is_array($teacher) ? ($teacher['name'] ?? '') : $teacher }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Schedule --}}
        @if($date || $startTime || $endTime || $duration)
            <div class="mt-5 rounded-xl bg-[var(--color-neutral-50)] p-4">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-3">

                    @if($date)
                        <div class="flex items-center gap-2 text-sm">
                            <svg
                                class="h-4.5 w-4.5 text-[var(--color-brand-600)]"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <path d="M6.75 3v2.25M17.25 3v2.25" />
                                <path d="M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25" />
                                <path d="M3 11.25h18" />
                            </svg>

                            <span class="font-medium text-[var(--color-text-secondary)]">
                                {{ $date }}
                            </span>
                        </div>
                    @endif

                    @if($startTime || $endTime)
                        <div class="flex items-center gap-2 text-sm">
                            <svg
                                class="h-4.5 w-4.5 text-[var(--color-brand-600)]"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                aria-hidden="true"
                            >
                                <circle cx="12" cy="12" r="8.75" />
                                <path d="M12 7.5v5l3 1.75" />
                            </svg>

                            <span class="font-semibold text-[var(--color-text-primary)]">
                                {{ $startTime }}

                                @if($endTime)
                                    تا {{ $endTime }}
                                @endif
                            </span>
                        </div>
                    @endif

                    @if($duration)
                        <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                            <span>
                                {{ $duration }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Classroom --}}
        @if($classroom)
            <div class="mt-4 flex items-center gap-2 text-sm text-[var(--color-text-secondary)]">
                <svg
                    class="h-4 w-4 text-[var(--color-text-muted)]"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M4.5 19.5h15" />
                    <path d="M6 19.5V6.75A2.25 2.25 0 0 1 8.25 4.5h7.5A2.25 2.25 0 0 1 18 6.75V19.5" />
                    <path d="M9 8.25h6M9 11.5h6M9 14.75h3" />
                </svg>

                <span>
                    {{ $classroom }}
                </span>
            </div>
        @endif

        {{-- Action --}}
        <div class="mt-auto pt-6">
            @if($status === 'live')
                <x-ui.button
                    :href="$href"
                    size="sm"
                    full-width
                >
                    ورود به کلاس

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
            @elseif($status === 'completed')
                <x-ui.button
                    :href="$href"
                    variant="secondary"
                    size="sm"
                    full-width
                >
                    مشاهده جزئیات
                </x-ui.button>
            @elseif($status === 'cancelled')
                <x-ui.button
                    variant="secondary"
                    size="sm"
                    full-width
                    disabled
                >
                    کلاس لغو شده
                </x-ui.button>
            @else
                <x-ui.button
                    :href="$href"
                    variant="soft"
                    size="sm"
                    full-width
                >
                    مشاهده کلاس
                </x-ui.button>
            @endif
        </div>
    </div>
</article>
