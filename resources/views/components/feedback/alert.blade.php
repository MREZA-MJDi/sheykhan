@props([
'type' => 'info',
'title' => null,
])

@php
    $types = [
        'info' => [
            'wrapper' => 'border-[var(--color-info-100)] bg-[var(--color-info-50)] text-[var(--color-info-700)]',
            'icon' => 'text-[var(--color-info-600)]',
        ],
        'success' => [
            'wrapper' => 'border-[var(--color-success-100)] bg-[var(--color-success-50)] text-[var(--color-success-700)]',
            'icon' => 'text-[var(--color-success-600)]',
        ],
        'warning' => [
            'wrapper' => 'border-[var(--color-warning-100)] bg-[var(--color-warning-50)] text-[var(--color-warning-700)]',
            'icon' => 'text-[var(--color-warning-600)]',
        ],
        'danger' => [
            'wrapper' => 'border-[var(--color-danger-100)] bg-[var(--color-danger-50)] text-[var(--color-danger-700)]',
            'icon' => 'text-[var(--color-danger-600)]',
        ],
    ];

    $selected = $types[$type] ?? $types['info'];
@endphp

<div
    {{ $attributes->merge([
        'class' => "ui-alert {$selected['wrapper']}",
        'role' => 'alert',
    ]) }}
    dir="rtl"
>
    <div class="{{ $selected['icon'] }} mt-0.5 shrink-0">
        @switch($type)
            @case('success')
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="m5 12 4.5 4.5L19 7" />
            </svg>
            @break

            @case('warning')
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M12 9v3.75" />
                <path d="M12 16.5h.008" />
                <path d="m10.3 4.6-7.1 12.3A1.9 1.9 0 0 0 4.85 19.8h14.3a1.9 1.9 0 0 0 1.65-2.9L13.7 4.6a1.9 1.9 0 0 0-3.4 0Z" />
            </svg>
            @break

            @case('danger')
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="m15 9-6 6M9 9l6 6" />
            </svg>
            @break

            @default
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 10.5v5" />
                <path d="M12 7.5h.008" />
            </svg>
        @endswitch
    </div>

    <div class="min-w-0 flex-1">
        @if($title)
            <p class="font-bold">
                {{ $title }}
            </p>
        @endif

        @if($slot->isNotEmpty())
            <div class="{{ $title ? 'mt-1' : '' }} text-sm leading-6">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
