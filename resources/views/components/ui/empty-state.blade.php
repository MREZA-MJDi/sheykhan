@props([
    'title' => 'موردی پیدا نشد',
    'description' => null,
])

<div {{ $attributes->class(['ui-empty']) }}>
    <div class="ui-empty__icon" aria-hidden="true">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 0 0-2-2h-3.5L13 3.5h-2L9.5 5H6a2 2 0 0 0-2 2v6m16 0-2.5 6h-11L4 13m16 0H4"/>
        </svg>
    </div>

    <h3 class="ui-empty__title">{{ $title }}</h3>

    @if($description)
        <p class="ui-empty__description">{{ $description }}</p>
    @endif

    @if($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>