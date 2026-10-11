import { bootPanel } from './panel-base.js';

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

        const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
        const maxBytes = maxSizes[extension] || 0;

        if (!maxBytes) {
            input.value = '';
            if (preview) preview.hidden = true;
            setStatus('فرمت فایل پشتیبانی نمی‌شود.', 'error');
            resetProgress();
            return;
        }

        if (file.size > maxBytes) {
            input.value = '';
            if (preview) preview.hidden = true;
            setStatus(`حجم فایل ${formatUploadSize(file.size)} است؛ حداکثر مجاز ${formatUploadSize(maxBytes)} است.`, 'error');
            resetProgress();
            return;
        }

        form.dataset.maxBytes = String(maxBytes);

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


function bootBannerEditors() {
    const supportedMimes = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);

    const formatSize = (bytes) => {
        const units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        let value = Math.max(0, Number(bytes) || 0);
        let index = 0;
        while (value >= 1024 && index < units.length - 1) {
            value /= 1024;
            index += 1;
        }
        return new Intl.NumberFormat('fa-IR', { maximumFractionDigits: index ? 1 : 0 }).format(value) + ' ' + units[index];
    };

    document.querySelectorAll('[data-banner-slot]').forEach((slot) => {
        if (slot.dataset.bannerEditorBound === 'true') return;
        slot.dataset.bannerEditorBound = 'true';

        const preview = slot.querySelector('[data-banner-preview]');
        const empty = slot.querySelector('[data-banner-preview-empty]');
        const file = slot.querySelector('[data-banner-file]');
        const select = slot.querySelector('[data-banner-media-select]');
        const x = slot.querySelector('[data-crop-x]');
        const y = slot.querySelector('[data-crop-y]');
        const xOutput = slot.querySelector('[data-crop-x-output]');
        const yOutput = slot.querySelector('[data-crop-y-output]');
        const reset = slot.querySelector('[data-banner-reset]');
        const fileMeta = slot.querySelector('[data-banner-file-meta]');
        const fileName = slot.querySelector('[data-banner-file-name]');
        const fileSize = slot.querySelector('[data-banner-file-size]');
        const fileDimensions = slot.querySelector('[data-banner-file-dimensions]');
        const fileFeedback = slot.querySelector('[data-banner-file-feedback]');
        const previewDimensions = slot.querySelector('[data-banner-preview-dimensions]');
        const titleInput = slot.querySelector('[data-banner-title-input]');
        const descriptionInput = slot.querySelector('[data-banner-description-input]');
        const ctaLabelInput = slot.querySelector('[data-banner-cta-label-input]');
        const ctaUrlInput = slot.querySelector('[data-banner-cta-url-input]');
        const previewTitle = slot.querySelector('[data-banner-preview-title]');
        const previewDescription = slot.querySelector('[data-banner-preview-description]');
        const previewCta = slot.querySelector('[data-banner-preview-cta]');
        const previewCtaLabel = slot.querySelector('[data-banner-preview-cta-label]');
        const ctaUrlFeedback = slot.querySelector('[data-banner-cta-url-feedback]');
        const initialSource = preview?.dataset.bannerInitialImage || '';
        let objectUrl = null;

        if (!preview || !x || !y) return;

        const imageElement = () => slot.querySelector('[data-banner-preview-image]');
        const releaseObjectUrl = () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        };
        const setFileFeedback = (message, kind = 'normal') => {
            if (!fileFeedback) return;
            fileFeedback.textContent = message;
            fileFeedback.classList.toggle('is-error', kind === 'error');
            fileFeedback.classList.toggle('is-warning', kind === 'warning');
            fileFeedback.setAttribute('role', kind === 'error' ? 'alert' : 'status');
        };

        const setPosition = () => {
            const xValue = Math.max(0, Math.min(100, Number(x.value || 50)));
            const yValue = Math.max(0, Math.min(100, Number(y.value || 50)));
            const image = imageElement();
            if (image) image.style.objectPosition = xValue + '% ' + yValue + '%';
            if (xOutput) xOutput.textContent = xValue + '٪';
            if (yOutput) yOutput.textContent = yValue + '٪';
        };

        const updateDimensions = (image, selectedFile = null) => {
            const width = Number(image.naturalWidth || 0);
            const height = Number(image.naturalHeight || 0);
            if (!width || !height) return;

            const ratio = width / height;
            const ratioLabel = ratio.toFixed(2) + ':1';
            const cropHint = Math.abs(ratio - (16 / 9)) / (16 / 9) <= 0.05
                ? 'نسبت تصویر با قاب ۱۶:۹ هماهنگ است.'
                : ratio > (16 / 9)
                    ? 'در قاب نهایی بخشی از کناره‌های تصویر بریده می‌شود.'
                    : 'در قاب نهایی بخشی از بالا و پایین تصویر بریده می‌شود.';
            const recommendedWidth = Number(file?.dataset.recommendedWidth || 1600);
            const recommendedHeight = Number(file?.dataset.recommendedHeight || 900);
            const isLowResolution = width < recommendedWidth || height < recommendedHeight;
            const resolutionHint = isLowResolution
                ? ' وضوح کمتر از پیشنهاد ' + recommendedWidth.toLocaleString('fa-IR') + '×' + recommendedHeight.toLocaleString('fa-IR') + ' پیکسل است.'
                : '';
            const dimensionsText = 'ابعاد ' + width.toLocaleString('fa-IR') + '×' + height.toLocaleString('fa-IR') +
                ' پیکسل · نسبت ' + ratioLabel + '. ' + cropHint + resolutionHint;

            if (previewDimensions) previewDimensions.textContent = dimensionsText;
            if (fileDimensions && selectedFile) {
                fileDimensions.textContent = width.toLocaleString('fa-IR') + '×' + height.toLocaleString('fa-IR') + ' پیکسل · ' + ratioLabel;
            }
            if (selectedFile) {
                setFileFeedback(
                    'پیش‌نمایش آماده است. ' + cropHint + resolutionHint + ' قاب و متن را بررسی کن.',
                    isLowResolution ? 'warning' : 'normal'
                );
            }
        };

        const showEmpty = () => {
            imageElement()?.remove();
            if (empty) empty.hidden = false;
            if (previewDimensions) previewDimensions.textContent = 'پس از انتخاب تصویر، ابعاد واقعی و برش احتمالی نمایش داده می‌شود.';
        };

        const showImage = (src, selectedFile = null) => {
            let image = imageElement();
            if (!image) {
                image = document.createElement('img');
                image.alt = '';
                image.setAttribute('data-banner-preview-image', '');
                preview.prepend(image);
            }

            image.hidden = false;
            image.style.objectFit = 'cover';
            image.style.width = '100%';
            image.style.height = '100%';
            image.style.objectPosition = Math.max(0, Math.min(100, Number(x.value || 50))) + '% ' +
                Math.max(0, Math.min(100, Number(y.value || 50))) + '%';
            image.onload = () => updateDimensions(image, selectedFile);
            image.onerror = () => {
                if (selectedFile && file) {
                    file.value = '';
                    releaseObjectUrl();
                    if (fileMeta) fileMeta.hidden = true;
                    setFileFeedback('مرورگر نتوانست تصویر را باز کند؛ فایل دیگری انتخاب کن.', 'error');
                    restorePriorImage();
                } else if (previewDimensions) {
                    previewDimensions.textContent = 'این تصویر بارگذاری نشد؛ URL یا فایل را بررسی کن.';
                }
            };
            if (image.src !== src) image.src = src;
            if (empty) empty.hidden = true;
            setPosition();
            if (image.complete && image.naturalWidth > 0) updateDimensions(image, selectedFile);
            return image;
        };

        const restorePriorImage = () => {
            const selectedSource = select?.selectedOptions?.[0]?.dataset?.mediaUrl || initialSource;
            if (selectedSource) showImage(selectedSource);
            else showEmpty();
        };

        const updatePreviewCopy = () => {
            const title = titleInput?.value?.trim() || '';
            const description = descriptionInput?.value?.trim() || '';
            const ctaLabel = ctaLabelInput?.value?.trim() || '';
            const ctaUrl = ctaUrlInput?.value?.trim() || '';
            const validUrl = /^(https?:\/\/|\/|#)/i.test(ctaUrl);

            if (previewTitle) {
                previewTitle.textContent = title;
                previewTitle.hidden = !title;
            }
            if (previewDescription) {
                previewDescription.textContent = description;
                previewDescription.hidden = !description;
            }
            if (previewCtaLabel) previewCtaLabel.textContent = ctaLabel;
            if (previewCta) previewCta.hidden = !(ctaLabel && ctaUrl && validUrl);

            if (ctaUrlFeedback) {
                ctaUrlFeedback.classList.remove('is-error');
                if (ctaUrl && !validUrl) {
                    ctaUrlFeedback.textContent = 'لینک باید با /، # یا https:// شروع شود.';
                    ctaUrlFeedback.classList.add('is-error');
                    ctaUrlFeedback.setAttribute('role', 'alert');
                } else if (ctaLabel && !ctaUrl) {
                    ctaUrlFeedback.textContent = 'تا وقتی لینک وارد نشود، دکمه در سایت نمایش داده نمی‌شود.';
                    ctaUrlFeedback.setAttribute('role', 'status');
                } else {
                    ctaUrlFeedback.textContent = '';
                    ctaUrlFeedback.setAttribute('role', 'status');
                }
            }
        };

        x.addEventListener('input', setPosition);
        y.addEventListener('input', setPosition);
        titleInput?.addEventListener('input', updatePreviewCopy);
        descriptionInput?.addEventListener('input', updatePreviewCopy);
        ctaLabelInput?.addEventListener('input', updatePreviewCopy);
        ctaUrlInput?.addEventListener('input', updatePreviewCopy);

        reset?.addEventListener('click', () => {
            x.value = '50';
            y.value = '50';
            setPosition();
        });

        select?.addEventListener('change', () => {
            releaseObjectUrl();
            if (file) file.value = '';
            if (fileMeta) fileMeta.hidden = true;
            setFileFeedback('');

            const src = select.selectedOptions?.[0]?.dataset?.mediaUrl || initialSource;
            if (src) {
                showImage(src);
                setFileFeedback('این تصویر با همان قاب ۱۶:۹ کارت عمومی نمایش داده می‌شود.');
            } else {
                showEmpty();
            }
        });

        file?.addEventListener('change', () => {
            const selectedFile = file.files?.[0];
            if (!selectedFile) return;

            const extension = (selectedFile.name.split('.').pop() || '').toLowerCase();
            const allowedExtensions = new Set(
                (file.dataset.allowedExtensions || 'jpg,jpeg,png,webp,avif')
                    .split(',')
                    .map((value) => value.trim().toLowerCase())
                    .filter(Boolean)
            );
            const extensionAllowed = allowedExtensions.has(extension);
            const mime = (selectedFile.type || '').toLowerCase();
            const mimeAllowed = mime ? supportedMimes.has(mime) : extensionAllowed;
            const maxSizeMb = Number(file.dataset.maxSizeMb || 5);

            if (!extensionAllowed || !mimeAllowed) {
                file.value = '';
                releaseObjectUrl();
                if (fileMeta) fileMeta.hidden = true;
                setFileFeedback('فرمت تصویر پشتیبانی نمی‌شود؛ JPG، PNG، WEBP یا AVIF انتخاب کن.', 'error');
                restorePriorImage();
                return;
            }
            if (selectedFile.size > maxSizeMb * 1024 * 1024 || selectedFile.size <= 0) {
                file.value = '';
                releaseObjectUrl();
                if (fileMeta) fileMeta.hidden = true;
                setFileFeedback(
                    selectedFile.size <= 0 ? 'فایل انتخاب‌شده خالی است.' : 'حجم فایل بیشتر از ' + maxSizeMb + ' مگابایت است.',
                    'error'
                );
                restorePriorImage();
                return;
            }

            releaseObjectUrl();
            objectUrl = URL.createObjectURL(selectedFile);
            if (fileMeta) fileMeta.hidden = false;
            if (fileName) fileName.textContent = selectedFile.name;
            if (fileSize) fileSize.textContent = formatSize(selectedFile.size);
            if (fileDimensions) fileDimensions.textContent = 'در حال خواندن ابعاد تصویر…';
            if (select) select.value = '';
            setFileFeedback('این فایل در صورت ذخیره، تصویر بنر را جایگزین می‌کند.');

            showImage(objectUrl, selectedFile);
        });

        const firstSource = select?.selectedOptions?.[0]?.dataset?.mediaUrl || initialSource;
        if (firstSource) showImage(firstSource);
        else showEmpty();
        setPosition();
        updatePreviewCopy();
        window.addEventListener('pagehide', releaseObjectUrl, { once: true });
    });
}
document.addEventListener('DOMContentLoaded', () => {
    bootPanel();
    bootBannerEditors();

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


function bootOwnerCoverPreviews() {
    const formatSize = (bytes) => {
        const units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        let value = Math.max(0, Number(bytes) || 0);
        let unit = 0;
        while (value >= 1024 && unit < units.length - 1) {
            value /= 1024;
            unit += 1;
        }
        return new Intl.NumberFormat('fa-IR', { maximumFractionDigits: unit ? 1 : 0 }).format(value) + ' ' + units[unit];
    };

    document.querySelectorAll('[data-owner-cover-editor]').forEach((editor) => {
        if (editor.dataset.ownerCoverPreviewBound === 'true') return;
        editor.dataset.ownerCoverPreviewBound = 'true';

        const input = editor.querySelector('[data-owner-cover-input]');
        const frame = editor.querySelector('.owner-cover-preview');
        const feedback = editor.querySelector('[data-owner-cover-feedback]');
        if (!input || !frame) return;

        let preview = frame.querySelector('[data-owner-cover-preview]');
        const empty = frame.querySelector('[data-owner-cover-empty]');
        const initialSource = preview?.getAttribute('src') || '';
        let objectUrl = null;

        const setFeedback = (message, kind = 'normal') => {
            if (!feedback) return;
            feedback.textContent = message;
            feedback.classList.toggle('is-error', kind === 'error');
            feedback.classList.toggle('is-warning', kind === 'warning');
            feedback.setAttribute('role', kind === 'error' ? 'alert' : 'status');
        };

        const releaseObjectUrl = () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        };

        const restoreInitialPreview = () => {
            if (initialSource) {
                if (!preview) {
                    preview = document.createElement('img');
                    preview.alt = '';
                    preview.dataset.ownerCoverPreview = '1';
                    frame.appendChild(preview);
                }
                preview.hidden = false;
                preview.src = initialSource;
                if (empty) empty.hidden = true;
            } else {
                if (preview) preview.hidden = true;
                if (empty) empty.hidden = false;
            }
        };

        const describeDimensions = (image, file) => {
            const width = Number(image.naturalWidth || 0);
            const height = Number(image.naturalHeight || 0);
            if (!width || !height) return;

            const expectedRatio = Number(editor.dataset.previewRatio || (16 / 10));
            const actualRatio = width / height;
            const recommendedWidth = Number(input.dataset.recommendedWidth || 1200);
            const recommendedHeight = Number(input.dataset.recommendedHeight || Math.round(recommendedWidth / expectedRatio));
            const cropHint = Math.abs(actualRatio - expectedRatio) / expectedRatio <= 0.05
                ? 'نسبت تصویر با قاب نهایی هماهنگ است.'
                : (actualRatio > expectedRatio
                    ? 'برای پرشدن قاب، بخشی از کناره‌های تصویر دیده نمی‌شود.'
                    : 'برای پرشدن قاب، بخشی از بالا و پایین تصویر دیده نمی‌شود.');
            const lowResolution = width < recommendedWidth || height < recommendedHeight;
            const resolutionHint = lowResolution
                ? ' وضوح فایل کمتر از پیشنهاد ' + recommendedWidth.toLocaleString('fa-IR') + '×' + recommendedHeight.toLocaleString('fa-IR') + ' است.'
                : '';
            const fileInfo = file ? ' فایل: ' + formatSize(file.size) + '.' : '';
            setFeedback(
                width.toLocaleString('fa-IR') + '×' + height.toLocaleString('fa-IR') + ' پیکسل · ' + cropHint + resolutionHint + ' فایل اصلی تغییر نمی‌کند.' + fileInfo,
                lowResolution ? 'warning' : 'normal'
            );
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;

            const extension = (file.name.split('.').pop() || '').toLowerCase();
            const allowedExtensions = new Set(
                (input.dataset.allowedExtensions || 'jpg,jpeg,png,webp')
                    .split(',')
                    .map((value) => value.trim().toLowerCase())
                    .filter(Boolean)
            );
            const type = (file.type || '').toLowerCase();
            const allowedTypes = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
            const validType = allowedExtensions.has(extension) && (type ? allowedTypes.has(type) : true);
            const maxSizeMb = Number(input.dataset.maxSizeMb || 10);

            if (!validType) {
                input.value = '';
                releaseObjectUrl();
                restoreInitialPreview();
                setFeedback('فرمت تصویر پشتیبانی نمی‌شود. یکی از فرمت‌های مجاز این فرم را انتخاب کن.', 'error');
                return;
            }

            if (file.size > maxSizeMb * 1024 * 1024) {
                input.value = '';
                releaseObjectUrl();
                restoreInitialPreview();
                setFeedback('حجم فایل بیشتر از ' + maxSizeMb + ' مگابایت است؛ فایل کوچک‌تری انتخاب کن.', 'error');
                return;
            }

            releaseObjectUrl();
            objectUrl = URL.createObjectURL(file);

            if (!preview) {
                preview = document.createElement('img');
                preview.alt = '';
                preview.dataset.ownerCoverPreview = '1';
                frame.appendChild(preview);
            }

            preview.hidden = false;
            preview.onerror = () => {
                if (preview?.src !== objectUrl) return;
                input.value = '';
                releaseObjectUrl();
                restoreInitialPreview();
                setFeedback('پیش‌نمایش این تصویر باز نشد؛ لطفاً فایل دیگری انتخاب کن.', 'error');
            };
            preview.onload = () => {
                if (preview?.src !== objectUrl) return;
                if (empty) empty.hidden = true;
                describeDimensions(preview, file);
            };
            preview.src = objectUrl;
            if (empty) empty.hidden = true;
            setFeedback('در حال بررسی تصویر و تطبیق با قاب عمومی…');
        });

        if (preview) {
            const hydrate = () => {
                if (!preview?.naturalWidth || !preview?.naturalHeight) return;
                describeDimensions(preview, null);
            };
            if (preview.complete) hydrate();
            else preview.addEventListener('load', hydrate, { once: true });
        }

        window.addEventListener('pagehide', releaseObjectUrl, { once: true });
    });
}
document.addEventListener('DOMContentLoaded', bootOwnerCoverPreviews);


