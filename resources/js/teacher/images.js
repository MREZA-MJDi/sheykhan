// Profile image preview/compression. The actual upload fields are avatar and cover.
const MAX_DIMENSION = { avatar: 420, cover: 1600 };

function shrink(file, maxDimension) {
  return new Promise((resolve, reject) => {
    const image = new Image();
    const objectUrl = URL.createObjectURL(file);
    image.onload = () => {
      const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.max(1, Math.round(image.width * scale));
      canvas.height = Math.max(1, Math.round(image.height * scale));
      const context = canvas.getContext('2d');
      if (!context) {
        URL.revokeObjectURL(objectUrl);
        reject(new Error('Canvas unavailable'));
        return;
      }
      context.drawImage(image, 0, 0, canvas.width, canvas.height);
      URL.revokeObjectURL(objectUrl);
      canvas.toBlob((blob) => {
        if (!blob) return reject(new Error('Image compression failed'));
        resolve(new File([blob], `${file.name.replace(/\.[^.]+$/, '')}.jpg`, { type: 'image/jpeg' }));
      }, 'image/jpeg', 0.84);
    };
    image.onerror = () => {
      URL.revokeObjectURL(objectUrl);
      reject(new Error('Invalid image'));
    };
    image.src = objectUrl;
  });
}

function updatePreview(kind, url) {
  document.querySelectorAll(`[data-tp-preview-bg="${kind}"]`).forEach((element) => {
    element.style.backgroundImage = url ? `url("${url}")` : '';
    element.querySelectorAll('[data-tp-photo-initials]').forEach((initials) => { initials.hidden = Boolean(url); });
    element.classList.toggle('no-cover', kind === 'cover' && !url);
    element.classList.toggle('has-cover', kind === 'cover' && Boolean(url));
  });
}

export function initImages() {
  document.addEventListener('change', async (event) => {
    const input = event.target.closest('[data-tp-image]');
    const file = input?.files?.[0];
    if (!input || !file) return;
    const kind = input.dataset.tpImage;
    if (!(kind in MAX_DIMENSION) || !file.type.startsWith('image/')) {
      input.value = '';
      window.alert('فقط فایل تصویری معتبر انتخاب کن.');
      return;
    }
    try {
      const compressed = await shrink(file, MAX_DIMENSION[kind]);
      const target = document.querySelector(`input[type="file"][name="${kind}"]`);
      if (target && target !== input) {
        const transfer = new DataTransfer();
        transfer.items.add(compressed);
        target.files = transfer.files;
      } else if (target === input) {
        const transfer = new DataTransfer();
        transfer.items.add(compressed);
        input.files = transfer.files;
      }
      const url = URL.createObjectURL(compressed);
      updatePreview(kind, url);
      const removeField = document.querySelector(`[data-tp-remove-flag="${kind}"]`);
      if (removeField) removeField.value = '0';
    } catch {
      input.value = '';
      window.alert('تصویر خوانده نشد؛ فایل دیگری را امتحان کن.');
    }
  });

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-tp-image-remove]');
    if (!button) return;
    const kind = button.dataset.tpImageRemove;
    const input = document.querySelector(`input[type="file"][name="${kind}"]`);
    if (input) input.value = '';
    updatePreview(kind, null);
    const removeField = document.querySelector(`[data-tp-remove-flag="${kind}"]`);
    if (removeField) removeField.value = '1';
  });
}