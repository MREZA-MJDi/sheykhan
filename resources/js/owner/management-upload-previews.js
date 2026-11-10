/* File and cover previews for the owner resource library and product catalog. */
(() => {
    'use strict';

    const formatSize = (bytes) => {
        let size = Math.max(0, Number(bytes) || 0);
        const units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
        let index = 0;
        while (size >= 1024 && index < units.length - 1) {
            size /= 1024;
            index += 1;
        }
        return new Intl.NumberFormat('fa-IR', { maximumFractionDigits: 1 }).format(size) + ' ' + units[index];
    };

    const releaseUrl = (node) => {
        const url = node?.dataset?.ownerPreviewUrl;
        if (url) URL.revokeObjectURL(url);
        if (node?.dataset) delete node.dataset.ownerPreviewUrl;
    };

    const bindResourceInput = (input) => {
        if (input.dataset.ownerResourcePreviewBound === 'true') return;
        const form = input.closest('form');
        const preview = form?.querySelector('[data-owner-resource-preview]');
        const image = preview?.querySelector('[data-owner-resource-image]');
        const video = preview?.querySelector('[data-owner-resource-video]');
        const audio = preview?.querySelector('[data-owner-resource-audio]');
        const generic = preview?.querySelector('[data-owner-resource-generic]');
        const name = preview?.querySelector('[data-owner-resource-name]');
        const size = preview?.querySelector('[data-owner-resource-size]');
        const dimensions = preview?.querySelector('[data-owner-resource-dimensions]');

        if (!form || !preview) return;
        input.dataset.ownerResourcePreviewBound = 'true';

        const reset = () => {
            [image, video, audio].forEach((node) => {
                releaseUrl(node);
                if (!node) return;
                if (node.tagName === 'VIDEO' || node.tagName === 'AUDIO') node.pause?.();
                node.removeAttribute('src');
                node.hidden = true;
            });
            if (generic) generic.hidden = true;
            if (preview) preview.hidden = true;
            if (name) name.textContent = '';
            if (size) size.textContent = '';
            if (dimensions) dimensions.textContent = '';
        };

        input.addEventListener('change', () => {
            reset();
            const file = input.files?.[0];
            if (!file) return;
            preview.hidden = false;
            if (name) name.textContent = file.name;
            if (size) size.textContent = formatSize(file.size);

            const url = URL.createObjectURL(file);
            if (file.type.startsWith('image/') && image) {
                image.dataset.ownerPreviewUrl = url;
                image.src = url;
                image.hidden = false;
                image.onload = () => {
                    if (dimensions) dimensions.textContent = image.naturalWidth + ' × ' + image.naturalHeight + ' پیکسل';
                };
                image.onerror = () => {
                    if (dimensions) dimensions.textContent = 'پیش‌نمایش تصویر بارگذاری نشد.';
                };
                return;
            }
            if (file.type.startsWith('video/') && video) {
                video.dataset.ownerPreviewUrl = url;
                video.src = url;
                video.hidden = false;
                video.onloadedmetadata = () => {
                    if (dimensions && Number.isFinite(video.duration)) {
                        dimensions.textContent = 'مدت ویدئو: ' + new Intl.NumberFormat('fa-IR', { maximumFractionDigits: 1 }).format(video.duration) + ' ثانیه';
                    }
                };
                return;
            }
            if (file.type.startsWith('audio/') && audio) {
                audio.dataset.ownerPreviewUrl = url;
                audio.src = url;
                audio.hidden = false;
                return;
            }
            URL.revokeObjectURL(url);
            if (generic) generic.hidden = false;
        });
    };

    const bindCoverInput = (input) => {
        if (input.dataset.ownerCoverPreviewBound === 'true') return;
        const editor = input.closest('[data-owner-cover-editor]') || input.parentElement;
        const preview = editor?.querySelector('.owner-cover-preview');
        const feedback = editor?.querySelector('[data-owner-cover-feedback]');
        const empty = editor?.querySelector('[data-owner-cover-empty]');
        if (!preview) return;

        input.dataset.ownerCoverPreviewBound = 'true';
        const image = document.createElement('img');
        image.alt = 'پیش‌نمایش تصویر جلد انتخاب‌شده';
        image.hidden = true;
        image.loading = 'eager';
        image.style.width = '100%';
        image.style.height = '100%';
        image.style.objectFit = 'cover';
        image.style.aspectRatio = '4 / 3';
        preview.append(image);

        const showFeedback = (message) => {
            if (feedback) feedback.textContent = message;
        };

        input.addEventListener('change', () => {
            releaseUrl(image);
            image.hidden = true;
            image.removeAttribute('src');
            if (empty) empty.hidden = false;

            const file = input.files?.[0];
            if (!file) {
                showFeedback('این پیش‌نمایش مطابق نسبت تصویر کارت محصول است؛ فایل اصلی بدون تغییر ذخیره می‌شود.');
                return;
            }

            const maxMb = Number(input.dataset.maxSizeMb || 10);
            if (file.size > maxMb * 1024 * 1024) {
                showFeedback('حجم تصویر از سقف ' + maxMb + ' مگابایت بیشتر است؛ تصویر دیگری انتخاب کن.');
                return;
            }

            const allowed = (input.dataset.allowedExtensions || 'jpg,jpeg,png,webp')
                .split(',').map((extension) => extension.trim().toLowerCase()).filter(Boolean);
            const extension = file.name.split('.').pop()?.toLowerCase() || '';
            if (!allowed.includes(extension)) {
                showFeedback('فرمت تصویر باید یکی از این موارد باشد: ' + allowed.join('، '));
                return;
            }

            const url = URL.createObjectURL(file);
            image.dataset.ownerPreviewUrl = url;
            image.src = url;
            image.hidden = false;
            if (empty) empty.hidden = true;
            image.onload = () => {
                const recommendedWidth = Number(input.dataset.recommendedWidth || 0);
                const recommendedHeight = Number(input.dataset.recommendedHeight || 0);
                const sizeNote = recommendedWidth && recommendedHeight
                    ? '؛ اندازهٔ پیشنهادی ' + recommendedWidth + '×' + recommendedHeight
                    : '';
                showFeedback('پیش‌نمایش ' + image.naturalWidth + '×' + image.naturalHeight + ' پیکسل' + sizeNote + '. فایل اصلی بدون تغییر ذخیره می‌شود.');
            };
            image.onerror = () => showFeedback('بارگذاری پیش‌نمایش تصویر ممکن نشد؛ فایل دیگری انتخاب کن.');
        });
    };

    const boot = (root = document) => {
        root.querySelectorAll('[data-owner-resource-input]').forEach(bindResourceInput);
        root.querySelectorAll('[data-owner-cover-input]').forEach(bindCoverInput);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => boot(), { once: true });
    } else {
        boot();
    }

    window.addEventListener('owner:modal:mounted', (event) => {
        const root = event.detail?.root;
        if (root) boot(root);
    });
})();
