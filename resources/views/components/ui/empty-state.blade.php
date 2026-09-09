@props([
'title' => 'موردی پیدا نشد',
'description' => null,
'icon' => 'default',
'action' => null,
'actionHref' => null,
'actionVariant' => 'primary',
])

@php
    $icons = [
        'default' => <<<'SVG'
            <svg
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-16 w-16"
            >
                <path d="M12 9v3.75" />
                <path d="M12 15.75h.008" />
                <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        SVG,

        'course' => <<<'SVG'
            <svg
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-16 w-16"
            >
                <path d="M4.5 5.25A2.25 2.25 0 0 1 6.75 3h11.5A1.75 1.75 0 0 1 20 4.75v14.5A1.75 1.75 0 0 1 18.25 21h-11.5A2.25 2.25 0 0 1 4.5 18.75v-13.5Z" />
                <path d="M8 7h8M8 11h8M8 15h5" />
            </svg>
        SVG,

        'student' => <<<'SVG'
            <svg
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-16 w-16"
            >
                <path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                <path d="M4.5 20.25a8.25 8.25 0 0 1 15 0" />
            </svg>
        SVG,

        'calendar' => <<<'SVG'
            <svg
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-16 w-16"
            >
                <path d="M6.75 3v2.25M17.25 3v2.25" />
                <path d="M3.75 8.25h16.5" />
                <path d="M5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25Z" />
            </svg>
        SVG,

        'search' => <<<'SVG'
            <svg
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
                class="h-16 w-16"
            >
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>
        SVG,
    ];

    $selectedIcon = $icons[$icon] ?? $icons['default'];
@endphp

<div
    {{ $attributes->merge([
        'class' => 'ui-empty-state',
    ]) }}
    dir="rtl"
>
    {{-- Icon --}}
    <div
        class="flex h-20 w-20 items-center justify-center rounded-full bg-[var(--color-brand-50)] text-[var(--color-brand-600)]"
    >
        {!! $selectedIcon !!}
    </div>

    {{-- Content --}}
    <div class="mt-6 max-w-md">
        <h2
            class="text-xl font-extrabold text-[var(--color-text-primary)] sm:text-2xl"
        >
            {{ $title }}
        </h2>

        @if($description)
            <p class="mt-3 text-sm leading-7 text-pretty text-[var(--color-text-secondary)]">
                {{ $description }}
            </p>
        @endif
    </div>

    {{-- Action --}}
    @if($action)
        <div class="mt-6">
            @if($actionHref)
                <x-ui.button
                    :href="$actionHref"
                    :variant="$actionVariant"
                >
                    {{ $action }}
                </x-ui.button>
            @else
                <x-ui.button :variant="$actionVariant">
                    {{ $action }}
                </x-ui.button>
            @endif
        </div>
    @endif

    {{-- Secondary content --}}
    @if($slot->isNotEmpty())
        <div class="mt-6 text-sm text-[var(--color-text-muted)]">
            {{ $slot }}
        </div>
    @endif
</div>
