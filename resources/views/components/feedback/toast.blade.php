@props([
    'type' => 'success',
    'message' => null,
])

@if($message)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-transition
        class="fixed bottom-5 left-5 z-[var(--z-toast)] w-[calc(100%-2.5rem)] max-w-sm rounded-[var(--radius-lg)] border border-[var(--color-border)] bg-white p-4 shadow-[var(--shadow-xl)]"
    >
        <div class="flex items-start gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-[var(--color-text)]">
                    {{ $message }}
                </p>
            </div>

            <button
                type="button"
                @click="show = false"
                class="shrink-0 text-[var(--color-text-muted)] hover:text-[var(--color-text)]"
                aria-label="بستن"
            >
                ×
            </button>
        </div>
    </div>
@endif
