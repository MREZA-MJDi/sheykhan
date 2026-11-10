/* SEO-specialist single JS entry: SEO interactions, calendar and Persian input helpers. */
/* Accessible interactions for the server-rendered owner dashboard. No demo records. */
(() => {
    'use strict';

    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

    // Blog cover picker: immediate preview and client-side feedback; the server remains authoritative.
    $$('[data-owner-cover-editor]').forEach((editor) => {
        const input = $('[data-owner-cover-input]', editor);
        const preview = $('[data-owner-cover-preview]', editor);
        const empty = $('[data-owner-cover-empty]', editor);
        const feedback = $('[data-owner-cover-feedback]', editor);
        if (!input || !feedback) return;

        let previewUrl = null;
        const originalPreviewSrc = preview?.getAttribute('src') || null;
        const initialMessage = feedback.textContent.trim();

        const restorePreview = () => {
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = null;
            if (preview) {
                if (originalPreviewSrc) {
                    preview.src = originalPreviewSrc;
                    preview.hidden = false;
                } else {
                    preview.remove();
                }
            }
            if (empty) empty.hidden = Boolean(originalPreviewSrc);
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) {
                restorePreview();
                feedback.textContent = initialMessage;
                feedback.dataset.state = 'idle';
                return;
            }

            const maxBytes = Number(input.dataset.maxSizeMb || 10) * 1024 * 1024;
            const allowed = (input.dataset.allowedExtensions || 'jpg,jpeg,png,webp')
                .split(',')
                .map((extension) => extension.trim().toLowerCase());
            const extension = file.name.split('.').pop()?.toLowerCase() || '';
            const validMime = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);

            if (!allowed.includes(extension) || !validMime) {
                input.value = '';
                restorePreview();
                feedback.textContent = 'فرمت تصویر معتبر نیست. فقط JPG، PNG و WebP مجاز هستند.';
                feedback.dataset.state = 'error';
                return;
            }

            if (file.size > maxBytes) {
                input.value = '';
                restorePreview();
                feedback.textContent = 'حجم تصویر بیشتر از ۱۰ مگابایت است.';
                feedback.dataset.state = 'error';
                return;
            }

            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);

            let image = $('[data-owner-cover-preview]', editor);
            if (!image) {
                image = document.createElement('img');
                image.alt = 'پیش‌نمایش تصویر شاخص مقاله';
                image.dataset.ownerCoverPreview = '';
                $('.owner-cover-preview', editor)?.replaceChildren(image);
            }
            image.src = previewUrl;
            image.hidden = false;
            if (empty) empty.hidden = true;

            const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
            feedback.textContent = `تصویر آمادهٔ بارگذاری است · ${sizeMb} مگابایت · پس از ذخیره به مقاله متصل می‌شود.`;
            feedback.dataset.state = 'success';
        });
    });

})();
