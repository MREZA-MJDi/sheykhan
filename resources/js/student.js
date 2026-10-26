/* Student panel JavaScript entry. The only cross-panel modules are the Jalali calendar and Persian number/money helpers. */

import './persian-calendar.js';

import './persian-money.js';

import Alpine from 'alpinejs';
import shaka from 'shaka-player/dist/shaka-player.ui.js';
import persianTranslations from './locales/shaka-fa.json';

const ALPINE_BOOT_FLAG = '__sheykhanAlpineBooted';

function bootAlpine() {
    if (window[ALPINE_BOOT_FLAG]) return;

    const alpine = window.Alpine || Alpine;
    window.Alpine = alpine;

    if (!alpine || typeof alpine.start !== 'function') return;

    alpine.start();
    window[ALPINE_BOOT_FLAG] = true;
}

function bootRoleAvatarFallback() {
    // This shared recovery behavior belongs only to Student and Teacher panels.
    if (!document.body.matches('.student-shell, .teacher-shell')) return;

    document.querySelectorAll('img[data-avatar-image]').forEach((image) => {
        if (image.dataset.roleAvatarFallbackBound === 'true') return;

        const fallback = image.parentElement?.querySelector('[data-avatar-fallback]');
        if (!fallback) return;

        image.dataset.roleAvatarFallbackBound = 'true';
        const showFallback = () => {
            image.hidden = true;
            fallback.hidden = false;
        };

        image.addEventListener('error', showFallback, { once: true });
        if (image.complete && image.naturalWidth === 0) showFallback();
    });
}

function bootProfileAvatarFallback() {
    document.querySelectorAll('img[data-profile-avatar]').forEach((image) => {
        if (image.dataset.avatarFallbackBound === 'true') return;

        image.dataset.avatarFallbackBound = 'true';
        const fallbackUrl = image.dataset.fallbackSrc || '';

        const recover = () => {
            const currentSrc = image.getAttribute('src') || '';
            if (fallbackUrl && currentSrc !== fallbackUrl && image.dataset.avatarFallbackAttempted !== 'true') {
                image.dataset.avatarFallbackAttempted = 'true';
                image.src = fallbackUrl;
                return;
            }

            image.hidden = true;
        };

        image.addEventListener('error', recover);
        if (image.complete && image.naturalWidth === 0) recover();
    });
}

export function bootPanel() {
    bootRoleAvatarFallback();
    bootProfileAvatarFallback();
    bootAlpine();

    const openButton = document.querySelector('[data-role-menu-open]');
    const sidebar = document.querySelector('.role-sidebar');

    if (!openButton || !sidebar) return;

    const setMenuState = (open) => {
        document.body.classList.toggle('role-menu-open', open);
        openButton.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    openButton.addEventListener('click', () => {
        setMenuState(!document.body.classList.contains('role-menu-open'));
    });

    document.addEventListener('click', (event) => {
        if (!document.body.classList.contains('role-menu-open')) return;

        if (sidebar.contains(event.target) || openButton.contains(event.target)) {
            return;
        }

        setMenuState(false);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setMenuState(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 1050) {
            setMenuState(false);
        }
    });
}

export function closePanelMenu() {
    document.body.classList.remove('role-menu-open');

    document
        .querySelector('[data-role-menu-open]')
        ?.setAttribute('aria-expanded', 'false');
}

const bootOwnerPeopleTabs = () => {
    document.querySelectorAll('[data-owner-people-tabs]').forEach((root) => {
        const buttons = Array.from(root.querySelectorAll('[data-owner-tab-button]'));
        const panels = Array.from(root.querySelectorAll('[data-owner-tab-panel]'));
        if (!buttons.length || !panels.length) return;

        const activate = (key, moveFocus = false) => {
            buttons.forEach((button) => {
                const active = button.dataset.ownerTabButton === key;
                button.setAttribute('aria-selected', active ? 'true' : 'false');
                button.tabIndex = active ? 0 : -1;
                button.classList.toggle('is-active', active);
            });
            panels.forEach((panel) => {
                const active = panel.dataset.ownerTabPanel === key;
                panel.hidden = !active;
                panel.classList.toggle('is-active', active);
            });
            if (moveFocus) buttons.find((button) => button.dataset.ownerTabButton === key)?.focus();
        };

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => activate(button.dataset.ownerTabButton));
            button.addEventListener('keydown', (event) => {
                if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
                event.preventDefault();
                const nextIndex = event.key === 'Home' ? 0
                    : event.key === 'End' ? buttons.length - 1
                    : (index + (event.key === 'ArrowRight' ? 1 : -1) + buttons.length) % buttons.length;
                activate(buttons[nextIndex].dataset.ownerTabButton, true);
            });
        });

        const requested = root.dataset.initialTab;
        activate(buttons.some((button) => button.dataset.ownerTabButton === requested) ? requested : buttons[0].dataset.ownerTabButton);
    });
};

