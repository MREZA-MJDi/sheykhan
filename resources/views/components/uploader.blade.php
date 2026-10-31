{{-- Student assignment and exam attachment picker. Server-side validation is authoritative. --}}
@props([
    'name' => 'attachments',
    'max' => 3,
    'maxMb' => 10,
    'accept' => '.pdf,.txt,.jpg,.jpeg,.png,.webp,.zip,.doc,.docx,.odt',
    'label' => 'فایل‌های پاسخ را اینجا بینداز یا انتخاب کن',
])
<div class="up" data-uploader data-max="{{ $max }}" data-max-mb="{{ $maxMb }}">
    <label class="drop">
        <input type="file" name="{{ $name }}[]" multiple accept="{{ $accept }}">
        <svg class="i" aria-hidden="true"><use href="#i-upload"/></svg>
        <b>{{ $label }}</b>
        <small>حداکثر {{ \App\Support\PersianUi::digits($max) }} فایل، هرکدام تا {{ \App\Support\PersianUi::digits($maxMb) }} مگابایت</small>
    </label>
    <p class="ferr" data-file-error role="alert"></p>
    <ul class="files" data-file-list aria-live="polite"></ul>
</div>
