const MONTH_NAMES = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
const WEEKDAYS = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const monthFormatter = new Intl.DateTimeFormat('en-u-ca-persian-nu-latn', {
    timeZone: 'UTC',
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
});

const latinDigits = (value) => String(value ?? '')
    .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
    .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)));

const persianDigits = (value) => String(value).replace(/[0-9]/g, (digit) => PERSIAN_DIGITS[Number(digit)]);
const pad = (value) => String(value).padStart(2, '0');
const isoDate = (year, month, day) => [year, pad(month), pad(day)].join('-');
const todayIso = () => {
    const now = new Date();
    return isoDate(now.getFullYear(), now.getMonth() + 1, now.getDate());
};

function gregorianToJalali(year, month, day) {
    const date = new Date(Date.UTC(Number(year), Number(month) - 1, Number(day), 12));
    if (Number.isNaN(date.getTime())) return null;

    const parts = Object.fromEntries(
        monthFormatter.formatToParts(date).map((part) => [part.type, part.value])
    );

    const result = {
        year: Number(latinDigits(parts.year)),
        month: Number(latinDigits(parts.month)),
        day: Number(latinDigits(parts.day)),
    };

    return Object.values(result).every(Number.isFinite) ? result : null;
}

function jalaliToGregorian(year, month, day) {
    if (!Number.isInteger(year) || !Number.isInteger(month) || !Number.isInteger(day)
        || year < 1200 || year > 1600 || month < 1 || month > 12 || day < 1 || day > 31) {
        return null;
    }

    let y = year + 1595;
    let days = -355668 + (365 * y) + (Math.floor(y / 33) * 8)
        + Math.floor(((y % 33) + 3) / 4) + day;
    days += month < 7 ? (month - 1) * 31 : ((month - 7) * 30) + 186;

    let gy = 400 * Math.floor(days / 146097);
    days %= 146097;

    if (days > 36524) {
        gy += 100 * Math.floor(--days / 36524);
        days %= 36524;
        if (days >= 365) days++;
    }

    gy += 4 * Math.floor(days / 1461);
    days %= 1461;

    if (days > 365) {
        gy += Math.floor((days - 1) / 365);
        days = (days - 1) % 365;
    }

    let gd = days + 1;
    const gregorianMonthDays = [
        31,
        ((gy % 4 === 0 && gy % 100 !== 0) || gy % 400 === 0) ? 29 : 28,
        31, 30, 31, 30, 31, 31, 30, 31, 30, 31,
    ];
    let gm = 1;

    while (gm <= 12 && gd > gregorianMonthDays[gm - 1]) {
        gd -= gregorianMonthDays[gm - 1];
        gm++;
    }

    return gm <= 12 ? [gy, gm, gd] : null;
}

function parseJalali(value) {
    const normalized = latinDigits(value).replace(/-/g, '/').trim();
    const match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
    if (!match) return null;

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);
    const gregorian = jalaliToGregorian(year, month, day);
    if (!gregorian) return null;

    const roundTrip = gregorianToJalali(gregorian[0], gregorian[1], gregorian[2]);
    if (!roundTrip || roundTrip.year !== year || roundTrip.month !== month || roundTrip.day !== day) {
        return null;
    }

    return { year, month, day, iso: isoDate(...gregorian) };
}

function jalaliFromIso(value) {
    const match = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) return null;
    return gregorianToJalali(Number(match[1]), Number(match[2]), Number(match[3]));
}

function formatJalali(value) {
    if (!value) return '';
    const parts = typeof value === 'string' ? jalaliFromIso(value) : value;
    if (!parts) return '';
    return persianDigits([parts.year, pad(parts.month), pad(parts.day)].join('/'));
}

function jalaliDaysInMonth(year, month) {
    if (month <= 6) return 31;
    if (month <= 11) return 30;

    const leapCandidate = jalaliToGregorian(year, 12, 30);
    if (!leapCandidate) return 29;
    const back = gregorianToJalali(leapCandidate[0], leapCandidate[1], leapCandidate[2]);
    return back && back.year === year && back.month === 12 && back.day === 30 ? 30 : 29;
}

function currentJalaliMonth() {
    const today = jalaliFromIso(todayIso());
    return today || { year: 1405, month: 1, day: 1 };
}

function shiftMonth(year, month, direction) {
    let nextYear = year;
    let nextMonth = month + direction;
    if (nextMonth < 1) {
        nextMonth = 12;
        nextYear--;
    } else if (nextMonth > 12) {
        nextMonth = 1;
        nextYear++;
    }
    return { year: nextYear, month: nextMonth };
}

