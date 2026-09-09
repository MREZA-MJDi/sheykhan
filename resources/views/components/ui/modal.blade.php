@props([
'id' => 'modal-' . uniqid(),
'title' => null,
'description' => null,
'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'max-w-md',
        'md' => 'max-w-xl',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-5xl',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div
    x-data="{ open: false }"
    x-on:open-modal.window="
        if ($event.detail === '{{ $id }}') open = true
    "
    x-on:keydown.escape.window="open = false"
    x-cloak
>
    {{-- Trigger --}}
    @isset($trigger)
        <div
            @click="open = true"
        >
            {{ $trigger }}
        </div>
    @endisset

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[var(--z-modal-backdrop)] bg-slate-950/50 backdrop-blur-sm"
        aria-hidden="true"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition:enter="transition duration-200 ease-[var(--ease-emphasized)]"
        x-transition:enter-start="scale-95 opacity-0"
        x-transition:enter-end="scale-100 opacity-100"
        x-transition:leave="transition duration-150 ease-[var(--ease-standard)]"
        x-transition:leave-start="scale-100 opacity-100"
        x-transition:leave-end="scale-95 opacity-0"
        class="fixed inset-0 z-[var(--z-modal)] flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $id }}-title"
        @click.self="open = false"
    >
        <div
            class="w-full {{ $sizeClass }} max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl border border-[var(--color-border)] bg-[var(--color-surface)] shadow-[var(--shadow-xl)]"
            dir="rtl"
        >
            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 border-b border-[var(--color-border)] px-5 py-5 sm:px-6">

                <div class="min-w-0">
                    @if($title)
                        <h2
                            id="{{ $id }}-title"
                            class="text-lg font-extrabold text-[var(--color-text-primary)] sm:text-xl"
                        >
                            {{ $title }}
                        </h2>
                    @endif

                    @if($description)
                        <p class="mt-1.5 text-sm leading-6 text-[var(--color-text-secondary)]">
                            {{ $description }}
                        </p>
                    @endif
                </div>

                <button
                    type="button"
                    @click="open = false"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--color-text-muted)] transition-colors duration-200 hover:bg-[var(--color-neutral-100)] hover:text-[var(--color-text-primary)]"
                    aria-label="بستن"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="M6 6l12 12M18 6L6 18"></path>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-5 py-5 sm:px-6 sm:py-6">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @isset($footer)
                <div class="flex flex-col-reverse gap-2 border-t border-[var(--color-border)] px-5 py-4 sm:flex-row sm:items-center sm:justify-start sm:px-6">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
