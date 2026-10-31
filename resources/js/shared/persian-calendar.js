import {
    MONTH_NAMES,
    WEEKDAYS,
    PERSIAN_DIGITS,
    latinDigits,
    persianDigits,
    pad,
    gregorianToJalali,
    jalaliToGregorian,
    parseJalali,
    jalaliFromIso,
    formatJalali,
    jalaliDaysInMonth,
    currentJalaliMonth,
    shiftMonth,
    dayIso,
    datePart,
} from './persian-calendar-core.mjs';

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
        const today = currentJalaliMonth();
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
    dateInput.lang = 'fa-IR';
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
        timeInput.dir = 'ltr';
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

    const field = dateInput.closest('[data-jalali-field]');
    const target = field?.querySelector('[data-jalali-target]');
    const timeInput = field?.querySelector('[data-jalali-visible-time]');
    const dateOnly = field?.dataset.jalaliDateOnly === '1';
    const originalValue = String(target?.value || '');

    // Fill the visible Jalali date/time controls from the server's Gregorian value.
    if (!dateInput.value.trim()) {
        dateInput.value = formatJalali(originalValue);
    }
    if (timeInput && !timeInput.value) {
        timeInput.value = originalValue.match(/T(\d{2}:\d{2})/)?.[1]
            || originalValue.match(/ (\d{2}:\d{2})/)?.[1]
            || '';
    }

    dateInput.dataset.jalaliCalendarEnhanced = 'true';
    dateInput.lang = 'fa-IR';
    dateInput.dir = 'ltr';
    dateInput.autocomplete = 'off';
    if (timeInput) {
        timeInput.lang = 'fa-IR';
        timeInput.dir = 'ltr';
        timeInput.step = '60';
    }

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

    const syncHiddenValue = () => {
        const parsed = parseJalali(dateInput.value);
        if (!parsed) {
            if (target) target.value = '';
            dateInput.setCustomValidity(dateInput.value.trim()
                ? 'تاریخ شمسی معتبر نیست؛ تاریخ را به شکل سال/ماه/روز وارد کن.'
                : '');
            return;
        }

        dateInput.value = formatJalali(parsed);
        if (target) {
            target.value = dateOnly
                ? parsed.iso
                : (timeInput?.value ? parsed.iso + 'T' + timeInput.value : '');
            target.dispatchEvent(new Event('input', { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
        }

        dateInput.setCustomValidity(!dateOnly && timeInput && !timeInput.value
            ? 'ساعت را هم انتخاب کن.'
            : '');
    };

    dateInput.addEventListener('input', syncHiddenValue);
    dateInput.addEventListener('change', syncHiddenValue);
    timeInput?.addEventListener('input', syncHiddenValue);
    timeInput?.addEventListener('change', syncHiddenValue);

    makeCalendar(wrapper, dateInput, 'jalali-text');
    syncHiddenValue();

    field?.closest('form')?.addEventListener('reset', () => {
        window.setTimeout(() => {
            const value = String(target?.value || '');
            dateInput.value = formatJalali(value);
            if (timeInput) timeInput.value = value.match(/T(\d{2}:\d{2})/)?.[1] || '';
            dateInput.setCustomValidity('');
        }, 0);
    });
}
function initPersianCalendars() {
    try {
        // A single shared picker upgrades native fields in every role and public form.
        document.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach(enhanceNativeDateInput);
        // Teacher assignment/exam/attendance fields already submit a converted hidden value.
        document.querySelectorAll('[data-jalali-visible-date]').forEach(enhanceExistingJalaliField);

        // Also enhance date inputs inserted after initial page load (dialogs/AJAX fragments).
        if (document.body && !document.body.dataset.persianCalendarObserver) {
            const selector = 'input[type="date"], input[type="datetime-local"]';
            const enhanceAddedNode = (node) => {
                if (!(node instanceof Element)) return;

                if (node.matches(selector)) enhanceNativeDateInput(node);
                if (node.matches('[data-jalali-visible-date]')) enhanceExistingJalaliField(node);
                node.querySelectorAll(selector).forEach(enhanceNativeDateInput);
                node.querySelectorAll('[data-jalali-visible-date]').forEach(enhanceExistingJalaliField);
            };

            const observer = new MutationObserver((records) => {
                records.forEach((record) => record.addedNodes.forEach(enhanceAddedNode));
            });

            observer.observe(document.body, { childList: true, subtree: true });
            document.body.dataset.persianCalendarObserver = 'true';
        }
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