function dayIso(year, month, day) {
    const gregorian = jalaliToGregorian(year, month, day);
    return gregorian ? isoDate(...gregorian) : '';
}

function datePart(value) {
    return String(value ?? '').trim().replace(' ', 'T').slice(0, 10);
}

function makeCalendar(field, dateInput, sourceType, source = null) {
    const popup = document.createElement('div');
    popup.className = 'jalali-calendar-popup';
    popup.hidden = true;
    popup.setAttribute('role', 'dialog');
    popup.setAttribute('aria-label', 'انتخاب تاریخ شمسی');

    const heading = document.createElement('div');
    heading.className = 'jalali-calendar-heading';

    const previous = document.createElement('button');
    previous.type = 'button';
    previous.className = 'jalali-calendar-month-button';
    previous.textContent = '‹';
    previous.setAttribute('aria-label', 'ماه قبل');

    const monthLabel = document.createElement('strong');
    monthLabel.className = 'jalali-calendar-month-label';

    const next = document.createElement('button');
    next.type = 'button';
    next.className = 'jalali-calendar-month-button';
    next.textContent = '›';
    next.setAttribute('aria-label', 'ماه بعد');

    heading.append(previous, monthLabel, next);

    const weekdays = document.createElement('div');
    weekdays.className = 'jalali-calendar-weekdays';
    weekdays.setAttribute('aria-hidden', 'true');
    WEEKDAYS.forEach((day) => {
        const label = document.createElement('span');
        label.textContent = day;
        weekdays.append(label);
    });

    const days = document.createElement('div');
    days.className = 'jalali-calendar-days';
    days.setAttribute('role', 'grid');

    popup.append(heading, weekdays, days);
    document.body.append(popup);

    let visibleMonth = parseJalali(dateInput.value) || currentJalaliMonth();
    let visibleYear = visibleMonth.year;
    let visibleMonthNumber = visibleMonth.month;

    const constraints = () => ({
        min: field.dataset.jalaliMin || '',
        max: field.dataset.jalaliMax || '',
    });

    const render = () => {
        monthLabel.textContent = MONTH_NAMES[visibleMonthNumber - 1] + ' ' + persianDigits(visibleYear);
        days.replaceChildren();

        const firstIso = dayIso(visibleYear, visibleMonthNumber, 1);
        const first = firstIso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!first) return;

        const weekday = new Date(Date.UTC(Number(first[1]), Number(first[2]) - 1, Number(first[3]), 12)).getUTCDay();
        const offset = (weekday + 1) % 7;
        const selected = parseJalali(dateInput.value);
        const today = jalaliFromIso(todayIso());
        const bounds = constraints();

        for (let blank = 0; blank < offset; blank++) {
            const spacer = document.createElement('span');
            spacer.className = 'jalali-calendar-day-spacer';
            spacer.setAttribute('aria-hidden', 'true');
            days.append(spacer);
        }

        for (let day = 1; day <= jalaliDaysInMonth(visibleYear, visibleMonthNumber); day++) {
            const candidateIso = dayIso(visibleYear, visibleMonthNumber, day);
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'jalali-calendar-day';
            button.textContent = persianDigits(day);
            button.setAttribute('role', 'gridcell');
            button.setAttribute('aria-label', persianDigits([visibleYear, pad(visibleMonthNumber), pad(day)].join('/')));

            if (selected && selected.year === visibleYear && selected.month === visibleMonthNumber && selected.day === day) {
                button.classList.add('is-selected');
                button.setAttribute('aria-pressed', 'true');
            }

            if (today && today.year === visibleYear && today.month === visibleMonthNumber && today.day === day) {
                button.classList.add('is-today');
            }

            const minDate = datePart(bounds.min);
            const maxDate = datePart(bounds.max);
            if ((minDate && candidateIso < minDate) || (maxDate && candidateIso > maxDate)) {
                button.disabled = true;
                button.setAttribute('aria-disabled', 'true');
            }

            button.addEventListener('click', () => {
                dateInput.value = formatJalali({ year: visibleYear, month: visibleMonthNumber, day });
                dateInput.dispatchEvent(new Event('input', { bubbles: true }));
                dateInput.dispatchEvent(new Event('change', { bubbles: true }));
                close();
                dateInput.focus();
            });

            days.append(button);
        }
    };

    const positionPopup = () => {
        const rect = field.getBoundingClientRect();
        const width = Math.min(320, Math.max(260, window.innerWidth - 24));
        popup.style.width = width + 'px';
        const left = Math.max(12, Math.min(rect.left, window.innerWidth - width - 12));
        const popupHeight = popup.getBoundingClientRect().height;
        let top = rect.bottom + 6;
        if (top + popupHeight > window.innerHeight - 12) top = Math.max(12, rect.top - popupHeight - 6);
        popup.style.left = left + 'px';
        popup.style.top = top + 'px';
    };

    const open = () => {
        const selected = parseJalali(dateInput.value);
        const initial = selected || currentJalaliMonth();
        visibleYear = initial.year;
        visibleMonthNumber = initial.month;
        render();
        popup.hidden = false;
        field.classList.add('is-calendar-open');
        dateInput.setAttribute('aria-expanded', 'true');
        field.querySelectorAll('[data-jalali-calendar-open]').forEach((button) => button.setAttribute('aria-expanded', 'true'));
        positionPopup();
    };

    const close = () => {
        popup.hidden = true;
        field.classList.remove('is-calendar-open');
        dateInput.setAttribute('aria-expanded', 'false');
        field.querySelectorAll('[data-jalali-calendar-open]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    };

    previous.addEventListener('click', () => {
        const moved = shiftMonth(visibleYear, visibleMonthNumber, -1);
        visibleYear = moved.year;
        visibleMonthNumber = moved.month;
        render();
    });

    next.addEventListener('click', () => {
        const moved = shiftMonth(visibleYear, visibleMonthNumber, 1);
        visibleYear = moved.year;
        visibleMonthNumber = moved.month;
        render();
    });

    field.querySelectorAll('[data-jalali-calendar-open]').forEach((button) => {
        button.addEventListener('click', () => {
            if (popup.hidden) open();
            else close();
        });
    });

    dateInput.addEventListener('click', open);
    dateInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowDown' && popup.hidden) open();
    });

    document.addEventListener('pointerdown', (event) => {
        if (!field.contains(event.target) && !popup.contains(event.target)) close();
    });
    window.addEventListener('resize', () => { if (!popup.hidden) positionPopup(); });
    window.addEventListener('scroll', () => { if (!popup.hidden) positionPopup(); }, true);

    if (source) {
        source.addEventListener('change', () => {
            const value = source.value;
            if (value) dateInput.value = formatJalali(value);
            render();
        });
    }

    return { open, close, render, popup };
}

