@props([
'name',
'value',
'label',
'description' => null,
'suffix' => null,
'checked' => false,
'disabled' => false,
])

@php
    $id = $attributes->get('id')
        ?? ($name . '-' . uniqid());

    $inputAttributes = $attributes->except([
        'id',
        'class',
    ]);
@endphp

<label
    for="{{ $id }}"
    class="
        group
        relative
        flex
        cursor-pointer
        items-center
        gap-4
        rounded-xl
        border
        border-[var(--color-border)]
        bg-[var(--color-surface)]
        p-4
        shadow-[var(--shadow-xs)]
        transition-all
        duration-200
        hover:border-[var(--color-border-strong)]
        hover:bg-[var(--color-neutral-50)]
        has-checked:border-[var(--color-brand-600)]
        has-checked:bg-[var(--color-brand-50)]
        has-checked:ring-1
        has-checked:ring-[var(--color-brand-600)]
        has-checked:shadow-[var(--shadow-sm)]
        {{ $disabled ? 'cursor-not-allowed opacity-50' : '' }}
        "
    dir="rtl"
>
    {{-- Content --}}
    <div class="min-w-0 flex-1">
        <div class="flex items-center justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm font-bold text-[var(--color-text-primary)]">
                    {{ $label }}
                </p>

                @if($description)
                    <p class="mt-1 text-xs leading-5 text-[var(--color-text-muted)]">
                        {{ $description }}
                    </p>
                @endif
            </div>

            @if($suffix !== null)
                <span class="shrink-0 text-sm font-semibold text-[var(--color-text-primary)]">
                    {{ $suffix }}
                </span>
            @endif
        </div>
    </div>

    {{-- Radio --}}
    <span class="relative flex h-5 w-5 shrink-0 items-center justify-center">
        <input
            id="{{ $id }}"
            type="radio"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @disabled($disabled)
            {{ $inputAttributes->merge([
                'class' => '
                    peer
                    sr-only
                '
            ]) }}
        />

        <span
            aria-hidden="true"
            class="
                flex
                h-5
                w-5
                items-center
                justify-center
                rounded-full
                border-2
                border-[var(--color-neutral-300)]
                bg-[var(--color-surface)]
                transition-all
                duration-150
                peer-checked:border-[var(--color-brand-600)]
                peer-checked:bg-[var(--color-brand-600)]
                peer-focus-visible:ring-4
                peer-focus-visible:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
            "
        >
            <span
                class="
                    h-2
                    w-2
                    scale-0
                    rounded-full
                    bg-white
                    transition-transform
                    duration-150
                    peer-checked:scale-100
                "
            ></span>
        </span>
    </span>
</label>
