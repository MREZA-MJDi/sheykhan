export const MONTH_NAMES = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
export const WEEKDAYS = ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
export const PERSIAN_DIGITS = '۰۱۲۳۴۵۶۷۸۹';
const monthFormatter = new Intl.DateTimeFormat('en-u-ca-persian-nu-latn', {
    timeZone: 'UTC',
    year: 'numeric',
    month: 'numeric',
    day: 'numeric',
});

export const latinDigits = (value) => String(value ?? '')
    .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
    .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)));

export const persianDigits = (value) => String(value).replace(/[0-9]/g, (digit) => PERSIAN_DIGITS[Number(digit)]);
export const pad = (value) => String(value).padStart(2, '0');
const isoDate = (year, month, day) => [year, pad(month), pad(day)].join('-');
const todayIso = () => {
    const now = new Date();
    return isoDate(now.getFullYear(), now.getMonth() + 1, now.getDate());
};

export function gregorianToJalali(year, month, day) {
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

export function jalaliToGregorian(year, month, day) {
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

export function parseJalali(value) {
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

export function jalaliFromIso(value) {
    const match = String(value ?? '').match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) return null;
    return gregorianToJalali(Number(match[1]), Number(match[2]), Number(match[3]));
}

export function formatJalali(value) {
    if (!value) return '';
    const parts = typeof value === 'string' ? jalaliFromIso(value) : value;
    if (!parts) return '';
    return persianDigits([parts.year, pad(parts.month), pad(parts.day)].join('/'));
}

export function jalaliDaysInMonth(year, month) {
    if (month <= 6) return 31;
    if (month <= 11) return 30;

    const leapCandidate = jalaliToGregorian(year, 12, 30);
    if (!leapCandidate) return 29;
    const back = gregorianToJalali(leapCandidate[0], leapCandidate[1], leapCandidate[2]);
    return back && back.year === year && back.month === 12 && back.day === 30 ? 30 : 29;
}

export function currentJalaliMonth() {
    const today = jalaliFromIso(todayIso());
    return today || { year: 1405, month: 1, day: 1 };
}

export function shiftMonth(year, month, direction) {
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

export function dayIso(year, month, day) {
    const gregorian = jalaliToGregorian(year, month, day);
    return gregorian ? isoDate(...gregorian) : '';
}

export function datePart(value) {
    return String(value ?? '').trim().replace(' ', 'T').slice(0, 10);
}

