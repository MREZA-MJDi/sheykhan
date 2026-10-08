@props(['name','value'=>null,'label'=>'تاریخ و زمان','dateOnly'=>false,'help'=>null])
<div class="teacher-jalali-field" data-teacher-jalali data-jalali-date-only="{{ $dateOnly ? '1' : '0' }}">
    <label>
        <span>{{ $label }}</span>
        <div class="teacher-jalali-field-grid {{ $dateOnly ? 'is-date-only' : '' }}">
            <input type="text" data-jalali-visible-date dir="ltr" inputmode="numeric" autocomplete="off" placeholder="۱۴۰۵/۰۷/۰۲" aria-label="{{ $label }} شمسی">
            @unless($dateOnly)
                <input type="time" data-jalali-visible-time aria-label="ساعت {{ $label }}">
            @endunless
        </div>
        <input type="hidden" name="{{ $name }}" data-jalali-target value="{{ old($name, $value) }}">
        @if($help)<em>{{ $help }}</em>@endif
        @error($name)<small class="teacher-field-error">{{ $message }}</small>@enderror
    </label>
</div>