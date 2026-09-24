import Alpine from 'alpinejs';
import { bootPanel } from './panel-base.js';

window.Alpine = Alpine;

function formatUploadSize(bytes) {
    if (!Number.isFinite(bytes) || bytes <= 0) return '۰ بایت';

    const units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
    let value = bytes;
    let unit = 0;

    while (value >= 1024 && unit < units.length - 1) {
        value /= 1024;
        unit++;
    }

    return `${new Intl.NumberFormat('fa-IR', { maximumFractionDigits: unit ? 1 : 0 }).format(value)} ${units[unit]}`;
}

function syncAjaxUploadForm(form) {
    const input = form.querySelector('input[type="file"][name="media"]');
    const preview = form.closest('[data-media-uploader]')?.querySelector('[data-upload-preview]');
    const status = form.closest('[data-media-uploader]')?.querySelector('[data-upload-message]');
    const percentLabel = form.closest('[data-media-uploader]')?.querySelector('[data-upload-percent]');
    const progressWrap = form.closest('[data-media-uploader]')?.querySelector('[data-upload-progress]');
    const progressBar = form.closest('[data-media-uploader]')?.querySelector('[data-upload-progress-bar]');
    const submit = form.querySelector('button[type="submit"]');
    const maxSizes = {
        jpg: 10 * 1024 * 1024,
        jpeg: 10 * 1024 * 1024,
        png: 10 * 1024 * 1024,
        webp: 10 * 1024 * 1024,
        pdf: 50 * 1024 * 1024,
        zip: 200 * 1024 * 1024,
        mp4: 500 * 1024 * 1024,
        webm: 500 * 1024 * 1024,
        mov: 500 * 1024 * 1024
    };

    if (!input || form.dataset.ajaxUploader === 'bound') return;
    form.dataset.ajaxUploader = 'bound';

    const setStatus = (message = '', tone = '') => {
        if (!status) return;
        status.textContent = message;
        status.className = 'course-upload-status' + (tone ? ` ${tone}` : '');
    };

    const resetProgress = () => {
        if (progressWrap) progressWrap.hidden = true;
        if (progressBar) progressBar.style.width = '0%';
    };

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            if (preview) preview.hidden = true;
            setStatus('');
            resetProgress();
            return;
        }

        if (maxBytes && file.size > maxBytes) {
            input.value = '';
            if (preview) preview.hidden = true;
            setStatus(`حجم فایل ${formatUploadSize(file.size)} است؛ حداکثر مجاز ${formatUploadSize(maxBytes)} است.`, 'error');
            return;
        }

        const wrapper = form.closest('[data-media-uploader]');
        const previewImage = wrapper?.querySelector('[data-preview-image]');
        const previewVideo = wrapper?.querySelector('[data-preview-video]');
        const previewName = wrapper?.querySelector('[data-preview-name]');
        const previewSize = wrapper?.querySelector('[data-preview-size]');
        const previewGeneric = wrapper?.querySelector('[data-preview-generic]');
        const objectUrl = URL.createObjectURL(file);

        if (preview) preview.hidden = false;
        if (previewName) previewName.textContent = file.name;
        if (previewSize) previewSize.textContent = formatUploadSize(file.size);
        if (previewImage) previewImage.hidden = true;
        if (previewVideo) {
            previewVideo.hidden = true;
            previewVideo.pause();
            previewVideo.removeAttribute('src');
        }
        if (previewGeneric) previewGeneric.hidden = true;

        if (file.type.startsWith('image/') && previewImage) {
            previewImage.src = objectUrl;
            previewImage.hidden = false;
            previewImage.onload = () => URL.revokeObjectURL(objectUrl);
        } else if (file.type.startsWith('video/') && previewVideo) {
            previewVideo.src = objectUrl;
            previewVideo.hidden = false;
            previewVideo.onloadeddata = () => URL.revokeObjectURL(objectUrl);
        } else if (previewGeneric) {
            previewGeneric.hidden = false;
        }

        setStatus(`${formatUploadSize(file.size)} آماده آپلود است.`, '');
        resetProgress();
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        if (form.dataset.uploading === 'true') return;

        const file = input.files?.[0];
        if (!file) {
            setStatus('ابتدا یک فایل انتخاب کن.', 'error');
            return;
        }

        const maxBytes = Number(form.dataset.maxBytes || 0);
        if (maxBytes && file.size > maxBytes) {
            setStatus(`حجم فایل بیشتر از ${formatUploadSize(maxBytes)} است.`, 'error');
            return;
        }

        form.dataset.uploading = 'true';
        if (submit) {
            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            submit.dataset.originalText = submit.textContent;
            submit.textContent = 'در حال آپلود…';
        }
        if (progressWrap) progressWrap.hidden = false;
        if (progressBar) progressBar.style.width = '0%';
        setStatus('در حال شروع آپلود…', '');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.content || '');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');

        xhr.upload.addEventListener('progress', (event) => {
            if (!event.lengthComputable) {
                setStatus('در حال آپلود فایل…', '');
                return;
            }

            const percent = Math.round((event.loaded / event.total) * 100);
            if (progressBar) progressBar.style.width = `${percent}%`;
            if (percentLabel) percentLabel.textContent = `${percent}٪`;
            setStatus(`در حال آپلود · ${formatUploadSize(event.loaded)} از ${formatUploadSize(event.total)}`, '');
        });

        xhr.addEventListener('load', () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                setStatus('آپلود با موفقیت انجام شد. در حال به‌روزرسانی…', 'success');
                window.location.reload();
                return;
            }

            let message = 'آپلود انجام نشد. فایل یا اتصال را بررسی کن.';
            try {
                const payload = JSON.parse(xhr.responseText);
                if (payload?.message) message = payload.message;
                if (payload?.errors?.media?.[0]) message = payload.errors.media[0];
            } catch (_) {}

            form.dataset.uploading = 'false';
            form.dataset.submitted = 'false';
            if (submit) {
                submit.disabled = false;
                submit.removeAttribute('aria-busy');
                submit.textContent = submit.dataset.originalText || 'آپلود فایل';
            }
            setStatus(message, 'error');
            if (progressWrap) progressWrap.hidden = true;
            if (percentLabel) percentLabel.textContent = '۰٪';
        });

        xhr.addEventListener('error', () => {
            form.dataset.uploading = 'false';
            form.dataset.submitted = 'false';
            if (submit) {
                submit.disabled = false;
                submit.removeAttribute('aria-busy');
                submit.textContent = submit.dataset.originalText || 'آپلود فایل';
            }
            setStatus('ارتباط با سرور قطع شد. دوباره تلاش کن.', 'error');
            if (progressWrap) progressWrap.hidden = true;
        });

        xhr.addEventListener('abort', () => {
            form.dataset.uploading = 'false';
            form.dataset.submitted = 'false';
            if (submit) {
                submit.disabled = false;
                submit.removeAttribute('aria-busy');
                submit.textContent = submit.dataset.originalText || 'آپلود فایل';
            }
            setStatus('آپلود لغو شد.', 'error');
            if (progressWrap) progressWrap.hidden = true;
        });

        xhr.send(new FormData(form));
    });
}