function bootSeoOgPreview() {
    document.querySelectorAll('[data-seo-og-preview]').forEach((preview) => {
        const imageInput = document.querySelector('[data-seo-og-image-input]');
        const titleInput = document.querySelector('[data-seo-og-title-input]');
        const descriptionInput = document.querySelector('[data-seo-og-description-input]');
        const seoTitleInput = document.querySelector('[data-seo-title-input]');
        const seoDescriptionInput = document.querySelector('[data-seo-description-input]');
        const imageWrap = preview.querySelector('[data-seo-og-image-wrap]');
        const image = preview.querySelector('[data-seo-og-preview-image]');
        const empty = preview.querySelector('[data-seo-og-preview-empty]');
        const title = preview.querySelector('[data-seo-og-preview-title]');
        const description = preview.querySelector('[data-seo-og-preview-description]');
        const status = preview.querySelector('[data-seo-og-preview-status]');
        if (!imageInput || !imageWrap) return;

        let previewTimer = null;
        let lastRequestedUrl = '';

        const setStatus = (message, isError = false) => {
            if (!status) return;
            status.textContent = message;
            status.classList.toggle('is-error', isError);
            status.setAttribute('role', isError ? 'alert' : 'status');
        };

        const syncText = () => {
            const ogTitle = titleInput?.value?.trim() || seoTitleInput?.value?.trim() || '';
            const ogDescription = descriptionInput?.value?.trim() || seoDescriptionInput?.value?.trim() || '';
            if (title) title.textContent = ogTitle || 'عنوان صفحه';
            if (description) description.textContent = ogDescription || 'توضیحات صفحه هنگام اشتراک‌گذاری در این بخش نمایش داده می‌شود.';
        };

        const describeLoadedImage = () => {
            const width = Number(image?.naturalWidth || 0);
            const height = Number(image?.naturalHeight || 0);
            if (!width || !height) {
                setStatus('پیش‌نمایش تصویر بارگذاری شد؛ نسبت پیشنهادی ۱۲۰۰×۶۳۰ پیکسل است.');
                return;
            }

            const targetRatio = 1200 / 630;
            const actualRatio = width / height;
            const cropHint = Math.abs(actualRatio - targetRatio) / targetRatio <= 0.05
                ? 'نسبت تصویر با قاب OG پیشنهادی هماهنگ است.'
                : actualRatio > targetRatio
                    ? 'در قاب نمونه، بخشی از کناره‌های تصویر بریده می‌شود.'
                    : 'در قاب نمونه، بخشی از بالا و پایین تصویر بریده می‌شود.';
            const resolutionHint = width < 1200 || height < 630
                ? ' وضوح تصویر کمتر از پیشنهاد ۱۲۰۰×۶۳۰ است.'
                : '';
            setStatus(
                'ابعاد واقعی: ' + width.toLocaleString('fa-IR') + '×' + height.toLocaleString('fa-IR') +
                ' پیکسل. ' + cropHint + resolutionHint +
                ' پلتفرم‌های اجتماعی ممکن است پیش‌نمایش را کمی متفاوت نمایش دهند.'
            );
        };

        const syncImage = () => {
            const value = imageInput.value.trim();
            if (!value) {
                if (image) {
                    image.hidden = true;
                    image.removeAttribute('src');
                }
                if (empty) empty.hidden = false;
                setStatus('نشانی تصویر اشتراک‌گذاری ثبت نشده است.');
                return;
            }

            let parsed;
            try {
                parsed = new URL(value);
            } catch {
                if (image) image.hidden = true;
                if (empty) empty.hidden = false;
                setStatus('یک نشانی کامل و عمومی با https:// وارد کن.', true);
                return;
            }

            if (!['http:', 'https:'].includes(parsed.protocol)) {
                if (image) image.hidden = true;
                if (empty) empty.hidden = false;
                setStatus('تصویر اشتراک‌گذاری باید نشانی HTTP یا HTTPS داشته باشد.', true);
                return;
            }

            lastRequestedUrl = parsed.href;
            if (image) {
                image.onload = () => {
                    if (image.src !== lastRequestedUrl) return;
                    image.hidden = false;
                    if (empty) empty.hidden = true;
                    describeLoadedImage();
                };
                image.onerror = () => {
                    if (image.src !== lastRequestedUrl) return;
                    image.hidden = true;
                    if (empty) empty.hidden = false;
                    setStatus('تصویر از این نشانی دریافت نشد؛ مطمئن شو URL عمومی و قابل‌دسترسی است.', true);
                };
                if (image.src !== lastRequestedUrl) image.src = lastRequestedUrl;
                else if (image.complete && image.naturalWidth > 0) {
                    image.hidden = false;
                    if (empty) empty.hidden = true;
                    describeLoadedImage();
                }
            }
        };

        const scheduleImageSync = () => {
            window.clearTimeout(previewTimer);
            previewTimer = window.setTimeout(syncImage, 250);
        };

        imageInput.addEventListener('input', scheduleImageSync);
        titleInput?.addEventListener('input', syncText);
        descriptionInput?.addEventListener('input', syncText);
        seoTitleInput?.addEventListener('input', syncText);
        seoDescriptionInput?.addEventListener('input', syncText);
        syncText();
        syncImage();
    });
}
document.addEventListener('DOMContentLoaded', bootSeoOgPreview);


