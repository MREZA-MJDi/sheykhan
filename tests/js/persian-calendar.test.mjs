import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

import {
    formatJalali,
    gregorianToJalali,
    jalaliDaysInMonth,
    jalaliFromIso,
    jalaliToGregorian,
    parseJalali,
} from '../../resources/js/persian-calendar-core.mjs';

test('known Gregorian dates convert to the expected Jalali date', () => {
    assert.deepEqual(gregorianToJalali(2026, 3, 21), { year: 1405, month: 1, day: 1 });
    assert.deepEqual(gregorianToJalali(2026, 9, 23), { year: 1405, month: 7, day: 1 });
    assert.deepEqual(gregorianToJalali(2025, 3, 20), { year: 1403, month: 12, day: 30 });
});

test('valid Persian-digit input converts to ISO for the existing backend contract', () => {
    assert.deepEqual(parseJalali('۱۴۰۵/۰۷/۰۱'), {
        year: 1405,
        month: 7,
        day: 1,
        iso: '2026-09-23',
    });
    assert.equal(formatJalali('2026-03-21'), '۱۴۰۵/۰۱/۰۱');
});

test('Jalali input accepts a leap-year Esfand 30 and rejects an invalid non-leap date', () => {
    assert.equal(jalaliDaysInMonth(1403, 12), 30);
    assert.equal(jalaliDaysInMonth(1404, 12), 29);
    assert.equal(parseJalali('۱۴۰۳/۱۲/۳۰')?.iso, '2025-03-20');
    assert.equal(parseJalali('۱۴۰۴/۱۲/۳۰'), null);
    assert.equal(parseJalali('۱۴۰۵/۱۳/۰۱'), null);
    assert.equal(parseJalali('۱۴۰۵/۰۷/۳۲'), null);
});

test('Jalali-to-Gregorian and Gregorian-to-Jalali round-trip across month boundaries', () => {
    const dates = [
        [1403, 1, 1],
        [1403, 6, 31],
        [1403, 7, 1],
        [1403, 12, 30],
        [1404, 1, 1],
        [1404, 6, 31],
        [1404, 7, 1],
        [1404, 12, 29],
        [1405, 1, 1],
        [1405, 7, 1],
        [1405, 12, 29],
    ];

    for (const [year, month, day] of dates) {
        const gregorian = jalaliToGregorian(year, month, day);
        assert.ok(gregorian, 'expected valid Jalali date ' + year + '/' + month + '/' + day);
        assert.deepEqual(gregorianToJalali(...gregorian), { year, month, day });
    }
});

test('ISO input without a valid Gregorian date is not silently normalized', () => {
    assert.equal(jalaliFromIso('not-a-date'), null);
    assert.equal(parseJalali('۱۴۰۵/۰۲/۳۲'), null);
});


test('calendar renderer uses a defined current-month helper', () => {
    const source = readFileSync(new URL('../../resources/js/persian-calendar.js', import.meta.url), 'utf8');

    assert.match(source, /const today = currentJalaliMonth\(\);/);
    assert.doesNotMatch(source, /\btodayIso\(\)/);
});
