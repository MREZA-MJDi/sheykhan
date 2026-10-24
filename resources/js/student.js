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
        // A stale or missing media file must never leave a broken-image icon in the UI.
        document.querySelectorAll('[data-avatar-image]').forEach((image) => {
            const fallback = image.parentElement?.querySelector('[data-avatar-fallback]');
            if (!fallback) return;

            const useFallback = () => {
                image.hidden = true;
                fallback.hidden = false;
            };

            image.addEventListener('error', useFallback, { once: true });
            if (image.complete && image.naturalWidth === 0) useFallback();
        });

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

        const profileModal = document.getElementById('student-profile-modal');
        const profileOpeners = document.querySelectorAll('[data-student-profile-open]');
        const profileCloseControls = profileModal?.querySelectorAll('[data-student-profile-close]') || [];
        const themeControls = profileModal?.querySelectorAll('[data-student-theme-toggle]') || [];
        let returnFocusTo = null;

        const setStudentTheme = (value) => {
            const theme = value === 'dark' ? 'dark' : 'light';
            document.documentElement.dataset.theme = theme;
            try { localStorage.setItem('sheykhan.student.theme', theme); } catch (_) {}
            themeControls.forEach((button) => button.setAttribute('aria-pressed', String(theme === 'dark')));
            const state = profileModal?.querySelector('[data-student-theme-state]');
            const label = profileModal?.querySelector('[data-student-theme-label]');
            if (state) state.textContent = theme === 'dark' ? 'تیره' : 'روشن';
            if (label) label.textContent = theme === 'dark' ? 'تم تیره فعال است؛ برای تغییر بزن' : 'تم روشن فعال است؛ برای تغییر بزن';
        };

        setStudentTheme(document.documentElement.dataset.theme || 'light');
        themeControls.forEach((button) => {
            button.addEventListener('click', () => {
                setStudentTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
            });
        });

        const closeProfileModal = () => {
            if (!profileModal || profileModal.hidden) return;
            profileModal.hidden = true;
            document.body.classList.remove('student-profile-modal-open');
            returnFocusTo?.focus({ preventScroll: true });
        };

        profileOpeners.forEach((button) => {
            button.addEventListener('click', () => {
                if (!profileModal) return;
                returnFocusTo = button;
                profileModal.hidden = false;
                document.body.classList.add('student-profile-modal-open');
                profileModal.querySelector('.student-profile-dialog')?.focus({ preventScroll: true });
            });
        });
        profileCloseControls.forEach((button) => button.addEventListener('click', closeProfileModal));
        profileModal?.addEventListener('click', (event) => {
            if (event.target === profileModal) closeProfileModal();
        });
        document.addEventListener('keydown', (event) => {
            if (!profileModal || profileModal.hidden) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                closeProfileModal();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = Array.from(profileModal.querySelectorAll('a[href],button:not([disabled]):not(.student-profile-backdrop),input:not([disabled]),[tabindex="0"]'))
                .filter((element) => element.getClientRects().length > 0);
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    });
})();