function enhanceNativeDateInput(source) {
    if (source.dataset.jalaliCalendarEnhanced === 'true') return;

    const sourceType = source.type;
    const originalValue = source.value;
    const required = source.required;
    const disabled = source.disabled;
    const minimum = source.getAttribute('min') || '';
    const maximum = source.getAttribute('max') || '';
    const originalId = source.id;
    const label = source.closest('label');
    const labelText = source.getAttribute('aria-label')
        || label?.querySelector('span')?.textContent?.trim()
        || label?.textContent?.trim()
        || (sourceType === 'date' ? 'تاریخ' : 'تاریخ و زمان');

    source.dataset.jalaliCalendarEnhanced = 'true';
    source.dataset.jalaliCalendarType = sourceType;
    source.dataset.jalaliMin = minimum;
    source.dataset.jalaliMax = maximum;
    source.type = 'hidden';
    source.removeAttribute('required');
    source.removeAttribute('min');
    source.removeAttribute('max');
    source.removeAttribute('id');
    source.classList.add('jalali-calendar-source');

    const field = document.createElement('div');
    field.className = 'jalali-calendar-control';
    field.dataset.jalaliMin = minimum;
    field.dataset.jalaliMax = maximum;
    field.dataset.jalaliType = sourceType;
    source.parentNode.insertBefore(field, source);
    field.append(source);

    const row = document.createElement('div');
    row.className = 'jalali-calendar-date-row';
    field.insertBefore(row, source);

    const dateInput = document.createElement('input');
    dateInput.type = 'text';
    dateInput.className = 'jalali-calendar-date-input';
    dateInput.inputMode = 'numeric';
    dateInput.autocomplete = 'off';
    dateInput.placeholder = '۱۴۰۵/۰۷/۰۲';
    dateInput.dir = 'ltr';
    dateInput.required = required;
    dateInput.disabled = disabled;
    dateInput.setAttribute('aria-label', labelText + ' (شمسی)');
    dateInput.setAttribute('aria-haspopup', 'dialog');
    dateInput.setAttribute('aria-expanded', 'false');
    if (originalId) dateInput.id = originalId;
    const describedBy = source.getAttribute('aria-describedby');
    if (describedBy) dateInput.setAttribute('aria-describedby', describedBy);
    dateInput.value = formatJalali(originalValue);
    row.append(dateInput);

    const calendarButton = document.createElement('button');
    calendarButton.type = 'button';
    calendarButton.className = 'jalali-calendar-open';
    calendarButton.dataset.jalaliCalendarOpen = 'true';
    calendarButton.disabled = disabled;
    calendarButton.setAttribute('aria-label', 'باز کردن تقویم شمسی برای ' + labelText);
    calendarButton.innerHTML = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="15" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v4M16.5 3.5v4M4 9.5h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M8 13h2m4 0h2M8 16.5h2m4 0h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>';
    row.append(calendarButton);

    let timeInput = null;
    if (sourceType === 'datetime-local') {
        timeInput = document.createElement('input');
        timeInput.type = 'time';
        timeInput.className = 'jalali-calendar-time-input';
        timeInput.value = originalValue.match(/^\d{4}-\d{2}-\d{2}T(\d{2}:\d{2})/)?.[1] || '';
        timeInput.required = required;
        timeInput.disabled = disabled;
        timeInput.setAttribute('aria-label', 'ساعت ' + labelText);
        timeInput.lang = 'fa-IR';
        timeInput.step = source.getAttribute('step') || '60';
        row.append(timeInput);
    }

    const sync = () => {
        const parsed = parseJalali(dateInput.value);
        if (!parsed) {
            source.value = '';
            dateInput.setCustomValidity(dateInput.value.trim() ? 'تاریخ شمسی معتبر نیست؛ تاریخ را به شکل سال/ماه/روز وارد کنید.' : '');
            return;
        }

        if (dateInput.value.trim()) dateInput.value = formatJalali(parsed);
        if (sourceType === 'date') {
            source.value = parsed.iso;
        } else if (!timeInput?.value) {
            source.value = '';
        } else {
            source.value = parsed.iso + 'T' + timeInput.value;
        }

        const candidate = source.value;
        const min = field.dataset.jalaliMin;
        const max = field.dataset.jalaliMax;
        let error = '';

        if (candidate && min && candidate < min) {
            error = 'تاریخ و زمان باید مساوی یا بعد از حداقل تعیین‌شده باشد.';
        } else if (candidate && max && candidate > max) {
            error = 'تاریخ و زمان باید مساوی یا قبل از حداکثر تعیین‌شده باشد.';
        }

        dateInput.setCustomValidity(error);
    };

    dateInput.addEventListener('input', sync);
    dateInput.addEventListener('change', sync);
    timeInput?.addEventListener('input', sync);
    timeInput?.addEventListener('change', sync);

    makeCalendar(field, dateInput, sourceType, source);
    sync();

    source.closest('form')?.addEventListener('reset', () => {
        window.setTimeout(() => {
            dateInput.value = formatJalali(source.value);
            if (timeInput) timeInput.value = source.value.match(/T(\d{2}:\d{2})/)?.[1] || '';
            dateInput.setCustomValidity('');
        }, 0);
    });
}

