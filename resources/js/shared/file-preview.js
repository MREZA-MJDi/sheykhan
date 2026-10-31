/* Presentation-free local file preview behavior. Each page owns its preview markup and styling. */
const INPUT_SELECTOR = [
    'input[data-file-preview]',
    'input[data-owner-file-preview]',
    '[data-media-uploader] input[type="file"]',
].join(',');

const formatFileSize = (bytes) => {
    const units = ['بایت', 'کیلوبایت', 'مگابایت', 'گیگابایت'];
    let value = Math.max(0, Number(bytes) || 0);
    let unit = 0;
    while (value >= 1024 && unit < units.length - 1) {
        value /= 1024;
        unit += 1;
    }
    return new Intl.NumberFormat('fa-IR', {
        maximumFractionDigits: unit ? 1 : 0,
    }).format(value) + ' ' + units[unit];
};

const releaseObjectUrl = (media) => {
    if (!media || !media.dataset.previewObjectUrl) return;
    URL.revokeObjectURL(media.dataset.previewObjectUrl);
    delete media.dataset.previewObjectUrl;
};

function bindFilePreview(input) {
    if (input.dataset.filePreviewBound === 'true') return;
    input.dataset.filePreviewBound = 'true';

    input.addEventListener('change', () => {
        const file = input.files?.[0] || null;
        const wrapper = input.closest('[data-file-preview-container]')
            || input.closest('[data-media-uploader]')
            || input.closest('form')
            || input.parentElement;
        if (!wrapper) return;

        const preview = wrapper.querySelector('[data-file-preview], [data-upload-preview]');
        const image = wrapper.querySelector('[data-preview-image]');
        const video = wrapper.querySelector('[data-preview-video]');
        const name = wrapper.querySelector('[data-preview-name]');
        const size = wrapper.querySelector('[data-preview-size]');
        const generic = wrapper.querySelector('[data-preview-generic]');

        releaseObjectUrl(image);
        releaseObjectUrl(video);

        if (!file) {
            if (preview) preview.hidden = true;
            if (image) {
                image.hidden = true;
                image.removeAttribute('src');
            }
            if (video) {
                video.pause?.();
                video.hidden = true;
                video.removeAttribute('src');
            }
            if (generic) generic.hidden = true;
            if (name) name.textContent = '';
            if (size) size.textContent = '';
            return;
        }

        if (preview) preview.hidden = false;
        if (name) name.textContent = file.name;
        if (size) size.textContent = formatFileSize(file.size);
        if (image) {
            image.hidden = true;
            image.removeAttribute('src');
        }
        if (video) {
            video.pause?.();
            video.hidden = true;
            video.removeAttribute('src');
        }
        if (generic) generic.hidden = true;

        const objectUrl = URL.createObjectURL(file);
        if (file.type.startsWith('image/') && image) {
            image.dataset.previewObjectUrl = objectUrl;
            image.src = objectUrl;
            image.hidden = false;
            const release = () => releaseObjectUrl(image);
            image.addEventListener('load', release, { once: true });
            image.addEventListener('error', release, { once: true });
        } else if (file.type.startsWith('video/') && video) {
            video.dataset.previewObjectUrl = objectUrl;
            video.src = objectUrl;
            video.hidden = false;
            const release = () => releaseObjectUrl(video);
            video.addEventListener('loadeddata', release, { once: true });
            video.addEventListener('error', release, { once: true });
        } else {
            URL.revokeObjectURL(objectUrl);
            if (generic) generic.hidden = false;
        }
    });
}

export function bootFilePreviews(root = document) {
    root.querySelectorAll(INPUT_SELECTOR).forEach(bindFilePreview);
}

const initialize = () => bootFilePreviews();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize, { once: true });
} else {
    initialize();
}
