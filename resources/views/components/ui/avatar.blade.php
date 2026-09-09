@props([
'src' => null,
'alt' => '',
'name' => null,
'size' => 'md',
'shape' => 'circle',
])

@php
    $sizes = [
        'xs' => [
            'wrapper' => 'h-7 w-7',
            'text' => 'text-[10px]',
        ],
        'sm' => [
            'wrapper' => 'h-9 w-9',
            'text' => 'text-xs',
        ],
        'md' => [
            'wrapper' => 'h-11 w-11',
            'text' => 'text-sm',
        ],
        'lg' => [
            'wrapper' => 'h-14 w-14',
            'text' => 'text-base',
        ],
        'xl' => [
            'wrapper' => 'h-20 w-20',
            'text' => 'text-xl',
        ],
        '2xl' => [
            'wrapper' => 'h-24 w-24',
            'text' => 'text-2xl',
        ],
    ];

    $shapes = [
        'circle' => 'rounded-full',
        'rounded' => 'rounded-xl',
        'square' => 'rounded-md',
    ];

    $selectedSize = $sizes[$size] ?? $sizes['md'];
    $selectedShape = $shapes[$shape] ?? $shapes['circle'];

    $initials = null;

    if ($name) {
        $parts = preg_split('/\s+/u', trim($name));

        if (count($parts) >= 2) {
            $initials = mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
        } else {
            $initials = mb_substr($name, 0, 1);
        }
    }

    $classes = collect([
        'relative inline-flex shrink-0 items-center justify-center overflow-hidden',
        $selectedSize['wrapper'],
        $selectedShape,
        'bg-[var(--color-brand-50)] text-[var(--color-brand-700)]',
        'ring-1 ring-[var(--color-border)]',
        $attributes->get('class'),
    ])->filter()->implode(' ');
@endphp

<span
    {{ $attributes->except('class')->merge(['class' => $classes]) }}
    @if($name)
    title="{{ $name }}"
    @endif
>
    @if($src)
        <img
            src="{{ $src }}"
            alt="{{ $alt ?: $name }}"
            loading="lazy"
            class="h-full w-full object-cover"
        >
    @elseif($slot->isNotEmpty())
        {{ $slot }}
    @elseif($initials)
        <span class="{{ $selectedSize['text'] }} font-bold leading-none">
            {{ $initials }}
        </span>
    @else
        <svg
            class="h-1/2 w-1/2 text-[var(--color-text-muted)]"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.7"
            aria-hidden="true"
        >
            <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            <path d="M4.5 20.25a8.25 8.25 0 0 1 15 0" />
        </svg>
    @endif
</span>
