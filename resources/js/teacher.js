/* Teacher-only runtime. Role UI and behavior are isolated. */
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
import './teacher/persian-calendar.js';
import './teacher/persian-money.js';
import './teacher/assessment-builder.js';
import { bootFileUploadProgress } from './teacher/upload-progress.js';
import './teacher/course-media-uploader.js';
document.addEventListener('DOMContentLoaded', () => bootFileUploadProgress());
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
        try { localStorage.setItem('sheykhan.teacher.theme', theme);  } catch (_) {}
        if (themeButton) {
            themeButton.setAttribute('aria-pressed', String(theme === 'dark'));
            themeButton.setAttribute('aria-label', theme === 'dark' ? 'فعال‌کردن تم روشن' : 'فعال‌کردن تم تیره');
            themeButton.title = theme === 'dark' ? 'تم روشن' : 'تم تیره';
        }
    };

    let savedTheme = root.dataset.theme === 'dark' ? 'dark' : 'light';
    try {
        const stored = localStorage.getItem('sheykhan.teacher.theme');
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
                '[data-dashboard-searchable], .teacher-workspace-item, .teacher-workspace-card, tbody tr, .tl, .ls, .row, .teacher-stat-card, .teacher-quick-action, .weekly-calendar-event, .teacher-exam-attempt-card, .teacher-student-row, .teacher-course-card'
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

document.addEventListener('DOMContentLoaded', () => {
  const side=document.getElementById('side'), overlay=document.getElementById('teacher-overlay');
  const toggleMenu=(open)=>{side?.classList.toggle('open',!!open);overlay?.classList.toggle('open',!!open);overlay?.setAttribute('aria-hidden',String(!open));};
  document.querySelectorAll('[data-teacher-menu-open]').forEach(x=>x.addEventListener('click',()=>toggleMenu(true)));
  document.querySelectorAll('[data-teacher-menu-close]').forEach(x=>x.addEventListener('click',()=>toggleMenu(false)));
  overlay?.addEventListener('click',()=>toggleMenu(false));
  document.addEventListener('keydown',e=>{if(e.key==='Escape')toggleMenu(false);});
  document.querySelectorAll('[data-teacher-preview]').forEach(input=>input.addEventListener('change',()=>{
    const target=document.querySelector(input.dataset.teacherPreview); if(!target)return; target.replaceChildren();
    [...(input.files||[])].forEach(file=>{
      const chip=document.createElement('span');chip.className='chip';
      chip.textContent=file.name+' · '+String(Math.max(1,Math.ceil(file.size/1024))).replace(/\d/g,d=>'۰۱۲۳۴۵۶۷۸۹'[d])+' KB';
      if(file.type==='application/pdf'||file.type.startsWith('image/')){
        const a=document.createElement('a'),url=URL.createObjectURL(file);a.href=url;a.target='_blank';a.rel='noopener noreferrer';a.textContent='پیش‌نمایش';
        a.addEventListener('click',()=>setTimeout(()=>URL.revokeObjectURL(url),60000),{once:true});chip.append(' · ',a);
      }
      target.appendChild(chip);
    });
  }));
});
