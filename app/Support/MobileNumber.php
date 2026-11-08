<?php

namespace App\Support;

final class MobileNumber
{
    public static function normalize(?string $value): ?string
    {
        if (blank($value)) return null;
        $digits = strtr(trim((string) $value), [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
        $digits = preg_replace('/\D+/', '', $digits) ?? '';
        if (str_starts_with($digits, '0098')) $digits = '0' . substr($digits, 4);
        elseif (str_starts_with($digits, '98') && strlen($digits) === 12) $digits = '0' . substr($digits, 2);
        elseif (str_starts_with($digits, '9') && strlen($digits) === 10) $digits = '0' . $digits;
        return $digits !== '' ? $digits : null;
    }

    public static function valid(?string $value): bool
    {
        return is_string($value) && preg_match('/^09[0-9]{9}$/', $value) === 1;
    }
}
