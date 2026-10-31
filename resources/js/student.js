/* Student panel entry. Shared shell behavior lives in panel-runtime.js. */
import './shared/persian-calendar.js';
import './shared/persian-money.js';
import { bootPanelRuntime, closePanelMenu } from './panel-runtime.js';
export { closePanelMenu };

bootPanelRuntime();

/* Lightweight student-only interactions; data and authorization remain server-owned. */
(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    ready(() => {
        const avatarInput = document.querySelector('[data-avatar-input]');
        const avatarPreview = document.querySelector('[data-avatar-preview]');
        let previewUrl = null;

        if (avatarInput && avatarPreview) {
            avatarInput.addEventListener('change', () => {
                const file = avatarInput.files?.[0];
                if (!file) return;

                const allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/avif'];
                const maxBytes = 3 * 1024 * 1024;
                if (!allowedTypes.includes(file.type) || file.size > maxBytes) {
                    window.alert('تصویر باید JPG، PNG، WebP یا AVIF و حداکثر ۳ مگابایت باشد.');
                    avatarInput.value = '';
                    return;
                }

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                const currentPreviewUrl = URL.createObjectURL(file);
                previewUrl = currentPreviewUrl;
                const image = new Image();
                image.alt = 'پیش‌نمایش تصویر پروفایل';
                image.width = 100;
                image.height = 100;
                image.decoding = 'async';
                image.loading = 'eager';
                image.style.cssText = 'width:100%;height:100%;object-fit:cover';
                image.onload = () => {
                    URL.revokeObjectURL(currentPreviewUrl);
                    if (previewUrl === currentPreviewUrl) previewUrl = null;
                };
                image.onerror = () => {
                    URL.revokeObjectURL(currentPreviewUrl);
                    if (previewUrl === currentPreviewUrl) previewUrl = null;
                    avatarInput.value = '';
                    const fallback = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    fallback.setAttribute('viewBox', '0 0 120 120');
                    fallback.setAttribute('aria-hidden', 'true');
                    fallback.style.cssText = 'width:100%;height:100%';
                    const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
                    use.setAttribute('href', '#avatar');
                    fallback.append(use);
                    avatarPreview.replaceChildren(fallback);
                };
                image.src = currentPreviewUrl;
                avatarPreview.replaceChildren(image);
            });
        }

        document.querySelectorAll('[data-countdown]').forEach((timer) => {
            let remaining = Math.max(0, Number.parseInt(timer.dataset.countdown || '0', 10) || 0);
            const form = timer.closest('form') || document.getElementById('exam-form');
            let submitted = false;
            const digits = (value) => String(value).padStart(2, '0').replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);

            const render = () => {
                const minutes = Math.floor(remaining / 60);
                const seconds = remaining % 60;
                timer.textContent = digits(minutes) + ':' + digits(seconds);
            };

            const tick = () => {
                render();
                if (remaining <= 0) {
                    window.clearInterval(interval);
                    if (!submitted && form) {
                        submitted = true;
                        const submitButton = form.querySelector('[type="submit"]');
                        if (typeof form.requestSubmit === 'function') {
                            if (submitButton) form.requestSubmit(submitButton);
                            else form.requestSubmit();
                        } else {
                            form.submit();
                        }
                    }
                    return;
                }
                remaining -= 1;
            };

            render();
            const interval = window.setInterval(tick, 1000);
            if (remaining <= 0) tick();
        });

        document.querySelectorAll('[data-mark-complete]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = button.closest('[data-progress-form]');
                const progress = form?.querySelector('[data-progress-value]');
                if (!form || !progress) return;
                progress.value = '100';
                if (typeof form.requestSubmit === 'function') form.requestSubmit();
                else form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            });
        });

        document.querySelectorAll('[data-progress-form]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const submitButton = form.querySelector('[type="submit"], [data-mark-complete]');
                const feedback = form.querySelector('[data-progress-feedback]');
                if (submitButton) submitButton.disabled = true;
                if (feedback) feedback.textContent = 'در حال ذخیره…';

                try {
                    const response = await fetch(form.action, {
                        method: 'PATCH',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                    });
                    const result = await response.json();
                    if (!response.ok || !result.ok) throw new Error('progress');
                    if (feedback) feedback.textContent = 'پیشرفت درس ذخیره شد.';
                    const meter = document.querySelector('[data-progress-meter]');
                    if (meter) {
                        meter.style.width = result.progress_percent + '%';
                        meter.setAttribute('aria-valuenow', String(result.progress_percent));
                    }
                } catch {
                    if (feedback) feedback.textContent = 'ذخیره انجام نشد؛ صفحه را تازه کن و دوباره تلاش کن.';
                } finally {
                    if (submitButton) submitButton.disabled = false;
                }
            });
        });

        document.querySelectorAll('[data-question-next],[data-question-previous]').forEach((button) => {
            button.addEventListener('click', () => {
                const current = button.closest('[data-question-step]');
                const steps = [...document.querySelectorAll('[data-question-step]')];
                if (!current || steps.length < 2) return;
                const index = steps.indexOf(current);
                const direction = button.hasAttribute('data-question-next') ? 1 : -1;
                const target = steps[index + direction];
                if (!target) return;
                target.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
                target.querySelector('input,textarea,button')?.focus({ preventScroll: true });
            });
        });

        document.querySelectorAll('textarea[data-autosize]').forEach((textarea) => {
            const resize = () => {
                textarea.style.height = 'auto';
                textarea.style.height = `${textarea.scrollHeight}px`;
            };
            textarea.addEventListener('input', resize);
            resize();
        });

    });
})();