function enhanceExistingJalaliField(dateInput) {
    if (dateInput.dataset.jalaliCalendarEnhanced === 'true') return;

    const grid = dateInput.parentElement;
    if (!grid) return;

    dateInput.dataset.jalaliCalendarEnhanced = 'true';
    const wrapper = document.createElement('div');
    wrapper.className = 'jalali-calendar-visible-control';
    grid.insertBefore(wrapper, dateInput);
    wrapper.append(dateInput);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'jalali-calendar-open';
    button.dataset.jalaliCalendarOpen = 'true';
    button.setAttribute('aria-label', 'باز کردن تقویم شمسی');
    button.innerHTML = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5.5" width="17" height="15" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M7.5 3.5v4M16.5 3.5v4M4 9.5h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M8 13h2m4 0h2M8 16.5h2m4 0h2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>';
    wrapper.append(button);

    dateInput.setAttribute('aria-haspopup', 'dialog');
    dateInput.setAttribute('aria-expanded', 'false');
    dateInput.autocomplete = 'off';

    const field = dateInput.closest('[data-teacher-jalali]') || wrapper;
    makeCalendar(wrapper, dateInput, 'jalali-text');
    // The picker intentionally emits input/change so the existing Gregorian hidden-value sync runs.
    dateInput.addEventListener('input', () => dateInput.setCustomValidity(''));
}

function initPersianCalendars() {
    try {
        // A single shared picker upgrades native fields in every role and public form.
        document.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach(enhanceNativeDateInput);
        // Teacher assignment/exam/attendance fields already submit a converted hidden value.
        document.querySelectorAll('[data-jalali-visible-date]').forEach(enhanceExistingJalaliField);
    } catch (error) {
        // Never block a form if a browser has an incomplete Intl calendar implementation.
        console.error('Persian calendar enhancement failed', error);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPersianCalendars, { once: true });
} else {
    initPersianCalendars();
}
