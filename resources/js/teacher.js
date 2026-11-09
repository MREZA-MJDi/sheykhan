/* Teacher-only runtime. Role UI and behavior are isolated. */
import { bootAlpineRuntime as bootAlpine } from './shared/alpine-runtime.js';
import { bootSharedBehaviors } from './shared/behavior-runtime.js';
import './shared/persian-calendar.js';
import './shared/persian-money.js';
import './teacher/assessment-builder.js';
import { bootFileUploadProgress } from './shared/upload-progress.js';
import './teacher/course-media-uploader.js';
import './teacher/recipient-picker.js';

bootAlpine();
bootSharedBehaviors();
document.addEventListener('DOMContentLoaded', () => bootFileUploadProgress());
import {
    MONTH_NAMES,
    pad,
    persianDigits,
    jalaliToGregorian,
    jalaliDaysInMonth,
} from './shared/persian-calendar-core.mjs';

const fa = persianDigits;
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
        const jalaliMonthDays = jalaliDaysInMonth;

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

/* Teacher form selectors use server-side search instead of loading whole rosters. */
(() => {
  'use strict';
  document.querySelectorAll('[data-teacher-lookup-picker]').forEach(root => {
    const endpoint = root.dataset.optionsUrl;
    const type = root.dataset.lookupType;
    const search = root.querySelector('[data-teacher-lookup-search]');
    const hidden = root.querySelector('[data-teacher-lookup-value]');
    const labelHidden = root.querySelector('[data-teacher-lookup-label]');
    const results = root.querySelector('[data-teacher-lookup-results]');
    const status = root.querySelector('[data-teacher-lookup-status]');
    const more = root.querySelector('[data-teacher-lookup-more]');
    const courseInput = root.dataset.courseInput ? document.getElementById(root.dataset.courseInput) : null;
    if (!endpoint || !type || !search || !hidden || !results || !status || !more) return;

    if (root.dataset.initialLabel && !search.value) search.value = root.dataset.initialLabel;
    let page = 1;
    let lastPage = 1;
    let timer = null;
    let sequence = 0;
    let controller = null;

    const render = (items, append) => {
      if (!append) results.replaceChildren();
      items.forEach(item => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'teacher-lookup-option';
        option.textContent = String(item.label || '');
        option.addEventListener('click', () => {
          hidden.value = String(item.id);
          if (labelHidden) labelHidden.value = String(item.label || '');
          search.value = String(item.label || '');
          search.removeAttribute('aria-invalid');
          results.replaceChildren();
          more.hidden = true;
          status.textContent = 'انتخاب شد: ' + search.value;
          hidden.dispatchEvent(new Event('change', { bubbles: true }));
        });
        results.append(option);
      });
    };

    const load = async (nextPage = 1, append = false) => {
      const term = search.value.trim();
      if (term.length < 2) {
        results.replaceChildren();
        more.hidden = true;
        status.textContent = 'برای جست‌وجو حداقل دو نویسه وارد کن.';
        return;
      }
      if (courseInput && !courseInput.value) {
        results.replaceChildren();
        more.hidden = true;
        status.textContent = 'ابتدا دوره را انتخاب کن.';
        return;
      }
      controller?.abort();
      controller = new AbortController();
      const current = ++sequence;
      const url = new URL(endpoint, window.location.origin);
      url.searchParams.set('q', term);
      url.searchParams.set('page', String(nextPage));
      if (courseInput?.value) url.searchParams.set('course_id', courseInput.value);
      status.textContent = 'در حال جست‌وجو…';
      more.disabled = true;
      try {
        const response = await fetch(url, {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal,
        });
        if (!response.ok) throw new Error('lookup-failed');
        const payload = await response.json();
        if (current !== sequence) return;
        const items = Array.isArray(payload.data) ? payload.data : [];
        page = Number(payload.meta?.current_page ?? nextPage);
        lastPage = Number(payload.meta?.last_page ?? 1);
        render(items, append);
        more.hidden = page >= lastPage || items.length === 0;
        status.textContent = items.length
          ? (Number(payload.meta?.total ?? items.length) + ' نتیجه؛ صفحه ' + page + ' از ' + lastPage)
          : 'نتیجه‌ای پیدا نشد.';
      } catch (error) {
        if (error?.name !== 'AbortError' && current === sequence) {
          status.textContent = 'جست‌وجو انجام نشد؛ دوباره تلاش کن.';
          more.hidden = true;
        }
      } finally {
        if (current === sequence) {
          controller = null;
          more.disabled = false;
        }
      }
    };

    search.addEventListener('input', () => {
      hidden.value = '';
      if (labelHidden) labelHidden.value = '';
      search.removeAttribute('aria-invalid');
      clearTimeout(timer);
      timer = setTimeout(() => load(1, false), 250);
    });
    more.addEventListener('click', () => {
      if (page < lastPage) load(page + 1, true);
    });
    courseInput?.addEventListener('change', () => {
      hidden.value = '';
      if (labelHidden) labelHidden.value = '';
      search.value = '';
      results.replaceChildren();
      more.hidden = true;
      status.textContent = 'حالا کلاس مربوط به این دوره را جست‌وجو کن.';
    });
    root.closest('form')?.addEventListener('submit', event => {
      const hasUnselectedSearch = search.value.trim().length > 0 && !hidden.value;
      if ((root.dataset.required === 'true' && !hidden.value) || hasUnselectedSearch) {
        event.preventDefault();
        search.setAttribute('aria-invalid', 'true');
        status.textContent = 'لطفاً یکی از گزینه‌های نتایج جست‌وجو را انتخاب کن.';
        search.focus();
      }
    });
  });
})();

