<?php

namespace App\Support;

use LogicException;

final class NationalIdLookup
{
    public static function make(string $nationalId): string
    {
        $normalized = self::normalize($nationalId);
        $key = config('app.national_id_hmac_key');

        if (!is_string($key) || $key === '') {
            throw new LogicException(
                'NATIONAL_ID_HMAC_KEY must be configured for national ID lookup.'
            );
        }

        return hash_hmac('sha256', $normalized, $key);
    }

    private static function normalize(string $value): string
    {
        $value = strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4',
            '۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4',
            '٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);

        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