// Show the selected assignment attachments before submission without uploading them early.
document.addEventListener('change', (event) => {
    const input = event.target instanceof HTMLInputElement
        ? event.target.closest('[data-assignment-file-upload]')
        : null;

    if (!input) return;

    const output = input.form?.querySelector('[data-assignment-files-selection]');
    if (!output) return;

    const files = Array.from(input.files ?? []);
    if (!files.length) {
        output.textContent = 'هنوز فایلی انتخاب نشده است.';
        return;
    }

    const sizeLabel = (bytes) => {
        const kilobytes = Math.ceil(bytes / 1024);
        return kilobytes >= 1024
            ? `${(kilobytes / 1024).toLocaleString('fa-IR', { maximumFractionDigits: 1 })} مگابایت`
            : `${kilobytes.toLocaleString('fa-IR')} کیلوبایت`;
    };

    output.textContent = files
        .map((file) => `${file.name} · ${sizeLabel(file.size)}`)
        .join('، ');
});


/* ZIP reference interaction: safe multi-file selection with count/size feedback.
   The backend still validates every upload and stores student submissions privately. */
(() => {
    'use strict';
    const formatFa = (value) => Number(value).toLocaleString('fa-IR');

    document.querySelectorAll('[data-uploader]').forEach((box) => {
        const input = box.querySelector('input[type="file"]');
        const list = box.querySelector('[data-file-list]');
        const error = box.querySelector('[data-file-error]');
        const drop = box.querySelector('.drop');
        if (!input || !list || !error || !drop) return;

        const maxCount = Math.max(1, Number.parseInt(box.dataset.max || '3', 10) || 3);
        const maxBytes = Math.max(1, Number.parseInt(box.dataset.maxMb || '10', 10) || 10) * 1024 * 1024;
        let selected = [];

        const sync = () => {
            const transfer = new DataTransfer();
            selected.forEach((file) => transfer.items.add(file));
            input.files = transfer.files;
            list.replaceChildren();

            selected.forEach((file, index) => {
                const item = document.createElement('li');
                const remove = document.createElement('button');
                item.className = 'tag g';
                item.append(\`\${file.name} · \${formatFa(Math.ceil(file.size / 1024))} کیلوبایت \`);
                remove.type = 'button';
                remove.textContent = '×';
                remove.setAttribute('aria-label', \`حذف \${file.name}\`);
                remove.addEventListener('click', () => {
                    selected.splice(index, 1);
                    error.textContent = '';
                    sync();
                });
                item.append(remove);
                list.append(item);
            });
        };

        const addFiles = (files) => {
            error.textContent = '';
            Array.from(files || []).forEach((file) => {
                if (file.size > maxBytes) {
                    error.textContent = \`«\${file.name}» از \${formatFa(maxBytes / 1048576)} مگابایت بزرگ‌تر است.\`;
                    return;
                }
                if (selected.length >= maxCount) {
                    error.textContent = \`حداکثر \${formatFa(maxCount)} فایل می‌توانی اضافه کنی.\`;
                    return;
                }
                const duplicate = selected.some((current) =>
                    current.name === file.name && current.size === file.size && current.lastModified === file.lastModified
                );
                if (!duplicate) selected.push(file);
            });
            sync();
        };

        input.addEventListener('change', () => addFiles(input.files));
        ['dragenter', 'dragover'].forEach((eventName) => {
            drop.addEventListener(eventName, (event) => {
                event.preventDefault();
                drop.classList.add('hv');
            });
        });
        ['dragleave', 'drop'].forEach((eventName) => {
            drop.addEventListener(eventName, (event) => {
                event.preventDefault();
                drop.classList.remove('hv');
            });
        });
        drop.addEventListener('drop', (event) => addFiles(event.dataTransfer?.files));
    });
})();
