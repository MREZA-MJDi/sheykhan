@props([
    'id',
    'label',
    'name',
    'type',
    'endpoint',
    'emptyLabel' => 'یک گزینه را انتخاب کن',
    'searchPlaceholder' => 'برای جست‌وجو بنویس…',
    'statusText' => 'برای جست‌وجو حداقل دو حرف وارد کن.',
    'moreLabel' => 'نمایش گزینه‌های بعدی',
    'selectLabel' => null,
    'helpText' => null,
    'errorKey' => null,
    'required' => false,
    'dependsOnCourse' => false,
    'wrapperClass' => 'grid gap-2',
    'labelClass' => 'text-xs font-bold',
    'searchClass' => 'rounded-xl border border-slate-200 px-3 py-3 text-sm',
    'selectClass' => 'rounded-xl border border-slate-200 px-3 py-3 text-sm',
    'statusClass' => 'text-[11px] text-slate-500',
    'moreClass' => 'w-fit rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold',
])

<div {{ $attributes->merge(['class' => $wrapperClass]) }}
     data-academy-option-picker
     data-endpoint="{{ $endpoint }}"
     data-type="{{ $type }}"
     @if($dependsOnCourse) data-depends-on-course="true" @endif>
    <label for="{{ $id }}-search" class="{{ $labelClass }}">{{ $label }}</label>
    <input id="{{ $id }}-search"
           type="search"
           data-academy-option-search
           autocomplete="off"
           maxlength="80"
           placeholder="{{ $searchPlaceholder }}"
           class="{{ $searchClass }}">
    <select id="{{ $id }}"
            name="{{ $name }}"
            aria-label="{{ $selectLabel ?? $label }}"
            data-academy-option-select
            @required($required)
            class="{{ $selectClass }}">
        <option data-option-placeholder value="">{{ $emptyLabel }}</option>
        {{ $slot }}
    </select>
    <p data-academy-option-status class="{{ $statusClass }}" role="status" aria-live="polite">{{ $statusText }}</p>
    <button type="button" data-academy-option-more hidden class="{{ $moreClass }}">{{ $moreLabel }}</button>
    @if($helpText)
        <small class="owner-field-help">{{ $helpText }}</small>
    @endif
    @if($errorKey)
        @error($errorKey)
            <p role="alert" class="text-xs font-semibold text-[var(--color-danger-600)]">{{ $message }}</p>
        @enderror
    @endif
</div>
