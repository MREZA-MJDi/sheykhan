@props(['field'])

@error($field)
<p role="alert" class="text-xs font-semibold text-rose-600">{{ $message }}</p>
@enderror