function bootCourseMediaUploaders() {
    document.querySelectorAll('[data-upload-form]').forEach((form) => syncAjaxUploadForm(form));
}

document.addEventListener('DOMContentLoaded', bootCourseMediaUploaders);


document.addEventListener('DOMContentLoaded', () => {
    bootPanel();

    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.noSubmitLock === 'true') return;

        form.addEventListener('submit', (event) => {
            const submitter = event.submitter;

            if (submitter?.dataset.confirm && !window.confirm(submitter.dataset.confirm)) {
                event.preventDefault();
                return;
            }

            if (form.dataset.submitted === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitted = 'true';

            if (submitter) {
                submitter.disabled = true;
                submitter.setAttribute('aria-busy', 'true');
                submitter.dataset.originalText = submitter.textContent;
                submitter.textContent = 'در حال ثبت...';
            }
        });
    });

    document.querySelectorAll('[data-classroom-select]').forEach((classroomSelect) => {
        const courseSelect = document.getElementById(classroomSelect.dataset.courseSelect);
        if (!courseSelect) return;

        const syncClassrooms = () => {
            const selectedCourse = courseSelect.value;
            let visible = 0;

            classroomSelect.querySelectorAll('option[data-course-id]').forEach((option) => {
                const matches = option.dataset.courseId === selectedCourse;
                option.hidden = !matches;
                option.disabled = !matches;

                if (matches) visible += 1;
            });

            const selected = classroomSelect.selectedOptions[0];
            if (selected?.disabled) classroomSelect.value = '';

            classroomSelect
                .closest('form')
                ?.querySelector('[data-no-classrooms]')
                ?.toggleAttribute('hidden', visible > 0);
        };

        courseSelect.addEventListener('change', syncClassrooms);
        syncClassrooms();
    });
});

window.addEventListener('pageshow', () => {
    document.querySelectorAll('form[data-submitted="true"]').forEach((form) => {
        form.dataset.submitted = 'false';

        form.querySelectorAll('button[aria-busy="true"]').forEach((button) => {
            button.disabled = false;
            button.removeAttribute('aria-busy');

            if (button.dataset.originalText) {
                button.textContent = button.dataset.originalText;
            }
        });
    });
});

Alpine.start();