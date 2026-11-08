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
    <section class="mt-3 rounded-xl border border-slate-200 bg-white p-3" data-upload-progress-ui hidden aria-live="polite">
        <div data-upload-progress-status hidden>
            <div class="flex items-center justify-between gap-3 text-xs font-bold">
                <span data-upload-progress-message>در انتظار ارسال فایل‌ها…</span>
                <span data-upload-progress-percent>۰٪</span>
            </div>
            <div data-upload-progress-track role="progressbar" aria-label="پیشرفت بارگذاری فایل" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                <div data-upload-progress-bar class="h-full w-0 rounded-full bg-indigo-600 transition-[width] duration-150"></div>
            </div>
        </div>
        <p data-upload-summary class="text-xs text-slate-500"></p>
        <ul data-upload-file-list class="mt-2 grid gap-1 text-xs" role="list"></ul>
        <p data-upload-progress-error hidden class="mt-2 text-xs font-semibold text-rose-600" role="alert"></p>
    </section>
</div>
