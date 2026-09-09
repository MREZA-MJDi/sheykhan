@props([
'title',
'description' => null,
'eyebrow' => null,
'align' => 'right',
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center items-center',
        'left' => 'text-left items-start',
        default => 'text-right items-start',
    };
@endphp

<header
    {{ $attributes->merge([
        'class' => "flex flex-col {$alignClass} gap-3",
    ]) }}
    dir="rtl"
>
    @if($eyebrow)
        <span class="text-sm font-semibold text-[var(--color-brand-600)]">
            {{ $eyebrow }}
        </span>
    @endif

    <div class="min-w-0">
        <h1
            class="text-2xl font-extrabold tracking-tight text-[var(--color-text-primary)] sm:text-3xl lg:text-4xl"
        >
            {{ $title }}
        </h1>

        @if($description)
            <p
                class="mt-3 max-w-2xl text-sm leading-7 text-[var(--color-text-secondary)] sm:text-base"
            >
                {{ $description }}
            </p>
        @endif
    </div>

    @if($slot->isNotEmpty())
        <div class="mt-2 flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</header>
