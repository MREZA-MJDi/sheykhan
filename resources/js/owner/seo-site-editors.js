/* Live previews and crop controls for the SEO specialist's Hero, Banner and SEO editors. */
(() => {
  'use strict';

  const formatSize = (bytes) => {
    const mb = bytes / (1024 * 1024);
    return mb >= 1 ? mb.toFixed(1) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
  };

  const setText = (node, value, fallback = '') => {
    if (!node) return;
    const text = String(value || '').trim();
    node.textContent = text || fallback;
    node.hidden = !text;
  };

  const setImage = (image, url) => {
    if (!image) return;
    if (url) {
      image.src = url;
      image.hidden = false;
    } else {
      image.removeAttribute('src');
      image.hidden = true;
    }
  };

  const bindHeroEditors = () => {
    document.querySelectorAll('[data-home-hero-editor]').forEach((form) => {
      if (form.dataset.heroEditorBound === 'true') return;
      form.dataset.heroEditorBound = 'true';

      const desktop = form.querySelector('[data-home-hero-preview-desktop]');
      const mobile = form.querySelector('[data-home-hero-preview-mobile]');
      const desktopInfo = form.querySelector('[data-home-hero-desktop-info]');
      const mobileInfo = form.querySelector('[data-home-hero-mobile-info]');
      const desktopInput = form.querySelector('[data-home-hero-file="desktop"]');
      const mobileInput = form.querySelector('[data-home-hero-file="mobile"]');
      const mobileRemove = form.querySelector('[data-home-hero-remove-mobile]');
      const fallback = form.dataset.fallbackImage || form.dataset.defaultImage || '';

      const crop = () => {
        const values = {};
        ['crop_x', 'crop_y', 'mobile_crop_x', 'mobile_crop_y'].forEach((name) => {
          const input = form.querySelector('[data-home-hero-range="' + name + '"]');
          if (!input) return;
          values[name] = input.value;
          const output = form.querySelector('[data-home-hero-range-output="' + name + '"]');
          if (output) output.textContent = input.value + '٪';
        });
        if (desktop) desktop.style.objectPosition = (values.crop_x ?? 50) + '% ' + (values.crop_y ?? 46) + '%';
        if (mobile) mobile.style.objectPosition = (values.mobile_crop_x ?? 54) + '% ' + (values.mobile_crop_y ?? 50) + '%';
      };

      const bindFile = (input, image, info, otherImage) => {
        if (!input) return;
        input.addEventListener('change', () => {
          const file = input.files?.[0];
          if (!file) return;
          const maxMb = Number(input.dataset.maxSizeMb || 0);
          const minWidth = Number(input.dataset.minWidth || 0);
          const minHeight = Number(input.dataset.minHeight || 0);
          const feedback = form.querySelector('[data-home-hero-feedback="' + input.dataset.homeHeroFile + '"]');
          if (!file.type.startsWith('image/')) {
            input.value = '';
            if (feedback) feedback.textContent = 'فایل انتخاب‌شده تصویر معتبر نیست.';
            return;
          }
          if (maxMb && file.size > maxMb * 1024 * 1024) {
            input.value = '';
            if (feedback) feedback.textContent = 'حجم فایل باید حداکثر ' + maxMb + ' مگابایت باشد.';
            return;
          }
          const url = URL.createObjectURL(file);
          const probe = new Image();
          probe.onload = () => {
            if ((minWidth && probe.naturalWidth < minWidth) || (minHeight && probe.naturalHeight < minHeight)) {
              URL.revokeObjectURL(url);
              input.value = '';
              if (feedback) feedback.textContent = 'ابعاد تصویر باید حداقل ' + minWidth + '×' + minHeight + ' پیکسل باشد.';
              return;
            }
            setImage(image, url);
            if (otherImage && input.dataset.homeHeroFile === 'desktop' && !mobileInput?.files?.length && !mobileRemove?.checked) {
              setImage(otherImage, url);
            }
            if (feedback) feedback.textContent = file.name + ' · ' + probe.naturalWidth + '×' + probe.naturalHeight + ' · ' + formatSize(file.size);
            if (info) info.textContent = 'پیش‌نمایش فایل انتخاب‌شده؛ پس از ذخیره در سایت نمایش داده می‌شود.';
          };
          probe.onerror = () => {
            URL.revokeObjectURL(url);
            input.value = '';
            if (feedback) feedback.textContent = 'خواندن تصویر ممکن نشد؛ فایل دیگری انتخاب کن.';
          };
          probe.src = url;
        });
      };

      bindFile(desktopInput, desktop, desktopInfo, mobile);
      bindFile(mobileInput, mobile, mobileInfo, null);

      form.querySelectorAll('[data-home-hero-range]').forEach((input) => input.addEventListener('input', crop));
      form.querySelectorAll('[data-home-hero-reset-crop]').forEach((button) => {
        button.addEventListener('click', () => {
          const prefix = button.dataset.homeHeroResetCrop === 'mobile' ? 'mobile_' : '';
          const defaults = prefix ? { x: 54, y: 50 } : { x: 50, y: 46 };
          ['x', 'y'].forEach((axis) => {
            const input = form.querySelector('[data-home-hero-range="' + prefix + 'crop_' + axis + '"]');
            if (input) input.value = defaults[axis];
          });
          crop();
        });
      });

      mobileRemove?.addEventListener('change', () => {
        if (!mobileRemove.checked || mobileInput?.files?.length) return;
        setImage(mobile, desktop?.getAttribute('src') || form.dataset.currentDesktop || fallback);
        if (mobileInfo) mobileInfo.textContent = 'تصویر موبایل حذف می‌شود؛ تصویر دسکتاپ استفاده خواهد شد.';
      });

      form.querySelectorAll('input[name="title"], textarea[name="description"]').forEach((input) => {
        input.addEventListener('input', () => {
          const copy = form.querySelector('.website-hero-preview-copy');
          if (!copy) return;
          const title = form.querySelector('[name="title"]')?.value.trim();
          const description = form.querySelector('[name="description"]')?.value.trim();
          const strong = copy.querySelector('strong');
          const small = copy.querySelector('small');
          if (strong) strong.textContent = title || 'آینده‌ات را با یادگیری بساز';
          if (small) small.textContent = description || 'از دوره و کلاس تا تمرین و آزمون';
        });
      });

      crop();
    });
  };

  const bindBannerEditors = () => {
    document.querySelectorAll('[data-banner-editor]').forEach((form) => {
      if (form.dataset.bannerEditorBound === 'true') return;
      form.dataset.bannerEditorBound = 'true';

      form.querySelectorAll('[data-banner-slot]').forEach((slot) => {
        const preview = slot.querySelector('[data-banner-preview]');
        const image = preview?.querySelector('[data-banner-preview-image]');
        const empty = preview?.querySelector('[data-banner-preview-empty]');
        const file = slot.querySelector('[data-banner-file]');
        const select = slot.querySelector('[data-banner-media-select]');
        const x = slot.querySelector('[data-crop-x]');
        const y = slot.querySelector('[data-crop-y]');
        const title = slot.querySelector('[data-banner-title-input]');
        const description = slot.querySelector('[data-banner-description-input]');
        const ctaLabel = slot.querySelector('[data-banner-cta-label-input]');
        const ctaUrl = slot.querySelector('[data-banner-cta-url-input]');
        const titlePreview = slot.querySelector('[data-banner-preview-title]');
        const descriptionPreview = slot.querySelector('[data-banner-preview-description]');
        const ctaPreview = slot.querySelector('[data-banner-preview-cta]');
        const ctaLabelPreview = slot.querySelector('[data-banner-preview-cta-label]');
        const updateCrop = () => {
          if (image) image.style.objectPosition = (x?.value || 50) + '% ' + (y?.value || 50) + '%';
          const xo = slot.querySelector('[data-crop-x-output]');
          const yo = slot.querySelector('[data-crop-y-output]');
          if (xo && x) xo.textContent = x.value + '٪';
          if (yo && y) yo.textContent = y.value + '٪';
        };
        const updateCopy = () => {
          setText(titlePreview, title?.value, '');
          setText(descriptionPreview, description?.value, '');
          if (ctaLabelPreview) ctaLabelPreview.textContent = ctaLabel?.value || 'مشاهده';
          if (ctaPreview) ctaPreview.hidden = !(ctaLabel?.value.trim() && ctaUrl?.value.trim());
        };
        const chooseUrl = (url) => {
          setImage(image, url);
          if (empty) empty.hidden = Boolean(url);
        };

        [x, y].filter(Boolean).forEach(input => input.addEventListener('input', updateCrop));
        [title, description, ctaLabel, ctaUrl].filter(Boolean).forEach(input => input.addEventListener('input', updateCopy));
        slot.querySelector('[data-banner-reset]')?.addEventListener('click', () => {
          if (x) x.value = '50';
          if (y) y.value = '50';
          updateCrop();
        });

        select?.addEventListener('change', () => {
          if (file?.files?.length) return;
          const option = select.selectedOptions?.[0];
          chooseUrl(option?.dataset.mediaUrl || preview?.dataset.bannerInitialImage || '');
        });

        file?.addEventListener('change', () => {
          const selected = file.files?.[0];
          if (!selected) return;
          const feedback = slot.querySelector('[data-banner-file-feedback]');
          const maxMb = Number(file.dataset.maxSizeMb || 5);
          if (!selected.type.startsWith('image/')) {
            file.value = '';
            if (feedback) feedback.textContent = 'فایل انتخاب‌شده تصویر معتبر نیست.';
            return;
          }
          if (selected.size > maxMb * 1024 * 1024) {
            file.value = '';
            if (feedback) feedback.textContent = 'حجم فایل باید حداکثر ' + maxMb + ' مگابایت باشد.';
            return;
          }
          const url = URL.createObjectURL(selected);
          const probe = new Image();
          probe.onload = () => {
            chooseUrl(url);
            if (feedback) feedback.textContent = selected.name + ' · ' + probe.naturalWidth + '×' + probe.naturalHeight + ' · ' + formatSize(selected.size);
            const meta = slot.querySelector('[data-banner-file-meta]');
            if (meta) meta.hidden = false;
            setText(slot.querySelector('[data-banner-file-name]'), selected.name);
            setText(slot.querySelector('[data-banner-file-size]'), formatSize(selected.size));
            setText(slot.querySelector('[data-banner-file-dimensions]'), probe.naturalWidth + '×' + probe.naturalHeight + ' پیکسل');
          };
          probe.onerror = () => {
            URL.revokeObjectURL(url);
            file.value = '';
            if (feedback) feedback.textContent = 'خواندن تصویر ممکن نشد؛ فایل دیگری انتخاب کن.';
          };
          probe.src = url;
        });
        updateCrop();
        updateCopy();
      });
    });
  };

  const bindSeoPreview = () => {
    document.querySelectorAll('[data-seo-og-preview]').forEach((root) => {
      if (root.dataset.seoPreviewBound === 'true') return;
      root.dataset.seoPreviewBound = 'true';
      const form = root.closest('form') || document;
      const imageInput = form.querySelector('[data-seo-og-image-input]');
      const titleInput = form.querySelector('[data-seo-og-title-input]');
      const descriptionInput = form.querySelector('[data-seo-og-description-input]');
      const image = root.querySelector('[data-seo-og-preview-image]');
      const empty = root.querySelector('[data-seo-og-preview-empty]');
      const status = root.querySelector('[data-seo-og-preview-status]');
      const updateCopy = () => {
        setText(root.querySelector('[data-seo-og-preview-title]'), titleInput?.value, 'عنوان صفحه');
        setText(root.querySelector('[data-seo-og-preview-description]'), descriptionInput?.value, 'توضیحات صفحه هنگام اشتراک‌گذاری در این بخش نمایش داده می‌شود.');
      };
      const updateImage = () => {
        const value = imageInput?.value.trim() || '';
        if (!value) {
          setImage(image, '');
          if (empty) empty.hidden = false;
          if (status) status.textContent = 'تصویر اشتراک‌گذاری هنوز تنظیم نشده است.';
          return;
        }
        try {
          const url = new URL(value, window.location.origin);
          if (!['http:', 'https:'].includes(url.protocol)) throw new Error('invalid');
          setImage(image, url.href);
          if (empty) empty.hidden = true;
          if (status) status.textContent = 'در حال بررسی دسترسی تصویر…';
          image?.addEventListener('load', () => { if (status) status.textContent = 'تصویر برای پیش‌نمایش بارگذاری شد.'; }, { once: true });
          image?.addEventListener('error', () => {
            if (status) status.textContent = 'تصویر از این آدرس بارگذاری نشد؛ URL عمومی را بررسی کن.';
            setImage(image, '');
            if (empty) empty.hidden = false;
          }, { once: true });
        } catch {
          setImage(image, '');
          if (empty) empty.hidden = false;
          if (status) status.textContent = 'آدرس تصویر معتبر نیست.';
        }
      };
      [titleInput, descriptionInput].filter(Boolean).forEach(input => input.addEventListener('input', updateCopy));
      imageInput?.addEventListener('change', updateImage);
      imageInput?.addEventListener('input', updateImage);
      updateCopy();
      if (imageInput?.value.trim()) updateImage();
    });
  };

  const boot = () => {
    bindHeroEditors();
    bindBannerEditors();
    bindSeoPreview();
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, { once: true });
  else boot();
})();
