/* Teacher-only runtime. Role UI and behavior are isolated. */
import Alpine from 'alpinejs';
import './teacher/persian-calendar.js';
import './teacher/persian-money.js';
import './teacher/assessment-builder.js';
import {
    MONTH_NAMES,
    currentJalaliMonth,
    formatJalali,
    jalaliDaysInMonth,
    jalaliFromIso,
    jalaliToGregorian,
    latinDigits,
    pad as padDate,
    parseJalali,
    persianDigits,
} from './teacher/persian-calendar-core.mjs';
import { bootFileUploadProgress } from './teacher/upload-progress.js';
import './teacher/course-media-uploader.js';

window.SheykhanTeacherCalendar = Object.freeze({
    currentJalaliMonth,
    formatJalali,
    jalaliFromIso,
    jalaliToGregorian,
    parseJalali,
    persianDigits,
    latinDigits,
});
window.Alpine = Alpine;
Alpine.start();
document.addEventListener('DOMContentLoaded', () => bootFileUploadProgress());
// Persian date and digit helpers come from the shared teacher calendar core.
function fa(value){return persianDigits(value)}

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
        element.textContent = formatJalali(raw);
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
                    || /\.(jpe?g|png|webp|avif)$/i.test(file.name);

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
                const currentPreviewUrl = previewUrl;
                image.onload = () => {
                    if (previewUrl !== currentPreviewUrl) return;
                    if (feedback) feedback.textContent = `پیش‌نمایش «${file.name}» آماده است؛ برای ثبت نهایی، ذخیره پروفایل را بزن.`;
                };
                image.onerror = () => {
                    if (previewUrl === currentPreviewUrl) {
                        URL.revokeObjectURL(currentPreviewUrl);
                        previewUrl = null;
                    }
                    if (feedback) feedback.textContent = 'نمایش تصویر ممکن نشد؛ فایل دیگری انتخاب کنید.';
                };
                image.src = currentPreviewUrl;

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
        const monthNames = MONTH_NAMES;
        const digits = persianDigits;
        const pad = padDate;

        let viewYear = Number(liveForm.dataset.jalaliYear);
        let viewMonth = Number(liveForm.dataset.jalaliMonth);
        let selected = latinDigits(hiddenDate.value || '');

        const syncScheduledAt = () => {
            if (!hiddenDate || !timeInput || !hiddenScheduledAt || !hiddenDate.value || !timeInput.value) return;
            const [jy, jm, jd] = hiddenDate.value.split('/').map(Number);
            const gregorian = jalaliToGregorian(jy, jm, jd);
            if (!gregorian) {
                hiddenScheduledAt.value = '';
                return;
            }
            const [gy, gm, gd] = gregorian;
            hiddenScheduledAt.value = `${gy}-${pad(gm)}-${pad(gd)}T${timeInput.value}`;
        };

        const renderPicker = () => {
            if (!days || !monthLabel) return;

            monthLabel.textContent = `${monthNames[viewMonth - 1]} ${digits(viewYear)}`;
            days.innerHTML = '';

            const firstDate = jalaliToGregorian(viewYear, viewMonth, 1);
            if (!firstDate) return;
            const [firstGy, firstGm, firstGd] = firstDate;
            const firstWeekday = (new Date(Date.UTC(firstGy, firstGm - 1, firstGd, 12)).getUTCDay() + 1) % 7;

            for (let i = 0; i < firstWeekday; i++) {
                days.appendChild(document.createElement('i'));
            }

            for (let day = 1; day <= jalaliDaysInMonth(viewYear, viewMonth); day++) {
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
  const menuTrigger=document.querySelector('[data-teacher-menu-open]');
  const toggleMenu=(open)=>{side?.classList.toggle('open',!!open);overlay?.classList.toggle('open',!!open);overlay?.setAttribute('aria-hidden',String(!open));document.body.classList.toggle('teacher-menu-open',!!open);if(open)side?.querySelector('a.nv,button.nv')?.focus();else menuTrigger?.focus();};
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
