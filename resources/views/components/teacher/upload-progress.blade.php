@props(['label' => 'وضعیت بارگذاری فایل'])
<section class="teacher-upload-progress" data-upload-progress-ui hidden aria-label="{{ $label }}">
    <div class="teacher-upload-progress__files" data-upload-file-list></div>
    <div class="teacher-upload-progress__summary" data-upload-summary></div>
    <div class="teacher-upload-progress__status" data-upload-progress-status hidden>
        <div class="teacher-upload-progress__bar-track" role="progressbar" aria-label="پیشرفت ارسال فایل" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" data-upload-progress-track><span class="teacher-upload-progress__bar" data-upload-progress-bar></span></div>
        <div class="teacher-upload-progress__status-row"><strong data-upload-progress-percent>۰٪</strong><span data-upload-progress-message role="status" aria-live="polite">در انتظار ارسال</span></div>
    </div>
    <p class="teacher-upload-progress__error" data-upload-progress-error role="alert" hidden></p>
</section>
