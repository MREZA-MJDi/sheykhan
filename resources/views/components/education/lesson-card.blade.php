@props([
'title',
'subtitle' => null,
'duration' => null,
'type' => 'video',
'status' => 'available',
'progress' => null,
'href' => '#',
'number' => null,
])

@php
    $statusConfig = [
        'completed' => [
            'label' => 'تکمیل شده',
            'variant' => 'success',
        ],
        'current' => [
            'label' => 'ادامه یادگیری',
            'variant' => 'brand',
        ],
        'available' => [
            'label' => 'قابل مشاهده',
            'variant' => 'neutral',
        ],
        'locked' => [
            'label' => 'قفل شده',
            'variant' => 'neutral',
        ],
    ];

    $currentStatus = $statusConfig[$status] ?? $statusConfig['available'];

    $typeLabels = [
        'video' => 'ویدئو',
        'document' => 'جزوه',
        'quiz' => 'آزمون',
        'assignment' => 'تکلیف',
    ];

    $typeLabel = $typeLabels[$type] ?? 'درس';
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

        {{-- Number / Type --}}
        <div class="shrink-0">
            @if($number)
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-brand-50)] text-sm font-extrabold text-[var(--color-brand-700)]">
                    {{ $number }}
                </div>
            @else
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--color-neutral-100)] text-[var(--color-text-muted)]">
                    @if($type === 'video')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="m9 7 8 5-8 5V7Z" />
                            <rect x="3.5" y="4.5" width="17" height="15" rx="2" />
                        </svg>
                    @elseif($type === 'document')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M7 3.75h7l4 4V20.25H7A2.25 2.25 0 0 1 4.75 18V6A2.25 2.25 0 0 1 7 3.75Z" />
                            <path d="M14 3.75v4h4" />
                            <path d="M8 12h8M8 15.5h6" />
                        </svg>
                    @elseif($type === 'quiz')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5Z" />
                            <path d="m8 12 2.25 2.25L16 8.5" />
                        </svg>
                    @else
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 17.25V6.75A2.25 2.25 0 0 1 5.25 4.5Z" />
                            <path d="M8 12h8M8 15.5h5" />
                        </svg>
                    @endif
                </div>
            @endif
        </div>

        {{-- Content --}}
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge variant="neutral" size="sm">
                    {{ $typeLabel }}
                </x-ui.badge>

                <x-ui.badge
                    :variant="$currentStatus['variant']"
                    size="sm"
                >
                    {{ $currentStatus['label'] }}
                </x-ui.badge>
            </div>

            @if($status === 'locked')
                <div class="mt-3">
                    <h3 class="text-base font-bold leading-7 text-[var(--color-text-primary)]">
                        {{ $title }}
                    </h3>

                    @if($subtitle)
                        <p class="mt-1 text-sm leading-6 text-[var(--color-text-muted)]">
                            {{ $subtitle }}
                        </p>
                    @endif
                </div>
            @else
                <a
                    href="{{ $href }}"
                    class="mt-3 block"
                >
                    <h3 class="text-base font-bold leading-7 text-[var(--color-text-primary)] transition-colors duration-200 group-hover:text-[var(--color-brand-700)]">
                        {{ $title }}
                    </h3>

                    @if($subtitle)
                        <p class="mt-1 text-sm leading-6 text-[var(--color-text-secondary)]">
                            {{ $subtitle }}
                        </p>
                    @endif
                </a>
            @endif

            {{-- Meta --}}
            @if($duration || $progress !== null)
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2">

                    @if($duration)
                        <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.75" />
                                <path d="M12 7.5v5l3 1.75" />
                            </svg>

                            <span>{{ $duration }}</span>
                        </div>
                    @endif

                    @if($progress !== null)
                        <div class="flex items-center gap-2 text-xs text-[var(--color-text-muted)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.75" />
                                <path d="M12 7.5v9" />
                            </svg>

                            <span>{{ $progress }}٪ تکمیل</span>
                        </div>
                    @endif
                </div>
            @endif

            @if($progress !== null && $status !== 'locked')
                <div class="mt-4">
                    <x-ui.progress
                        :value="$progress"
                        :show-value="false"
                        size="sm"
                    />
                </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="shrink-0">
            @if($status === 'locked')
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-[var(--color-neutral-100)] text-[var(--color-text-muted)]"
                    title="این درس قفل است"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <rect x="5" y="10" width="14" height="10" rx="2" />
                        <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                    </svg>
                </div>
            @elseif($status === 'completed')
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[var(--color-success-50)] text-[var(--color-success-600)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m5 12 4.5 4.5L19 7" />
                    </svg>
                </div>
            @else
                <a
                    href="{{ $href }}"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-[var(--color-brand-50)] text-[var(--color-brand-600)] transition-colors duration-200 hover:bg-[var(--color-brand-100)]"
                    aria-label="مشاهده {{ $title }}"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</article>
