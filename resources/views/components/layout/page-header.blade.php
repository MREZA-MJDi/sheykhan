@props([
    'title',
    'description' => null,
    'eyebrow' => null,
    'breadcrumbs' => [],
])

<section
    {{ $attributes->class([
        'border-b border-[var(--color-border)] bg-[var(--color-surface)]',
    ]) }}
>
    <x-layout.container>
        <div class="py-10 sm:py-12 lg:py-14">
            @if(count($breadcrumbs))
                <div class="mb-7">
                    <x-navigation.breadcrumbs :items="$breadcrumbs" />
                </div>
            @endif

            <div class="max-w-3xl">
                @if($eyebrow)
                    <span class="ui-eyebrow mb-3">{{ $eyebrow }}</span>
                @endif

                <h1 class="text-3xl font-black leading-tight tracking-tight text-[var(--color-text)] sm:text-4xl lg:text-5xl">
                    {{ $title }}
                </h1>

                @if($description)
                    <p class="mt-4 max-w-2xl text-sm leading-8 text-[var(--color-text-secondary)] sm:text-base">
                        {{ $description }}
                    </p>
                @endif

                @isset($actions)
                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        </div>
    </x-layout.container>
</section>