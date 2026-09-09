@props([
    'title' => 'خطایی رخ داده است',
    'description' => null,
])

<div
    {{ $attributes->merge([
        'class' => 'rounded-[var(--radius-xl)] border border-red-200 bg-red-50 p-6 text-center',
    ]) }}
>
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600">
        !
    </div>

    <h3 class="mt-4 text-base font-bold text-red-900">
        {{ $title }}
    </h3>

    @if($description)
        <p class="mt-2 text-sm leading-6 text-red-700">
            {{ $description }}
        </p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-5">
            {{ $slot }}
        </div>
    @endif
</div>
