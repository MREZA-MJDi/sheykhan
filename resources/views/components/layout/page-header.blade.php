@props([
'title',
'description' => null,
'eyebrow' => null,
'breadcrumbs' => [],
])

<section
    {{ $attributes->class([
        'border-b border-gray-100 bg-white',
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
                    <span class="mb-3 block text-sm font-medium text-gray-500">
                        {{ $eyebrow }}
                    </span>
                @endif

                <h1
                    class="text-2xl font-bold tracking-tight text-gray-950
                           sm:text-3xl lg:text-4xl"
                >
                    {{ $title }}
                </h1>

                @if($description)
                    <p
                        class="mt-4 max-w-2xl text-sm leading-7 text-gray-500
                               sm:text-base"
                    >
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
