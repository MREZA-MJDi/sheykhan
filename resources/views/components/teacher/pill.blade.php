{{-- type: default | ok | warn | bad | info --}}
@props(['type' => 'default'])
<span {{ $attributes->class(['tp-pill', "tp-pill--{$type}" => $type !== 'default']) }}>{{ $slot }}</span>