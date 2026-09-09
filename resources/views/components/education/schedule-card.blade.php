@props([
'title',
'subtitle' => null,
'date' => null,
'day' => null,
'startTime' => null,
'endTime' => null,
'teacher' => null,
'course' => null,
'type' => 'class',
'status' => 'upcoming',
'href' => '#',
])

@php
    $typeConfig = [
        'class' => [
            'label' => 'کلاس',
            'iconBg' => 'bg-[var(--color-brand-50)]',
            'iconColor' => 'text-[var(--color-brand-600)]',
        ],
        'exam' => [
            'label' => 'آزمون',
            'iconBg' => 'bg-[var(--color-warning-50)]',
            'iconColor' => 'text-[var(--color-warning-600)]',
        ],
        'assignment' => [
            'label' => 'تکلیف',
            'iconBg' => 'bg-[var(--color-info-50)]',
            'iconColor' => 'text-[var(--color-info-600)]',
        ],
        'live' => [
            'label' => 'کلاس آنلاین',
            'iconBg' => 'bg-[var(--color-danger-50)]',
            'iconColor' => 'text-[var(--color-danger-600)]',
        ],
    ];

    $statusConfig = [
        'upcoming' => [
            'label' => 'پیش رو',
            'variant' => 'brand',
        ],
        'today' => [
            'label' => 'امروز',
            'variant' => 'success',
        ],
        'completed' => [
            'label' => 'انجام شده',
            'variant' => 'neutral',
        ],
        'cancelled' => [
            'label' => 'لغو شده',
            'variant' => 'danger',
        ],
    ];

    $currentType = $typeConfig[$type] ?? $typeConfig['class'];
    $currentStatus = $statusConfig[$status] ?? $statusConfig['upcoming'];
@endphp

<article
    {{ $attributes->merge([
        'class' => '
            group
            rounded-2xl
            border
            border-[var(--color-border)]
            bg-[var(--color-surface)]
            p-4
            shadow-[var(--shadow-xs)]
            transition-all
            duration-200
            hover:border-[var(--color-border-strong)]
            hover:shadow-[var(--shadow-sm)]
        ',
    ]) }}
    dir="rtl"
>
    <div class="flex items-start gap-4">

        {{-- Date --}}
        @if($day || $date)
            <div class="hidden w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-[var(--color-brand-50)] px-2 py-3 text-center sm:flex">
                @if($day)
                    <span class="text-xs font-semibold text-[var(--color-brand-600)]">
                        {{ $day }}
                    </span>
                @endif

                @if($date)
                    <span class="mt-1 text-sm font-extrabold text-[var(--color-brand-800)]">
                        {{ $date }}
                    </span>
                @endif
            </div>
        @endif

        {{-- Icon --}}
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $currentType['iconBg'] }} {{ $currentType['iconColor'] }}">
            @switch($type)
                @case('exam')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path d="M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z" />
                    <path d="M8.5 9h7M8.5 12h7M8.5 15h4.5" />
                </svg>
                @break

                @case('assignment')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <path d="M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6a2.25 2.25 0 0 1 2.25-2.25Z" />
                    <path d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5" />
                </svg>
                @break

                @case('live')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <rect x="3.5" y="5" width="17" height="14" rx="2" />
                    <path d="m10 9 5 3-5 3V9Z" />
                </svg>
                @break

                @default
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                    <rect x="4" y="5" width="16" height="14" rx="2" />
                    <path d="M8 3v4M16 3v4M4 10h16" />
                    <path d="M8 14h3M8 17h5" />
                </svg>
            @endswitch
        </div>

        {{-- Main content --}}
        <div class="min-w-0 flex-1">

            {{-- Mobile date --}}
            @if($day || $date)
                <div class="mb-2 flex items-center gap-2 text-xs font-medium text-[var(--color-text-muted)] sm:hidden">
                    @if($day)
                        <span>{{ $day }}</span>
                    @endif

                    @if($date)
                        <span>{{ $date }}</span>
                    @endif
                </div>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge
                    variant="neutral"
                    size="sm"
                >
                    {{ $currentType['label'] }}
                </x-ui.badge>

                <x-ui.badge
                    :variant="$currentStatus['variant']"
                    size="sm"
                >
                    {{ $currentStatus['label'] }}
                </x-ui.badge>
            </div>

            <a
                href="{{ $href }}"
                class="mt-2 block"
            >
                <h3 class="line-clamp-2 text-base font-extrabold leading-7 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                    {{ $title }}
                </h3>
            </a>

            @if($subtitle)
                <p class="mt-1 line-clamp-1 text-sm leading-6 text-[var(--color-text-muted)]">
                    {{ $subtitle }}
                </p>
            @endif

            {{-- Schedule details --}}
            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">

                @if($startTime)
                    <div class="flex items-center gap-2 text-xs font-semibold text-[var(--color-text-secondary)]">
                        <svg class="h-4 w-4 text-[var(--color-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <circle cx="12" cy="12" r="8.75" />
                            <path d="M12 7.5v5l3 1.75" />
                        </svg>

                        <span>
                            {{ $startTime }}

                            @if($endTime)
                                تا {{ $endTime }}
                            @endif
                        </span>
                    </div>
                @endif

                @if($course)
                    <div class="flex min-w-0 items-center gap-2 text-xs text-[var(--color-text-muted)]">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                            <path d="M8 9h8M8 13h6M8 17h4" />
                        </svg>

                        <span class="truncate">
                            {{ $course }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- Teacher --}}
            @if($teacher)
                <div class="mt-4 flex items-center gap-2">
                    <x-ui.avatar
                        :name="is_array($teacher) ? ($teacher['name'] ?? null) : $teacher"
                        :src="is_array($teacher) ? ($teacher['avatar'] ?? null) : null"
                        size="xs"
                    />

                    <span class="truncate text-xs font-medium text-[var(--color-text-muted)]">
                        {{ is_array($teacher) ? ($teacher['name'] ?? '') : $teacher }}
                    </span>
                </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="shrink-0">
            <a
                href="{{ $href }}"
                class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-neutral-50)] text-[var(--color-text-muted)] transition-colors duration-200 hover:bg-[var(--color-brand-50)] hover:text-[var(--color-brand-600)]"
                aria-label="مشاهده {{ $title }}"
            >
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
            </a>
        </div>
    </div>
</article>