const bootRoleMobileNavigation = () => {
    document.querySelectorAll('[data-role-more-panel]').forEach((panel) => {
        const opener = document.querySelector('[data-role-more-open][aria-controls="' + panel.id + '"]');
        const dialog = panel.querySelector('.role-mobile-more-dialog');
        const closers = panel.querySelectorAll('[data-role-more-close]');
        if (!opener || !dialog) return;

        let returnFocusTo = null;
        const close = () => {
            if (panel.hidden) return;
            panel.hidden = true;
            opener.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('role-mobile-more-open');
            returnFocusTo?.focus({ preventScroll: true });
        };

        opener.addEventListener('click', () => {
            returnFocusTo = opener;
            panel.hidden = false;
            opener.setAttribute('aria-expanded', 'true');
            document.body.classList.add('role-mobile-more-open');
            dialog.focus({ preventScroll: true });
        });
        closers.forEach((button) => button.addEventListener('click', close));
        panel.addEventListener('click', (event) => {
            if (event.target === panel) close();
        });
        dialog.querySelectorAll('a[href]').forEach((link) => {
            link.addEventListener('click', () => {
                panel.hidden = true;
                opener.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('role-mobile-more-open');
            });
        });
        document.addEventListener('keydown', (event) => {
            if (panel.hidden) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
                return;
            }
            if (event.key !== 'Tab') return;
            const focusable = Array.from(panel.querySelectorAll('a[href],button:not([disabled]):not(.role-mobile-more-backdrop),[tabindex="0"]'))
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
};

const bootShakaPlayerComponents = () => {
    if (!document.querySelector('[data-shaka-player-root]')) return;
    bootShakaPlayers().catch((error) => {
        console.error('[Sheykhan Shaka] Failed to initialize player', error);
        document.querySelectorAll('[data-shaka-status]').forEach((status) => {
            status.textContent = 'پلیر بارگذاری نشد. منابع فرانت‌اند را بررسی و صفحه را دوباره بارگذاری کنید.';
            status.dataset.tone = 'error';
            status.hidden = false;
        });
    });
};

const bootSharedFoundation = () => {
    bootPanel();
    bootOwnerPeopleTabs();
    bootRoleMobileNavigation();
    bootShakaPlayerComponents();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSharedFoundation, { once: true });
} else {
    bootSharedFoundation();
}


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

        const profileModal = document.getElementById('student-profile-modal');
        const profileOpeners = document.querySelectorAll('[data-student-profile-open]');
        const profileCloseControls = profileModal?.querySelectorAll('[data-student-profile-close]') || [];
        const themeControls = profileModal?.querySelectorAll('[data-student-theme-toggle]') || [];
        let returnFocusTo = null;

        const setStudentTheme = (value) => {
            const theme = value === 'dark' ? 'dark' : 'light';
            document.documentElement.dataset.theme = theme;
            try { localStorage.setItem('sheykhan.panel.theme', theme); localStorage.setItem('sheykhan.student.theme', theme); } catch (_) {}
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


/* Student-local Shaka player implementation. */
const initializedPlayers = new WeakMap();

const showStatus = (root, message, tone = 'info') => {
    const status = root.querySelector('[data-shaka-status]');
    if (!status) return;

    status.textContent = message;
    status.dataset.tone = tone;
    status.hidden = false;
};

const hideStatus = (root) => {
    const status = root.querySelector('[data-shaka-status]');
    if (status) status.hidden = true;
};

const getPlayableManifestUrl = (rawUrl) => {
    if (!rawUrl || !rawUrl.trim()) return null;

    try {
        const url = new URL(rawUrl, window.location.origin);
        if (!['http:', 'https:'].includes(url.protocol)) return null;
        if (window.location.protocol === 'https:' && url.protocol !== 'https:') return null;
        return url.href;
    } catch {
        return null;
    }
};

async function initializePlayer(root) {
    if (initializedPlayers.has(root)) return initializedPlayers.get(root);

    const task = (async () => {
        const video = root.querySelector('[data-shaka-video]');
        const container = root.querySelector('[data-shaka-player-container]');
        const rawManifestUrl = root.dataset.manifestUrl || '';
        const manifestUrl = getPlayableManifestUrl(rawManifestUrl);

        if (!video || !container) {
            showStatus(root, 'ساختار پلیر ناقص است. صفحه را دوباره بارگذاری کنید.', 'error');
            return;
        }

        if (!manifestUrl) {
            showStatus(root, 'منبع استریم هنوز به این درس متصل نشده است.', 'info');
            root.dataset.playerState = 'awaiting-source';
            return;
        }

        if (typeof shaka?.Player !== 'function' || !shaka.ui?.Overlay) {
            showStatus(root, 'کتابخانهٔ پلیر بارگذاری نشد. منابع فرانت‌اند را دوباره build کنید.', 'error');
            return;
        }

        shaka.polyfill.installAll();

        if (!shaka.Player.isBrowserSupported()) {
            showStatus(root, 'این مرورگر APIهای لازم برای پخش Shaka را پشتیبانی نمی‌کند.', 'error');
            root.dataset.playerState = 'unsupported';
            return;
        }

        let player;
        let ui;

        try {
            player = new shaka.Player();
            await player.attach(video);

            ui = new shaka.ui.Overlay(player, container, video);
            ui.configure({
                addSeekBar: true,
                enableTooltips: true,
                controlPanelElements: [
                    'play_pause',
                    'time_and_duration',
                    'spacer',
                    'mute',
                    'volume',
                    'playback_rate',
                    'overflow_menu',
                    'fullscreen',
                ],
                overflowMenuButtons: [
                    'quality',
                    'language',
                    'captions',
                    'playback_rate',
                    'statistics',
                ],
                seekBarColors: {
                    base: 'rgba(255, 255, 255, 0.28)',
                    buffered: 'rgba(124, 187, 255, 0.72)',
                    played: '#7c83ff',
                },
            });

            const localization = ui.getControls().getLocalization();
            localization.insert('fa', new Map(Object.entries(persianTranslations)));
            localization.changeLocale(['fa']);

            player.addEventListener('error', () => {
                showStatus(root, 'پخش این ویدئو انجام نشد. اتصال، آدرس استریم و مجوز دسترسی را بررسی کنید.', 'error');
                root.dataset.playerState = 'error';
            });

            showStatus(root, 'در حال آماده‌سازی پخش…');
            await player.load(manifestUrl);
            root.dataset.playerState = 'ready';
            hideStatus(root);
        } catch (error) {
            // Keep technical details out of the student-facing UI. The server-side
            // logging and a safe diagnostic channel can be added with the DRM API.
            console.error('[Sheykhan Shaka] Player initialization failed', error);
            showStatus(root, 'پخش این ویدئو آماده نشد. منبع استریم و سازگاری مرورگر را بررسی کنید.', 'error');
            root.dataset.playerState = 'error';

            if (ui) {
                try { ui.destroy(); } catch { /* best-effort cleanup */ }
            }
            if (player) {
                try { await player.destroy(); } catch { /* best-effort cleanup */ }
            }
        }
    })();

    initializedPlayers.set(root, task);
    return task;
}
async function bootShakaPlayers() {
    const roots = Array.from(document.querySelectorAll('[data-shaka-player-root]'));
    if (!roots.length) return;

    await Promise.all(roots.map(initializePlayer));
}
