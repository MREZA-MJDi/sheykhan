/* Teacher panel single entry. Only shared panel modules are the Jalali calendar and Persian input helpers. */

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


// ZIP utility: Jalali/Gregorian date conversion.
function fa(n){return String(n).replace(/\d/g,function(d){return"۰۱۲۳۴۵۶۷۸۹"[d]})}
function pad(n){return(n<10?"0":"")+n}
function g2j(gy,gm,gd){var gm2=[0,31,59,90,120,151,181,212,243,273,304,334],jy;if(gy>1600){jy=979;gy-=1600}else{jy=0;gy-=621}var g2=gm>2?gy+1:gy,days=365*gy+Math.floor((g2+3)/4)-Math.floor((g2+99)/100)+Math.floor((g2+399)/400)-80+gd+gm2[gm-1];jy+=33*Math.floor(days/12053);days%=12053;jy+=4*Math.floor(days/1461);days%=1461;if(days>365){jy+=Math.floor((days-1)/365);days=(days-1)%365}var jm=days<186?1+Math.floor(days/31):7+Math.floor((days-186)/30),jd=1+(days<186?days%31:(days-186)%30);return[jy,jm,jd]}
function j2g(jy,jm,jd){var gy;if(jy>979){gy=1600;jy-=979}else gy=621;var days=365*jy+Math.floor(jy/33)*8+Math.floor((jy%33+3)/4)+78+jd+(jm<7?(jm-1)*31:(jm-7)*30+186);gy+=400*Math.floor(days/146097);days%=146097;if(days>36524){days--;gy+=100*Math.floor(days/36524);days%=36524;if(days>=365)days++}gy+=4*Math.floor(days/1461);days%=1461;if(days>365){gy+=Math.floor((days-1)/365);days=(days-1)%365}var gd=days+1,sa=[0,31,(gy%4==0&&gy%100!=0)||gy%400==0?29:28,31,30,31,30,31,31,30,31,30,31],gm;for(gm=0;gm<13;gm++){if(gd<=sa[gm])break;gd-=sa[gm]}return[gy,gm,gd]}
const MN=["فروردین","اردیبهشت","خرداد","تیر","مرداد","شهریور","مهر","آبان","آذر","دی","بهمن","اسفند"];
function mlen(y,m){if(m<7)return 31;if(m<12)return 30;var g=j2g(y,12,30),r=g2j(g[0],g[1],g[2]);return r[1]==12&&r[2]==30?30:29}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const themeButton = document.getElementById('th');
    const searchInput = document.getElementById('gs');
    const main = document.querySelector('.workspace-content');

    const applyTheme = (value) => {
        const theme = value === 'dark' ? 'dark' : 'light';
        root.dataset.theme = theme;
        try { localStorage.setItem('sheykhan.panel.theme', theme); localStorage.setItem('theme', theme); } catch (_) {}
        if (themeButton) {
            themeButton.setAttribute('aria-pressed', String(theme === 'dark'));
            themeButton.setAttribute('aria-label', theme === 'dark' ? 'فعال‌کردن تم روشن' : 'فعال‌کردن تم تیره');
            themeButton.title = theme === 'dark' ? 'تم روشن' : 'تم تیره';
        }
    };

    let savedTheme = root.dataset.theme === 'dark' ? 'dark' : 'light';
    try {
        const stored = localStorage.getItem('sheykhan.panel.theme') || localStorage.getItem('theme') || localStorage.getItem('sheykhan.student.theme');
        if (stored === 'dark' || stored === 'light') savedTheme = stored;
    } catch (_) {}
    applyTheme(savedTheme);
    themeButton?.addEventListener('click', () => applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark'));

    if (searchInput && main) {
        const emptySearchState = main.querySelector('[data-dashboard-search-empty]');
        const emptyStates = Array.from(main.querySelectorAll('.empty-state'));
        const normalize = (value) => String(value || '')
            .toLocaleLowerCase('fa')
            .replace(/[يى]/g, 'ی')
            .replace(/ك/g, 'ک')
            .normalize('NFKC')
            .trim();

        const collectTargets = () => {
            const candidates = Array.from(main.querySelectorAll(
                '[data-dashboard-searchable], .teacher-workspace-item, .teacher-workspace-card, tbody tr, .tl, .ls'
            ));
            return candidates.filter((element) => !candidates.some((parent) =>
                parent !== element && parent.contains(element)
            ));
        };

        searchInput.addEventListener('input', () => {
            const query = normalize(searchInput.value);
            let visibleCount = 0;

            collectTargets().forEach((element) => {
                const matches = !query || normalize(element.textContent).includes(query);
                element.hidden = !matches;
                if (matches) visibleCount += 1;
            });

            // Empty-state copy describes the unfiltered dashboard, not a search result.
            emptyStates.forEach((element) => { element.hidden = Boolean(query); });
            if (emptySearchState) emptySearchState.hidden = !query || visibleCount > 0;
        });
    }

    // Only Laravel-provided chart JSON is rendered here; no sample data is generated.
    document.querySelectorAll('[data-dashboard-chart]').forEach((chart) => {
        const bars = chart.querySelector('[data-chart-bars]');
        const emptyState = chart.querySelector('[data-chart-empty]');
        const buttons = Array.from(chart.querySelectorAll('[data-chart-range]'));
        if (!bars || buttons.length === 0) return;
        let data = {};
        try { data = JSON.parse(chart.getAttribute('data-chart') || '{}'); } catch (_) {}

        const render = (range) => {
            const selected = data[range] || { labels: [], values: [] };
            const labels = Array.isArray(selected.labels) ? selected.labels : [];
            const values = Array.isArray(selected.values) ? selected.values : [];
            const count = Math.min(labels.length, values.length);
            bars.replaceChildren();
            const hasProgressData = count > 0 && values.slice(0, count).some((value) => Number(value) > 0);
            if (!hasProgressData) {
                if (emptyState) emptyState.hidden = false;
                return;
            }
            for (let index = 0; index < count; index += 1) {
                const value = Math.max(0, Math.min(100, Number(values[index]) || 0));
                const column = document.createElement('div');
                const number = document.createElement('b');
                const fill = document.createElement('i');
                const label = document.createElement('small');
                number.textContent = fa(Math.round(value)) + '٪';
                fill.style.height = value + '%';
                label.textContent = String(labels[index] ?? '');
                column.append(number, fill, label);
                bars.appendChild(column);
            }
            if (emptyState) emptyState.hidden = hasProgressData;
        };
        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const range = button.dataset.chartRange || 'week';
                buttons.forEach((item) => {
                    const active = item === button;
                    item.classList.toggle('on', active);
                    item.setAttribute('aria-pressed', String(active));
                });
                render(range);
            });
        });
        render('week');
    });

    // Optional Jalali text labels are derived from ISO dates; form values remain unchanged.
    document.querySelectorAll('[data-jalali-date]').forEach((element) => {
        const raw = element.getAttribute('data-jalali-date');
        const match = raw && raw.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!match) return;
        const converted = g2j(Number(match[1]), Number(match[2]), Number(match[3]));
        element.textContent = fa(converted[0]) + ' ' + MN[converted[1] - 1] + ' ' + fa(converted[2]);
    });

    document.addEventListener('click', (event) => {
        const target = event.target.closest('[data-confirm]');
        if (!target) return;
        if (!window.confirm(target.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟')) {
            event.preventDefault();
            event.stopPropagation();
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.dataset.confirmSubmit !== 'true') return;
        if (!window.confirm(form.dataset.confirm || 'از انجام این عملیات مطمئن هستید؟')) event.preventDefault();
    });

    document.querySelectorAll('[data-teacher-preview-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const kind = input.dataset.teacherPreviewInput;
            const file = input.files?.[0];
            const preview = document.querySelector('[data-teacher-preview-container="' + kind + '"]');
            const feedback = document.querySelector('[data-teacher-preview-feedback="' + kind + '"]');
            if (!file || !preview) return;

            const maxSizeMb = Number(input.dataset.maxSizeMb || 0);
            if (!file.type.startsWith('image/')) {
                if (feedback) feedback.textContent = 'لطفاً یک فایل تصویری معتبر انتخاب کنید.';
                input.value = '';
                return;
            }
            if (maxSizeMb > 0 && file.size > maxSizeMb * 1024 * 1024) {
                if (feedback) feedback.textContent = 'حجم فایل نباید بیشتر از ' + fa(maxSizeMb) + ' مگابایت باشد.';
                input.value = '';
                return;
            }

            const url = URL.createObjectURL(file);
            const image = document.createElement('img');
            image.src = url;
            image.alt = kind === 'avatar' ? 'پیش‌نمایش آواتار انتخاب‌شده' : 'پیش‌نمایش کاور انتخاب‌شده';
            image.dataset.teacherPreviewImage = kind;
            image.decoding = 'async';
            image.onload = () => URL.revokeObjectURL(url);
            image.onerror = () => {
                URL.revokeObjectURL(url);
                image.remove();
                if (feedback) feedback.textContent = 'نمایش این تصویر ممکن نشد؛ فایل دیگری انتخاب کنید.';
            };

            if (kind === 'cover') {
                preview.classList.remove('no-cover');
                preview.classList.add('has-cover');
                preview.querySelectorAll('img').forEach((existing) => existing.remove());
                const label = preview.querySelector('.teacher-cover-preview-label');
                if (label) preview.insertBefore(image, label);
                else preview.prepend(image);
            } else {
                preview.replaceChildren(image);
            }

            if (feedback) {
                feedback.textContent = file.name + ' · ' + fa(Math.ceil(file.size / 1024)) + ' کیلوبایت · پیش‌نمایش آماده است.';
            }
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-overlay-close]')) document.body.classList.remove('op1');
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') document.body.classList.remove('op1');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    // Preview profile images before upload; the server remains authoritative for validation.
    const profileForm = document.querySelector('[data-teacher-profile-form]');
    if (profileForm) {
        ['avatar', 'cover'].forEach((kind) => {
            const input = profileForm.querySelector(`[data-teacher-preview-input="${kind}"]`);
            const preview = profileForm.querySelector(`[data-teacher-preview-container="${kind}"]`);
            const feedback = profileForm.querySelector(`[data-teacher-preview-feedback="${kind}"]`);
            if (!input || !preview) return;

            let previewUrl = null;
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) return;

                const maxBytes = Number(input.dataset.maxSizeMb || (kind === 'avatar' ? 3 : 5)) * 1024 * 1024;
                const looksLikeImage = file.type.startsWith('image/')
                    || /\\.(jpe?g|png|webp|avif)$/i.test(file.name);

                if (!looksLikeImage || file.size > maxBytes) {
                    input.value = '';
                    if (feedback) {
                        feedback.textContent = !looksLikeImage
                            ? 'لطفاً یک فایل تصویری معتبر انتخاب کن.'
                            : `حجم تصویر نباید بیشتر از ${kind === 'avatar' ? '۳' : '۵'} مگابایت باشد.`;
                    }
                    return;
                }

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                previewUrl = URL.createObjectURL(file);

                let image = preview.querySelector('img');
                if (!image) {
                    image = document.createElement('img');
                    image.alt = kind === 'avatar' ? 'پیش‌نمایش آواتار مدرس' : '';
                    preview.replaceChildren(image);
                }
                image.src = previewUrl;

                if (kind === 'cover') {
                    preview.classList.remove('no-cover');
                    preview.classList.add('has-cover');
                    let label = preview.querySelector('.teacher-cover-preview-label');
                    if (!label) {
                        label = document.createElement('span');
                        label.className = 'teacher-cover-preview-label';
                        preview.appendChild(label);
                    }
                    label.textContent = 'پیش‌نمایش کاور';
                }

                if (feedback) {
                    feedback.textContent = `پیش‌نمایش «${file.name}» آماده است؛ برای ثبت نهایی، ذخیره پروفایل را بزن.`;
                }
            });

            window.addEventListener('pagehide', () => {
                if (previewUrl) URL.revokeObjectURL(previewUrl);
            }, { once: true });
        });
    }

    document.querySelectorAll('[data-copy-schedule-link]').forEach((button) => {
        button.addEventListener('click', async () => {
            const value = button.dataset.copyScheduleLink || '';
            if (!value) return;

            try {
                await navigator.clipboard.writeText(value);
                const previous = button.textContent;
                button.textContent = 'کپی شد ✓';
                window.setTimeout(() => {
                    button.textContent = previous;
                }, 1600);
            } catch {
                window.prompt('لینک جلسه را کپی کنید:', value);
            }
        });
    });

    // Resolve the provider immediately in the form. Jitsi can use a unique
    // room name without API credentials; Google Meet and Zoom need a real URL
    // created by the user in the provider's own service.
    document.querySelectorAll('[data-meeting-provider-form]').forEach((form) => {
        const providerSelect = form.querySelector('[data-meeting-provider]');
        const meetingUrl = form.querySelector('[data-meeting-url]');
        const help = form.querySelector('[data-meeting-provider-help]');

        if (!providerSelect || !meetingUrl) return;

        const makeJitsiUrl = () => {
            const bytes = new Uint8Array(18);

            if (window.crypto?.getRandomValues) {
                window.crypto.getRandomValues(bytes);
            } else {
                for (let index = 0; index < bytes.length; index++) {
                    bytes[index] = Math.floor(Math.random() * 256);
                }
            }

            const roomId = Array.from(bytes, (byte) => byte.toString(16).padStart(2, '0')).join('');
            return 'https://meet.jit.si/Sheykhan-' + roomId;
        };

        const isJitsiUrl = (value) => {
            try {
                const url = new URL(value);
                return url.protocol === 'https:' && url.hostname === 'meet.jit.si';
            } catch {
                return false;
            }
        };

        const syncMeetingProvider = () => {
            const provider = providerSelect.value;

            if (provider === 'Jitsi') {
                if (!isJitsiUrl(meetingUrl.value)) {
                    meetingUrl.value = makeJitsiUrl();
                }

                meetingUrl.readOnly = true;
                meetingUrl.required = false;
                meetingUrl.placeholder = 'لینک یکتای Jitsi ساخته شد';
                if (help) {
                    help.textContent = 'لینک یکتای Jitsi همین حالا ساخته شد. این لینک برای همین جلسه یا بازه هفتگی ثابت می‌ماند.';
                }
                return;
            }

            if (isJitsiUrl(meetingUrl.value)) {
                meetingUrl.value = '';
            }

            meetingUrl.readOnly = false;
            meetingUrl.required = Boolean(provider);

            const guidance = {
                'Google Meet': 'جلسه را در Google Meet بساز، لینک واقعی meet.google.com را کپی کن و اینجا قرار بده. شیخان بدون اتصال OAuth/Calendar نمی‌تواند از طرف حساب گوگل جلسه بسازد.',
                'Zoom': 'جلسه را در Zoom بساز، لینک شرکت‌کننده را کپی کن و اینجا قرار بده. ساخت خودکار جلسه Zoom به اتصال رسمی و مجوز حساب نیاز دارد که فعلاً تنظیم نشده است.',
                'سایر': 'لینک HTTPS واقعی سرویس را اینجا قرار بده.',
            };

            meetingUrl.placeholder = provider === 'Google Meet'
                ? 'https://meet.google.com/abc-defg-hij'
                : (provider === 'Zoom' ? 'https://zoom.us/j/...' : 'https://...');
            if (help) {
                help.textContent = guidance[provider] || 'ابتدا سرویس را انتخاب کن.';
            }
        };

        providerSelect.addEventListener('change', syncMeetingProvider);
        syncMeetingProvider();
    });

    const teacherJalaliFields = document.querySelectorAll('[data-teacher-jalali]');

    const teacherNormalizeDigits = (value) => String(value ?? '')
        .replace(/[۰-۹]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
        .replace(/[٠-٩]/g, (d) => '٠١٢٣٤٥٦٧٨٩'.indexOf(d));

    const teacherGregorianToJalali = (value) => {
        if (!value) return '';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return '';

        return new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
            timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }).format(date);
    };

    const teacherJalaliToGregorian = (value) => {
        const normalized = teacherNormalizeDigits(value).replace(/-/g, '/').trim();
        const match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
        if (!match) return null;

        const jy = Number(match[1]);
        const jm = Number(match[2]);
        const jd = Number(match[3]);
        const formatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
            timeZone: 'UTC',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        });

        const start = new Date(Date.UTC(jy + 620, 0, 1, 12));
        for (let offset = 0; offset <= 800; offset++) {
            const candidate = new Date(start.getTime() + offset * 86400000);
            const parts = Object.fromEntries(
                formatter.formatToParts(candidate).map((item) => [item.type, item.value])
            );

            if (Number(teacherNormalizeDigits(parts.year)) === jy
                && Number(teacherNormalizeDigits(parts.month)) === jm
                && Number(teacherNormalizeDigits(parts.day)) === jd) {
                return [
                    candidate.getUTCFullYear(),
                    String(candidate.getUTCMonth() + 1).padStart(2, '0'),
                    String(candidate.getUTCDate()).padStart(2, '0'),
                ];
            }
        }

        return null;
    };

    teacherJalaliFields.forEach((field) => {
        const visibleDate = field.querySelector('[data-jalali-visible-date]');
        const visibleTime = field.querySelector('[data-jalali-visible-time]');
        const target = field.querySelector('[data-jalali-target]');
        const dateOnly = field.dataset.jalaliDateOnly === '1';

        if (!visibleDate || !target) return;

        const hydrate = () => {
            const raw = target.value;
            if (!raw) return;

            const match = String(raw).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2}))?/);
            if (!match) return;

            const jalali = teacherGregorianToJalali(
                match[4] ? `${match[1]}-${match[2]}-${match[3]}T${match[4]}:${match[5]}` : `${match[1]}-${match[2]}-${match[3]}T12:00`
            );

            if (jalali) visibleDate.value = jalali;
            if (visibleTime && match[4]) visibleTime.value = `${match[4]}:${match[5]}`;
        };

        const sync = () => {
            const gregorian = teacherJalaliToGregorian(visibleDate.value);
            if (!gregorian) {
                target.value = '';
                return false;
            }

            if (dateOnly) {
                target.value = gregorian.join('-');
                return true;
            }

            if (!visibleTime?.value) {
                target.value = '';
                return false;
            }

            target.value = `${gregorian.join('-')}T${visibleTime.value}`;
            return true;
        };

        hydrate();
        visibleDate.addEventListener('change', sync);
        visibleDate.addEventListener('blur', sync);
        visibleTime?.addEventListener('change', sync);

        field.closest('form')?.addEventListener('submit', (event) => {
            if (!sync()) {
                event.preventDefault();
                visibleDate.focus();
            }
        });
    });

    const liveForm = document.querySelector('[data-live-class-form]');

    if (liveForm) {
        const dateTrigger = liveForm.querySelector('[data-jalali-trigger]');
        const picker = liveForm.querySelector('[data-jalali-picker]');
        const days = liveForm.querySelector('[data-jalali-days]');
        const monthLabel = liveForm.querySelector('[data-jalali-month-label]');
        const hiddenDate = liveForm.querySelector('[data-jalali-date]');
        const hiddenScheduledAt = liveForm.querySelector('[data-scheduled-at]');
        const timeInput = liveForm.querySelector('[data-scheduled-time]');
        const monthNames = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
        const digits = (value) => String(value).replace(/[0-9]/g, (d) => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
        const pad = (value) => String(value).padStart(2, '0');
        const isLeapGregorian = (year) => (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;

        const jalaliToGregorian = (jy, jm, jd) => {
            let y = jy + 1595;
            let daysValue = -355668 + (365 * y) + Math.floor(y / 33) * 8 + Math.floor(((y % 33) + 3) / 4) + jd;
            daysValue += jm < 7 ? (jm - 1) * 31 : ((jm - 7) * 30) + 186;

            let gy = 400 * Math.floor(daysValue / 146097);
            daysValue %= 146097;

            if (daysValue > 36524) {
                gy += 100 * Math.floor(--daysValue / 36524);
                daysValue %= 36524;
                if (daysValue >= 365) daysValue++;
            }

            gy += 4 * Math.floor(daysValue / 1461);
            daysValue %= 1461;

            if (daysValue > 365) {
                gy += Math.floor((daysValue - 1) / 365);
                daysValue = (daysValue - 1) % 365;
            }

            let gd = daysValue + 1;
            const monthDays = [31, isLeapGregorian(gy) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            let gm = 1;

            while (gm <= 12 && gd > monthDays[gm - 1]) {
                gd -= monthDays[gm - 1];
                gm++;
            }

            return [gy, gm, gd];
        };

        const jalaliMonthDays = (year, month) => {
            if (month <= 6) return 31;
            if (month <= 11) return 30;

            // Validate Esfand 30 with the browser's Persian calendar implementation.
            const [gy, gm, gd] = jalaliToGregorian(year, 12, 30);
            const candidate = new Date(Date.UTC(gy, gm - 1, gd, 12));
            const parts = Object.fromEntries(
                new Intl.DateTimeFormat('en-u-ca-persian-nu-latn', {
                    timeZone: 'UTC',
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                }).formatToParts(candidate).map((part) => [part.type, part.value])
            );

            return Number(parts.year) === year
                && Number(parts.month) === 12
                && Number(parts.day) === 30
                ? 30
                : 29;
        };

        let viewYear = Number(liveForm.dataset.jalaliYear);
        let viewMonth = Number(liveForm.dataset.jalaliMonth);
        let selected = hiddenDate.value || '';

        const syncScheduledAt = () => {
            if (!hiddenDate || !timeInput || !hiddenScheduledAt || !hiddenDate.value || !timeInput.value) return;
            const [jy, jm, jd] = hiddenDate.value.split('/').map(Number);
            const [gy, gm, gd] = jalaliToGregorian(jy, jm, jd);
            hiddenScheduledAt.value = `${gy}-${pad(gm)}-${pad(gd)}T${timeInput.value}`;
        };

        const renderPicker = () => {
            if (!days || !monthLabel) return;

            monthLabel.textContent = `${monthNames[viewMonth - 1]} ${digits(viewYear)}`;
            days.innerHTML = '';

            const [firstGy, firstGm, firstGd] = jalaliToGregorian(viewYear, viewMonth, 1);
            const firstWeekday = (new Date(firstGy, firstGm - 1, firstGd).getDay() + 1) % 7;

            for (let i = 0; i < firstWeekday; i++) {
                days.appendChild(document.createElement('i'));
            }

            for (let day = 1; day <= jalaliMonthDays(viewYear, viewMonth); day++) {
                const button = document.createElement('button');
                const value = `${viewYear}/${pad(viewMonth)}/${pad(day)}`;
                button.type = 'button';
                button.textContent = digits(day);
                button.className = value === selected ? 'is-selected' : '';
                button.addEventListener('click', () => {
                    selected = value;
                    hiddenDate.value = value;
                    dateTrigger.querySelector('b').textContent = `${digits(viewYear)}/${digits(pad(viewMonth))}/${digits(pad(day))}`;
                    picker.hidden = true;
                    syncScheduledAt();
                    renderPicker();
                });
                days.appendChild(button);
            }
        };

        if (selected) {
            const parts = selected.split('/').map(Number);
            if (parts.length === 3) {
                viewYear = parts[0];
                viewMonth = parts[1];
                dateTrigger.querySelector('b').textContent = parts.map((part) => digits(pad(part))).join('/');
            }
        }

        dateTrigger?.addEventListener('click', () => {
            picker.hidden = !picker.hidden;
            if (!picker.hidden) renderPicker();
        });

        liveForm.querySelector('[data-jalali-prev]')?.addEventListener('click', () => {
            viewMonth--;
            if (viewMonth < 1) { viewMonth = 12; viewYear--; }
            renderPicker();
        });

        liveForm.querySelector('[data-jalali-next]')?.addEventListener('click', () => {
            viewMonth++;
            if (viewMonth > 12) { viewMonth = 1; viewYear++; }
            renderPicker();
        });

        timeInput?.addEventListener('change', syncScheduledAt);
        liveForm.addEventListener('submit', (event) => {
            syncScheduledAt();
            if (!hiddenScheduledAt?.value) {
                event.preventDefault();
                dateTrigger?.focus();
            }
        });

        renderPicker();
    }


});

// Shared exam builder for teacher and academy owner screens.
const initializeExamBuilder = () => {
    document.querySelectorAll('[data-course-classroom-select]').forEach((classroomSelect) => {
        const courseSelect = document.querySelector(classroomSelect.dataset.courseSelect);
        if (!courseSelect) return;

        const syncClassrooms = () => {
            const courseId = courseSelect.value;
            Array.from(classroomSelect.options).forEach((option) => {
                const matches = !option.dataset.courseId || option.dataset.courseId === courseId;
                option.hidden = !matches;
                if (!matches && option.selected) classroomSelect.value = '';
            });
        };

        courseSelect.addEventListener('change', syncClassrooms);
        syncClassrooms();
    });

    const examBuilder = document.querySelector('[data-exam-builder]');
    if (!examBuilder) return;

    const container = examBuilder.querySelector('[data-exam-questions]');
    const template = examBuilder.querySelector('[data-exam-question-template]');
    const addButton = examBuilder.querySelector('[data-exam-add-question]');
    if (!container || !template || !addButton) return;

    const toFaDigits = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
    const formatScore = (value) => {
        const rounded = Number.isFinite(value) ? Math.round(value * 100) / 100 : 0;
        return toFaDigits(String(rounded));
    };

    const typeLabel = (type) => ({
        text: 'تشریحی',
        single: 'چهارگزینه‌ای',
        multiple: 'چندپاسخی',
        checkbox: 'چندپاسخی',
    }[type] || 'تشریحی');

    const readQuestionOptions = (item) => {
        const entries = [...item.querySelectorAll('[data-option-entry]')];
        if (entries.length) {
            return entries.map((field) => field.value.trim()).filter(Boolean);
        }

        // Compatibility with a legacy question block that may still contain
        // the previous newline-separated options editor.
        const field = item.querySelector('[data-name="options_text"]');
        return field
            ? field.value.split(/\r?\n/).map((value) => value.trim()).filter(Boolean)
            : [];
    };

    const serializeOptions = (item, index, options) => {
        item.querySelectorAll('[data-option-hidden]').forEach((node) => node.remove());

        options.forEach((value, optionIndex) => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.dataset.optionHidden = '1';
            hidden.name = `questions[${index}][options][${optionIndex}]`;
            hidden.value = value;
            item.appendChild(hidden);
        });
    };

    const renderCorrectAnswer = (item, index, type, options, previousSelection, requireCorrectAnswer) => {
        const wrapper = item.querySelector('[data-question-correct]');
        const choices = item.querySelector('[data-correct-answer-choices]');
        const label = item.querySelector('[data-correct-answer-label]');
        const help = item.querySelector('[data-correct-answer-help]');
        if (!wrapper || !choices) return;

        wrapper.classList.toggle('hidden', type === 'text');
        choices.replaceChildren();
        if (type === 'text') return;

        const multi = type === 'multiple' || type === 'checkbox';
        if (label) {
            label.textContent = multi
                ? 'پاسخ‌های صحیح (سؤال چندپاسخی قدیمی)'
                : 'کلید پاسخ · یک گزینهٔ صحیح';
        }

        if (!options.length) {
            const empty = document.createElement('p');
            empty.className = 'teacher-exam-answer-key-empty';
            empty.textContent = 'ابتدا چهار گزینه را کامل کن تا کلید پاسخ ساخته شود.';
            choices.appendChild(empty);
        } else {
            options.forEach((option, optionIndex) => {
                const row = document.createElement('label');
                row.className = 'teacher-exam-correct-choice';

                const input = document.createElement('input');
                input.type = multi ? 'checkbox' : 'radio';
                input.name = multi
                    ? `questions[${index}][correct_answer][]`
                    : `questions[${index}][correct_answer]`;
                input.value = option;
                input.dataset.correctAnswerChoice = '1';
                input.checked = previousSelection.includes(option);
                input.setAttribute('aria-label', `پاسخ صحیح: ${option}`);
                if (!multi) input.required = Boolean(requireCorrectAnswer);

                const marker = document.createElement('span');
                marker.className = 'teacher-exam-correct-choice-marker';
                marker.textContent = ['الف', 'ب', 'ج', 'د'][optionIndex] || toFaDigits(optionIndex + 1);

                const copy = document.createElement('span');
                copy.className = 'teacher-exam-correct-choice-copy';
                copy.textContent = option;

                const check = document.createElement('span');
                check.className = 'teacher-exam-correct-choice-check';
                check.setAttribute('aria-hidden', 'true');
                check.textContent = '✓';

                row.append(marker, copy, input, check);
                choices.appendChild(row);
            });
        }

        if (help) {
            help.textContent = multi
                ? 'این سؤال قدیمی با مجموعهٔ گزینه‌های صحیح تصحیح می‌شود.'
                : 'دانش‌آموز فقط یکی از چهار گزینه را انتخاب می‌کند؛ پاسخ صحیح در تصحیح خودکار استفاده می‌شود.';
        }
    };

    const updatePaperSummary = () => {
        const questions = [...container.querySelectorAll('[data-exam-question]')];
        const total = questions.reduce((sum, item) => {
            const score = Number(item.querySelector('[data-name="score"]')?.value || 0);
            return sum + (Number.isFinite(score) && score > 0 ? score : 0);
        }, 0);

        examBuilder.querySelectorAll('[data-exam-question-count]').forEach((node) => {
            node.textContent = toFaDigits(questions.length);
        });
        examBuilder.querySelectorAll('[data-exam-total-score]').forEach((node) => {
            node.textContent = formatScore(total);
        });

        const titleInput = examBuilder.querySelector('[data-exam-title]');
        const title = titleInput?.value.trim() || 'عنوان آزمون شما';
        const titlePreview = examBuilder.querySelector('[data-exam-preview-title]');
        if (titlePreview) titlePreview.textContent = title;

        const courseSelect = examBuilder.querySelector('[data-course-select]');
        const coursePreview = examBuilder.querySelector('[data-exam-preview-course]');
        if (coursePreview) {
            coursePreview.textContent = courseSelect?.selectedOptions?.[0]?.textContent?.trim()
                ? `دوره: ${courseSelect.selectedOptions[0].textContent.trim()}`
                : 'دورهٔ انتخاب‌شده';
        }

        const durationInput = examBuilder.querySelector('[data-exam-duration]');
        const durationPreview = examBuilder.querySelector('[data-exam-preview-duration]');
        if (durationPreview) {
            const duration = Number(durationInput?.value || 0);
            durationPreview.textContent = duration > 0 ? `${toFaDigits(duration)} دقیقه` : 'بدون محدودیت';
        }

        const descriptionInput = examBuilder.querySelector('[data-exam-description]');
        const descriptionPreview = examBuilder.querySelector('[data-exam-preview-description]');
        if (descriptionPreview) {
            const description = descriptionInput?.value.trim() || '';
            descriptionPreview.textContent = description;
            descriptionPreview.hidden = !description;
        }

        questions.forEach((item, index) => {
            const number = item.querySelector('[data-question-number]');
            const kind = item.querySelector('[data-question-kind]');
            const type = item.querySelector('[data-name="type"]')?.value || 'text';
            const scoreInput = item.querySelector('[data-name="score"]');
            const score = Number(scoreInput?.value || 0);
            const printScore = item.querySelector('[data-print-score]');
            const foot = item.querySelector('[data-question-foot-note]');
            if (number) number.textContent = `سؤال ${toFaDigits(index + 1)}`;
            if (printScore) printScore.textContent = `${formatScore(score)} نمره`;
            item.dataset.questionType = type;
            if (kind) {
                kind.textContent = typeLabel(type);
                kind.dataset.questionType = type;
            }
            if (foot) {
                foot.textContent = type === 'text'
                    ? 'دانش‌آموز فضای نوشتن پاسخ دارد؛ پاسخ مرجع فقط برای مدرس است.'
                    : type === 'single'
                        ? 'در برگهٔ دانش‌آموز چهار گزینه با انتخاب تک‌پاسخ نمایش داده می‌شود.'
                        : 'این سؤال از نوع چندپاسخی قدیمی است.';
            }
        });
    };

    const syncQuestion = (item, index) => {
        item.querySelectorAll('[data-name]').forEach((field) => {
            if (field.dataset.name === 'options_text') {
                field.removeAttribute('name');
                return;
            }
            field.name = `questions[${index}][${field.dataset.name}]`;
        });

        const typeField = item.querySelector('[data-name="type"]');
        const optionsWrapper = item.querySelector('[data-question-options]');
        const referenceWrapper = item.querySelector('[data-question-reference]');
        const type = typeField?.value || 'text';
        const options = readQuestionOptions(item);
        const questionPrompt = item.querySelector('[data-name="question"]');
        const referenceAnswer = item.querySelector('[data-name="expected_answer"]');
        const status = examBuilder.querySelector('[name="status"]')?.value || 'draft';
        const hasDraftContent = Boolean(
            questionPrompt?.value.trim()
            || options.length
            || referenceAnswer?.value.trim()
        );
        const isEmptyDraftRow = status === 'draft' && !hasDraftContent;
        const answerSpace = item.querySelector('[data-answer-space-preview]');
        if (answerSpace) answerSpace.classList.toggle('hidden', type !== 'text');
        const previousSelection = [...item.querySelectorAll('[data-correct-answer-choice]:checked')]
            .map((input) => input.value);

        if (questionPrompt) questionPrompt.required = !isEmptyDraftRow;
        if (optionsWrapper) optionsWrapper.classList.toggle('hidden', type === 'text');
        if (referenceWrapper) referenceWrapper.classList.toggle('hidden', type !== 'text');

        // An incomplete draft can be saved and resumed later. Publishing turns
        // on the stricter browser checks that mirror server-side validation.
        item.querySelectorAll('[data-option-entry]').forEach((field) => {
            field.required = status === 'published' && type === 'single';
            field.setAttribute('aria-required', field.required ? 'true' : 'false');
        });

        const optionText = item.querySelector('[data-name="options_text"]');
        if (optionText && type === 'text') optionText.removeAttribute('required');

        serializeOptions(item, index, type === 'text' ? [] : options);
        renderCorrectAnswer(item, index, type, options, previousSelection, status === 'published');
    };

    const syncQuestions = () => {
        container.querySelectorAll('[data-exam-question]').forEach((item, index) => {
            syncQuestion(item, index);
        });
        updatePaperSummary();
    };

    container.addEventListener('input', (event) => {
        const field = event.target.closest('[data-option-entry], [data-name="options_text"], [data-name="score"], [data-name="question"], [data-name="expected_answer"]');
        if (!field) return;
        const item = field.closest('[data-exam-question]');
        if (!item) return;
        const index = [...container.querySelectorAll('[data-exam-question]')].indexOf(item);
        syncQuestion(item, index);
        updatePaperSummary();
    });

    container.addEventListener('change', (event) => {
        const typeField = event.target.closest('[data-name="type"]');
        if (typeField) {
            const item = typeField.closest('[data-exam-question]');
            if (item) {
                const index = [...container.querySelectorAll('[data-exam-question]')].indexOf(item);
                syncQuestion(item, index);
                updatePaperSummary();
            }
        }
    });

    examBuilder.querySelector('[name="status"]')?.addEventListener('change', syncQuestions);

    examBuilder.addEventListener('submit', () => {
        if (examBuilder.querySelector('[name="status"]')?.value !== 'draft') return;

        const blankRows = [...container.querySelectorAll('[data-exam-question]')].filter((item) => {
            const question = item.querySelector('[data-name="question"]')?.value.trim() || '';
            const answer = item.querySelector('[data-name="expected_answer"]')?.value.trim() || '';
            const options = readQuestionOptions(item);
            return !question && !answer && options.length === 0;
        });

        blankRows.forEach((item) => item.remove());
        if (blankRows.length) syncQuestions();
    });

    container.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-exam-remove-question]');
        if (!removeButton) return;
        event.preventDefault();
        const item = removeButton.closest('[data-exam-question]');
        if (!item) return;
        item.remove();
        syncQuestions();
    });

    const addQuestion = (initial = {}, focus = true) => {
        container.appendChild(template.content.cloneNode(true));
        const questionItems = container.querySelectorAll('[data-exam-question]');
        const item = questionItems[questionItems.length - 1];
        if (!item) return;

        const prompt = item.querySelector('[data-name="question"]');
        const type = item.querySelector('[data-name="type"]');
        const score = item.querySelector('[data-name="score"]');
        const referenceAnswer = item.querySelector('[data-name="expected_answer"]');

        if (prompt) prompt.value = typeof initial.question === 'string' ? initial.question : '';
        const initialType = initial.type === 'multiple_choice' ? 'single' : initial.type;
        if (type && ['text', 'single', 'multiple', 'checkbox'].includes(initialType)) {
            type.value = initialType;
        }
        if (score && initial.score !== undefined && initial.score !== null) score.value = initial.score;
        if (referenceAnswer && typeof initial.expected_answer === 'string') {
            referenceAnswer.value = initial.expected_answer;
        }

        const entries = [...item.querySelectorAll('[data-option-entry]')];
        const initialOptions = Array.isArray(initial.options)
            ? initial.options
            : (typeof initial.options_text === 'string'
                ? initial.options_text.split(/\r?\n/)
                : []);
        entries.forEach((field, index) => {
            field.value = typeof initialOptions[index] === 'string' ? initialOptions[index] : '';
        });

        syncQuestions();

        const expected = Array.isArray(initial.correct_answer)
            ? initial.correct_answer.map(String)
            : (initial.correct_answer === undefined || initial.correct_answer === null
                ? []
                : [String(initial.correct_answer)]);

        item.querySelectorAll('[data-correct-answer-choice]').forEach((input) => {
            input.checked = expected.includes(input.value);
        });

        if (focus) prompt?.focus();
    };

    addButton.addEventListener('click', () => addQuestion({}, true));

    let initialQuestions = [];
    try {
        const raw = JSON.parse(examBuilder.dataset.initialQuestions || '[]');
        initialQuestions = Array.isArray(raw)
            ? raw.filter((question) => question && typeof question === 'object')
            : [];
    } catch {
        initialQuestions = [];
    }

    examBuilder.querySelector('[data-print-exam]')?.addEventListener('click', () => window.print());
    examBuilder.querySelector('[data-exam-title]')?.addEventListener('input', updatePaperSummary);
    examBuilder.querySelector('[data-course-select]')?.addEventListener('change', updatePaperSummary);
    examBuilder.querySelector('[data-exam-duration]')?.addEventListener('input', updatePaperSummary);
    examBuilder.querySelector('[data-exam-description]')?.addEventListener('input', updatePaperSummary);

    if (initialQuestions.length) {
        initialQuestions.forEach((question) => addQuestion(question, false));
    } else {
        addQuestion({}, true);
    }

    updatePaperSummary();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeExamBuilder, { once: true });
} else {
    initializeExamBuilder();
}


/* Teacher-local Shaka player implementation. */
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