function bootOwnerResourcePreview() {
    const allowedExtensions = new Set([
        'pdf', 'mp4', 'webm', 'mov', 'mp3', 'm4a', 'jpg', 'jpeg', 'png',
        'webp', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'zip',
    ]);

    const formatSize = (bytes) => {
        const units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        let value = Math.max(0, Number(bytes) || 0);
        let index = 0;
        while (value >= 1024 && index < units.length - 1) {
            value /= 1024;
            index += 1;
        }
        return new Intl.NumberFormat('fa-IR', { maximumFractionDigits: index ? 1 : 0 }).format(value) + ' ' + units[index];
    };

    document.querySelectorAll('[data-owner-resource-input]').forEach((input) => {
        const form = input.closest('form');
        const preview = form?.querySelector('[data-owner-resource-preview]');
        const image = preview?.querySelector('[data-owner-resource-image]');
        const video = preview?.querySelector('[data-owner-resource-video]');
        const audio = preview?.querySelector('[data-owner-resource-audio]');
        const generic = preview?.querySelector('[data-owner-resource-generic]');
        const name = preview?.querySelector('[data-owner-resource-name]');
        const size = preview?.querySelector('[data-owner-resource-size]');
        const dimensions = preview?.querySelector('[data-owner-resource-dimensions]');
        const feedback = form?.querySelector('[data-owner-resource-feedback]');
        if (!preview) return;

        let objectUrl = null;
        const release = () => {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }
        };
        const resetPreview = () => {
            preview.hidden = true;
            [image, video, audio].forEach((element) => {
                if (!element) return;
                element.hidden = true;
                element.removeAttribute('src');
                element.load?.();
            });
            if (generic) generic.hidden = false;
            if (name) name.textContent = '';
            if (size) size.textContent = '';
            if (dimensions) dimensions.textContent = '';
        };
        const setFeedback = (message, isError = false) => {
            if (!feedback) return;
            feedback.textContent = message;
            feedback.classList.toggle('is-error', isError);
            feedback.classList.toggle('is-warning', false);
            feedback.setAttribute('role', isError ? 'alert' : 'status');
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            release();
            resetPreview();
            if (!file) {
                setFeedback('هنوز فایلی انتخاب نشده است.');
                return;
            }

            const extension = (file.name.split('.').pop() || '').toLowerCase();
            if (!allowedExtensions.has(extension)) {
                input.value = '';
                setFeedback('پسوند این فایل در این بخش پشتیبانی نمی‌شود.', true);
                return;
            }
            if (file.size <= 0 || file.size > 200 * 1024 * 1024) {
                input.value = '';
                setFeedback(file.size <= 0 ? 'فایل انتخاب‌شده خالی است.' : 'حجم فایل بیشتر از ۲۰۰ مگابایت است.', true);
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.hidden = false;
            if (name) name.textContent = file.name;
            if (size) size.textContent = formatSize(file.size);
            if (generic) generic.hidden = false;

            if (file.type.startsWith('image/') && image) {
                image.hidden = false;
                image.onload = () => {
                    if (image.naturalWidth && image.naturalHeight && dimensions) {
                        dimensions.textContent = image.naturalWidth.toLocaleString('fa-IR') + '×' +
                            image.naturalHeight.toLocaleString('fa-IR') + ' پیکسل · تصویر کامل نمایش داده می‌شود؛ این آپلودر برش عمومی اعمال نمی‌کند.';
                    }
                    if (generic) generic.hidden = true;
                    setFeedback('تصویر خوانده شد. این فایل در بخش منابع خصوصی قرار می‌گیرد، نه به‌عنوان بنر یا کاور عمومی.');
                };
                image.onerror = () => {
                    image.hidden = true;
                    if (generic) generic.hidden = false;
                    setFeedback('مرورگر نتوانست پیش‌نمایش تصویر را باز کند؛ فایل را بررسی کن.', true);
                };
                image.src = objectUrl;
            } else if (file.type.startsWith('video/') && video) {
                video.hidden = false;
                video.src = objectUrl;
                if (generic) generic.hidden = true;
                setFeedback('ویدئو آمادهٔ پیش‌نمایش است؛ ذخیره نهایی همچنان سمت سرور اعتبارسنجی می‌شود.');
            } else if (file.type.startsWith('audio/') && audio) {
                audio.hidden = false;
                audio.src = objectUrl;
                if (generic) generic.hidden = true;
                setFeedback('صوت آمادهٔ پیش‌نمایش است؛ برای فرمت‌های اداری و PDF نام و حجم فایل نمایش داده می‌شود.');
            } else {
                setFeedback('فایل انتخاب شد. پیش‌نمایش تصویری برای این نوع فایل در دسترس نیست.');
            }
        });

        window.addEventListener('pagehide', release, { once: true });
    });
}
document.addEventListener('DOMContentLoaded', bootOwnerResourcePreview);



