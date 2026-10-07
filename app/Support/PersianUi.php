<?php

namespace App\Support;

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

    public static function date(?CarbonInterface $date): string
    {
        if (!$date) return '—';
        [$jy,$jm,$jd] = self::gregorianToJalali((int)$date->format('Y'), (int)$date->format('m'), (int)$date->format('d'));
        return self::digits(sprintf('%04d/%02d/%02d', $jy, $jm, $jd));
    }

    public static function time(?CarbonInterface $date): string
    {
        return $date ? self::digits($date->format('H:i')) : '—';
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
