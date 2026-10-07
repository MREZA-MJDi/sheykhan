@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'disabled' => false,
    'block' => false,
    'href' => null,
    'external' => false,
])

@php
    $base = 'group relative inline-flex items-center justify-center gap-2 overflow-hidden whitespace-nowrap font-bold transition duration-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[var(--color-primary-100)]';

    $variants = [
        'primary' => 'bg-[var(--color-primary-600)] text-white shadow-[0_10px_24px_rgba(83,98,223,.18)] hover:-translate-y-0.5 hover:bg-[var(--color-primary-700)] hover:shadow-[0_14px_30px_rgba(83,98,223,.22)] active:translate-y-0',
        'secondary' => 'border border-[var(--color-border)] bg-white text-[var(--color-slate-900)] shadow-sm hover:-translate-y-0.5 hover:bg-[var(--color-slate-50)] hover:shadow-md active:translate-y-0',
        'outline' => 'border border-[var(--color-border-strong)] bg-white text-[var(--color-slate-800)] hover:-translate-y-0.5 hover:border-[var(--color-primary-300)] hover:text-[var(--color-primary-700)] hover:shadow-sm',
        'ghost' => 'bg-transparent text-[var(--color-slate-700)] hover:bg-[var(--color-slate-100)]',
        'danger' => 'bg-[var(--color-danger-600)] text-white hover:bg-[var(--color-danger-700)]',
        'accent' => 'bg-[var(--color-accent-500)] text-white hover:bg-[var(--color-accent-600)]',
        'link' => 'bg-transparent px-0 text-[var(--color-primary-600)] hover:text-[var(--color-primary-700)]',
    ];

    $sizes = [
        'sm' => 'min-h-9 rounded-xl px-3 text-xs',
        'md' => 'min-h-11 rounded-xl px-4 text-sm',
        'lg' => 'min-h-12 rounded-[.9rem] px-5 text-sm sm:text-base',
        'xl' => 'min-h-14 rounded-[1rem] px-6 text-base',
    ];

    $classes = trim(
        "{$base} "
        . ($variants[$variant] ?? $variants['primary'])
        . " "
        . ($sizes[$size] ?? $sizes['md'])
        . " "
        . ($block ? 'w-full' : '')
        . " "
        . ($disabled ? 'pointer-events-none opacity-50' : '')
    );
@endphp

@if($href)
    <a
        href="{{ $disabled ? '#' : $href }}"
        @if($external && !$disabled) target="_blank" rel="noopener noreferrer" @endif
        @if($disabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->class($classes) }}
    >
        <span>{{ $slot }}</span>
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->class($classes) }}
    >
        <span>{{ $slot }}</span>
    </button>
@endif