// Responsive public homepage hero editor: preview locally before upload and keep
// desktop/mobile focal points independent without introducing another dependency.
function bootHomeHeroEditor() {
    const form = document.querySelector('[data-home-hero-editor]');
    if (!form || form.dataset.homeHeroEditorBound === 'true') return;
    form.dataset.homeHeroEditorBound = 'true';

    const desktopInput = form.querySelector('[data-home-hero-file="desktop"]');
    const mobileInput = form.querySelector('[data-home-hero-file="mobile"]');
    const desktopPreview = form.querySelector('[data-home-hero-preview-desktop]');
    const mobilePreview = form.querySelector('[data-home-hero-preview-mobile]');
    const removeMobile = form.querySelector('[data-home-hero-remove-mobile]');
    const restoreDefault = form.querySelector('[data-home-hero-restore]');
    const submit = form.querySelector('[data-home-hero-submit]');
    const initialDesktop = form.dataset.currentDesktop || '';
    const initialMobile = form.dataset.currentMobile || '';
    const defaultImage = form.dataset.defaultImage || initialDesktop;
    const fallbackImage = form.dataset.fallbackImage || '';
    const currentSources = { desktop: initialDesktop, mobile: initialMobile };
    const objectUrls = { desktop: null, mobile: null };
    const acceptedTypes = new Set(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);

    if (!desktopInput || !mobileInput || !desktopPreview || !mobilePreview) return;

    const feedbackFor = (kind) => form.querySelector('[data-home-hero-feedback="' + kind + '"]');
    const setFeedback = (kind, message, tone = '') => {
        const output = feedbackFor(kind);
        if (!output) return;
        output.textContent = message;
        output.classList.toggle('is-error', tone === 'error');
        output.classList.toggle('is-warning', tone === 'warning');
        output.setAttribute('role', tone === 'error' ? 'alert' : 'status');
    };
    const releaseObjectUrl = (kind) => {
        if (!objectUrls[kind]) return;
        URL.revokeObjectURL(objectUrls[kind]);
        objectUrls[kind] = null;
    };
    const setPreviewSource = (image, src) => {
        if (!image) return;
        image.onerror = () => {
            if (fallbackImage && image.src !== fallbackImage) image.src = fallbackImage;
        };
        image.src = src || fallbackImage;
    };
    const selectedDesktop = () => objectUrls.desktop || currentSources.desktop || defaultImage;
    const selectedMobile = () => {
        if (objectUrls.mobile) return objectUrls.mobile;
        if (removeMobile?.checked) return selectedDesktop();
        return currentSources.mobile || selectedDesktop();
    };
    const updatePositions = () => {
        const values = {};
        form.querySelectorAll('[data-home-hero-range]').forEach((input) => {
            const value = Math.max(0, Math.min(100, Number(input.value || 0)));
            values[input.dataset.homeHeroRange] = value;
            const output = form.querySelector('[data-home-hero-range-output="' + input.dataset.homeHeroRange + '"]');
            if (output) output.textContent = value.toLocaleString('fa-IR') + '٪';
        });
        desktopPreview.style.objectPosition = (values.crop_x ?? 50) + '% ' + (values.crop_y ?? 46) + '%';
        mobilePreview.style.objectPosition = (values.mobile_crop_x ?? 54) + '% ' + (values.mobile_crop_y ?? 50) + '%';
    };
    const refreshPreviews = () => {
        setPreviewSource(desktopPreview, restoreDefault?.checked ? defaultImage : selectedDesktop());
        setPreviewSource(mobilePreview, restoreDefault?.checked ? defaultImage : selectedMobile());
        const mobileInfo = form.querySelector('[data-home-hero-mobile-info]');
        if (mobileInfo) {
            mobileInfo.textContent = restoreDefault?.checked
                ? 'تصویر پیش‌فرض در هر دو قاب استفاده می‌شود.'
                : objectUrls.mobile
                    ? 'پیش‌نمایش فایل جدید موبایل؛ هنوز روی سایت منتشر نشده است.'
                    : currentSources.mobile && !removeMobile?.checked
                        ? 'تصویر اختصاصی موبایل فعال است.'
                        : 'در موبایل همان تصویر اصلی با قاب‌بندی جداگانه استفاده می‌شود.';
        }
    };
    const syncRestoreState = () => {
        const restoring = Boolean(restoreDefault?.checked);
        [desktopInput, mobileInput].forEach((input) => { input.disabled = restoring; });
        form.querySelectorAll('[data-home-hero-range]').forEach((input) => { input.disabled = restoring; });
        if (removeMobile) removeMobile.disabled = restoring;
        const desktopInfo = form.querySelector('[data-home-hero-desktop-info]');
        if (desktopInfo) desktopInfo.textContent = restoring
            ? 'تصویر پیش‌فرض در صورت ذخیره جایگزین می‌شود.'
            : 'تصویر فعلی در کادر عریض نمایش داده می‌شود.';
        refreshPreviews();
        updatePositions();
    };
    const resetInputPreview = (kind) => {
        releaseObjectUrl(kind);
        if (kind === 'desktop') {
            setPreviewSource(desktopPreview, currentSources.desktop || defaultImage);
        } else {
            setPreviewSource(mobilePreview, currentSources.mobile || selectedDesktop());
        }
        refreshPreviews();
    };
    const bindFile = (input, kind) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                resetInputPreview(kind);
                setFeedback(kind, '');
                return;
            }
            const ext = (file.name.split('.').pop() || '').toLowerCase();
            const allowedExtensions = new Set(['jpg', 'jpeg', 'png', 'webp', 'avif']);
            const maxBytes = Number(input.dataset.maxSizeMb || (kind === 'desktop' ? 8 : 5)) * 1024 * 1024;
            if (!allowedExtensions.has(ext) || (file.type && !acceptedTypes.has(file.type))) {
                input.value = '';
                resetInputPreview(kind);
                setFeedback(kind, 'فرمت معتبر نیست؛ JPG، PNG، WEBP یا AVIF انتخاب کن.', 'error');
                return;
            }
            if (!file.size || file.size > maxBytes) {
                input.value = '';
                resetInputPreview(kind);
                setFeedback(kind, file.size > maxBytes
                    ? 'حجم فایل از ' + input.dataset.maxSizeMb + ' مگابایت بیشتر است.'
                    : 'فایل انتخاب‌شده خالی است.', 'error');
                return;
            }

            releaseObjectUrl(kind);
            const url = URL.createObjectURL(file);
            const probe = new Image();
            probe.onload = () => {
                const minWidth = Number(input.dataset.minWidth || 1);
                const minHeight = Number(input.dataset.minHeight || 1);
                if (probe.naturalWidth < minWidth || probe.naturalHeight < minHeight) {
                    URL.revokeObjectURL(url);
                    input.value = '';
                    resetInputPreview(kind);
                    setFeedback(kind,
                        'ابعاد تصویر ' + probe.naturalWidth.toLocaleString('fa-IR') + '×' +
                        probe.naturalHeight.toLocaleString('fa-IR') + ' است؛ حداقل ' +
                        minWidth.toLocaleString('fa-IR') + '×' + minHeight.toLocaleString('fa-IR') + ' لازم است.',
                        'error'
                    );
                    return;
                }

                objectUrls[kind] = url;
                refreshPreviews();
                const imageRatio = probe.naturalWidth / probe.naturalHeight;
                const targetRatio = kind === 'mobile' ? (9 / 16) : 2;
                const cropHint = Math.abs(imageRatio - targetRatio) / targetRatio < .12
                    ? 'نسبت تصویر با قاب ' + (kind === 'mobile' ? 'عمودی موبایل' : 'عریض دسکتاپ') + ' هماهنگ است.'
                    : 'نسبت تصویر با قاب نمایش متفاوت است؛ با تنظیم نقطه تمرکز، برش نمایشی را کنترل کن.';
                setFeedback(kind,
                    'پیش‌نمایش آماده است · ' +
                    probe.naturalWidth.toLocaleString('fa-IR') + '×' + probe.naturalHeight.toLocaleString('fa-IR') +
                    ' پیکسل · ' + formatUploadSize(file.size) + ' · ' + cropHint,
                    'warning'
                );
            };
            probe.onerror = () => {
                URL.revokeObjectURL(url);
                input.value = '';
                resetInputPreview(kind);
                setFeedback(kind, 'مرورگر نتوانست تصویر را باز کند؛ فایل دیگری انتخاب کن.', 'error');
            };
            probe.src = url;
        });
    };

    bindFile(desktopInput, 'desktop');
    bindFile(mobileInput, 'mobile');
    form.querySelectorAll('[data-home-hero-range]').forEach((input) => {
        input.addEventListener('input', () => {
            updatePositions();
        });
    });
    form.querySelectorAll('[data-home-hero-reset-crop]').forEach((button) => {
        button.addEventListener('click', () => {
            const mobile = button.dataset.homeHeroResetCrop === 'mobile';
            const defaults = mobile
                ? { mobile_crop_x: 54, mobile_crop_y: 50 }
                : { crop_x: 50, crop_y: 46 };
            Object.entries(defaults).forEach(([name, value]) => {
                const input = form.querySelector('[data-home-hero-range="' + name + '"]');
                if (input) input.value = String(value);
            });
            updatePositions();
        });
    });
    removeMobile?.addEventListener('change', () => {
        if (removeMobile.checked) {
            releaseObjectUrl('mobile');
            mobileInput.value = '';
            setFeedback('mobile', 'پس از ذخیره، تصویر موبایل حذف می‌شود و قاب‌بندی از تصویر اصلی استفاده می‌کند.');
        } else {
            setFeedback('mobile', '');
        }
        refreshPreviews();
    });
    restoreDefault?.addEventListener('change', () => {
        if (restoreDefault.checked) {
            releaseObjectUrl('desktop');
            releaseObjectUrl('mobile');
            desktopInput.value = '';
            mobileInput.value = '';
            if (removeMobile) removeMobile.checked = false;
            setFeedback('desktop', '');
            setFeedback('mobile', '');
            const defaults = { crop_x: 50, crop_y: 46, mobile_crop_x: 54, mobile_crop_y: 50 };
            Object.entries(defaults).forEach(([name, value]) => {
                const input = form.querySelector('[data-home-hero-range="' + name + '"]');
                if (input) input.value = String(value);
            });
        }
        syncRestoreState();
    });
    form.addEventListener('submit', () => {
        if (restoreDefault?.checked) {
            desktopInput.value = '';
            mobileInput.value = '';
        }
        if (submit) {
            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            submit.textContent = 'در حال ذخیره…';
        }
    });
    window.addEventListener('pagehide', () => {
        releaseObjectUrl('desktop');
        releaseObjectUrl('mobile');
    }, { once: true });

    refreshPreviews();
    updatePositions();
    syncRestoreState();
}
document.addEventListener('DOMContentLoaded', bootHomeHeroEditor);
