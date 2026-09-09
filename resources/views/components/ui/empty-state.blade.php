@props([
    'title' => 'موردی پیدا نشد',
    'description' => null,
])

<div
    {{ $attributes->merge([
        'class' => 'flex flex-col items-center justify-center rounded-[var(--radius-xl)] border border-dashed border-[var(--color-border-strong)] bg-white px-6 py-12 text-center',
    ]) }}
>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[var(--color-slate-100)] text-[var(--color-slate-500)]">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 0 0-2-2h-3.5L13 3.5h-2L9.5 5H6a2 2 0 0 0-2 2v6m16 0-2.5 6h-11L4 13m16 0H4"/>
        </svg>
    </div>

    <h3 class="text-base font-bold text-[var(--color-text)]">
        {{ $title }}
    </h3>

    @if($description)
        <p class="mt-2 max-w-md text-sm text-[var(--color-text-muted)]">
            {{ $description }}
        </p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
