<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class PersianUi
{
    public static function digits(mixed $value): string
    {
        return strtr((string) $value, [
            '0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴',
            '5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹',
        ]);
    }

    public static function money(int|float|string $amount): string
    {
        return self::digits(number_format((float) $amount, 0, '.', ',')) . ' تومان';
    }


    public static function normalizeDate(string|null $date): ?string
    {
        if (!$date) {
            return null;
        }

        $normalized = strtr(trim($date), [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4',
            '۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4',
            '٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);

        $normalized = str_replace('/', '-', $normalized);

        if (preg_match('/^(\\d{4})-(\\d{1,2})-(\\d{1,2})$/', $normalized, $match) !== 1) {
            return null;
        }

        $year = (int) $match[1];
        $month = (int) $match[2];
        $day = (int) $match[3];

        if ($year >= 1700) {
            try {
                $dateObject = Carbon::createSafe($year, $month, $day);
                return $dateObject?->toDateString();
            } catch (\\Throwable) {
                return null;
            }
        }

        if ($year < 1200 || $month < 1 || $month > 12 || $day < 1 || $day > 31) {
            return null;
        }

        [$gy, $gm, $gd] = self::jalaliToGregorian($year, $month, $day);

        if (self::gregorianToJalali($gy, $gm, $gd) !== [$year, $month, $day]) {
            return null;
        }

        try {
            return Carbon::createSafe($gy, $gm, $gd)?->toDateString();
        } catch (\\Throwable) {
            return null;
        }
    }

    public static function date(CarbonInterface|string|null $date): string
    {
        if (!$date) return '—';
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }
        [$jy,$jm,$jd] = self::gregorianToJalali((int)$date->format('Y'), (int)$date->format('m'), (int)$date->format('d'));
        return self::digits(sprintf('%04d/%02d/%02d', $jy, $jm, $jd));
    }

    public static function time(CarbonInterface|string|null $date): string
    {
        if (!$date) return '—';
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        return self::digits($date->format('H:i'));
    }

    public static function calendar(?CarbonInterface $date = null): array
    {
        $date ??= now();

        [$jy, $jm, $jd] = self::gregorianToJalali(
            (int) $date->format('Y'),
            (int) $date->format('m'),
            (int) $date->format('d'),
        );

        $monthNames = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد',
            4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر',
            10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];

        $daysInMonth = $jm <= 6
            ? 31
            : ($jm <= 11 ? 30 : (self::isJalaliLeapYear($jy) ? 30 : 29));

        [$firstGy, $firstGm, $firstGd] = self::jalaliToGregorian($jy, $jm, 1);
        $firstWeekday = (Carbon::create($firstGy, $firstGm, $firstGd)->dayOfWeek + 1) % 7;

        return [
            'year' => $jy,
            'month' => $jm,
            'day' => $jd,
            'month_name' => $monthNames[$jm],
            'month_label' => self::digits(sprintf('%04d', $jy)) . ' ' . $monthNames[$jm],
            'days_in_month' => $daysInMonth,
            'first_weekday' => $firstWeekday,
            'today_label' => self::digits(sprintf('%04d/%02d/%02d', $jy, $jm, $jd)),
        ];
    }

    private static function isJalaliLeapYear(int $jy): bool
    {
        [$gy, $gm, $gd] = self::jalaliToGregorian($jy, 12, 30);

        return self::gregorianToJalali($gy, $gm, $gd)[2] === 30;
    }

    private static function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        $jy += 1595;
        $days = -355668
            + (365 * $jy)
            + (intdiv($jy, 33) * 8)
            + intdiv(($jy % 33) + 3, 4)
            + $jd
            + ($jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);

        $gy = 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;
            if ($days >= 365) $days++;
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;
        $gDaysInMonth = [31, self::isGregorianLeapYear($gy) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $gm = 1;

        while ($gm <= 12 && $gd > $gDaysInMonth[$gm - 1]) {
            $gd -= $gDaysInMonth[$gm - 1];
            $gm++;
        }

        return [$gy, $gm, $gd];
    }

    private static function isGregorianLeapYear(int $gy): bool
    {
        return ($gy % 4 === 0 && $gy % 100 !== 0) || $gy % 400 === 0;
    }

    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $gDays = [0,31,59,90,120,151,181,212,243,273,304,334];
        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + intdiv($gy2 + 3, 4) - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400) + $gd + $gDays[$gm - 1];
        $jy = -1595 + (33 * intdiv($days, 12053));
        $days %= 12053;
        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }
        $jd = $days + 1;
        $jm = $jd <= 186 ? (int)ceil($jd / 31) : (int)ceil(($jd - 6) / 30);
        $jd = $jd <= 186 ? (($jd - 1) % 31) + 1 : (($jd - 187) % 30) + 1;
        return [$jy, $jm, $jd];
    }
}
