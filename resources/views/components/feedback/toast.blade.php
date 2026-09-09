@props([
'type' => 'success',
'title' => null,
'message' => null,
'duration' => 5000,
])

@php
    $types = [
        'success' => [
            'wrapper' => 'border-[var(--color-success-100)] bg-[var(--color-surface)]',
            'icon' => 'bg-[var(--color-success-50)] text-[var(--color-success-600)]',
        ],
        'info' => [
            'wrapper' => 'border-[var(--color-info-100)] bg-[var(--color-surface)]',
            'icon' => 'bg-[var(--color-info-50)] text-[var(--color-info-600)]',
        ],
        'warning' => [
            'wrapper' => 'border-[var(--color-warning-100)] bg-[var(--color-surface)]',
            'icon' => 'bg-[var(--color-warning-50)] text-[var(--color-warning-600)]',
        ],
        'danger' => [
            'wrapper' => 'border-[var(--color-danger-100)] bg-[var(--color-surface)]',
            'icon' => 'bg-[var(--color-danger-50)] text-[var(--color-danger-600)]',
        ],
    ];

    $selected = $types[$type] ?? $types['success'];
@endphp

<div
    x-data="{ show: true }"
    x-init="setTimeout(() => show = false, {{ (int) $duration }})"
    x-show="show"
    x-cloak
    x-transition:enter="transition duration-200 ease-out"
    x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition duration-150 ease-in"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="translate-y-2 opacity-0"
    class="pointer-events-auto w-full max-w-sm rounded-2xl border {{ $selected['wrapper'] }} p-4 shadow-[var(--shadow-lg)]"
    role="status"
    aria-live="polite"
    dir="rtl"
>
    <div class="flex items-start gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $selected['icon'] }}">
            @switch($type)
                @case('success')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m5 12 4.5 4.5L19 7" />
                </svg>
                @break

                @case('warning')
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M12 9v3.75M12 16.5h.008" />
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
                    <path d="M12 10.5v5M12 7.5h.008" />
                </svg>
            @endswitch
        </div>

        <div class="min-w-0 flex-1">
            @if($title)
                <p class="text-sm font-bold text-[var(--color-text-primary)]">
                    {{ $title }}
                </p>
            @endif

            @if($message)
                <p class="{{ $title ? 'mt-1' : '' }} text-sm leading-6 text-[var(--color-text-secondary)]">
                    {{ $message }}
                </p>
            @endif

            @if($slot->isNotEmpty())
                <div class="{{ $title || $message ? 'mt-1' : '' }} text-sm leading-6 text-[var(--color-text-secondary)]">
                    {{ $slot }}
                </div>
            @endif
        </div>

        <button
            type="button"
            @click="show = false"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[var(--color-text-muted)] transition-colors hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]"
            aria-label="بستن پیام"
        >
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                aria-hidden="true"
            >
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>
</div>
