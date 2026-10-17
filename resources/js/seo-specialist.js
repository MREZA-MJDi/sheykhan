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
