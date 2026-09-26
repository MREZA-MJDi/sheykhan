@props(['field'])

@error($field)
<p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>
@enderror