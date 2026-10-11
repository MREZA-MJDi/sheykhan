<?php

namespace App\Services;

/**
 * Verifies the portable phpass hashes still used by some legacy WordPress
 * accounts. This class only verifies; successful authentication is immediately
 * rehashed by Laravel's normal "hashed" model cast.
 */
final class LegacyWordPressPassword
{
    private const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function isLegacyHash(string $storedHash): bool
    {
        return str_starts_with($storedHash, '$wp$2y$')
            || str_starts_with($storedHash, '$P$')
            || str_starts_with($storedHash, '$H$');
    }

    public function supportsHashFormat(string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$wp$2y$')) {
            return strlen(substr($storedHash, 3)) === 60;
        }

        return (str_starts_with($storedHash, '$P$') || str_starts_with($storedHash, '$H$'))
            && strlen($storedHash) === 34;
    }

    public function verify(string $plain, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$wp$2y$')) {
            if (!$this->supportsHashFormat($storedHash)) {
                return false;
            }

            // WordPress 6.8+ pre-hashes with HMAC-SHA384, base64 encodes the
            // bytes, and bcrypts that intermediate value. The wrapper is not
            // cosmetic, so never validate by merely stripping it and checking
            // the student's raw password with password_verify().
            $prehashed = base64_encode(hash_hmac('sha384', $plain, 'wp-sha384', true));

            return password_verify($prehashed, substr($storedHash, 3));
        }

        if (!str_starts_with($storedHash, '$P$') && !str_starts_with($storedHash, '$H$')) {
            return false;
        }

        if (!$this->supportsHashFormat($storedHash)) {
            return false;
        }

        $computed = $this->cryptPrivate($plain, $storedHash);

        return $computed !== '*' && hash_equals($storedHash, $computed);
    }

    private function cryptPrivate(string $password, string $setting): string
    {
        $output = '*0';

        if (substr($setting, 0, 2) === $output) {
            $output = '*1';
        }

        $id = substr($setting, 0, 3);
        if ($id !== '$P$' && $id !== '$H$') {
            return $output;
        }

        if (strlen($setting) < 12) {
            return '*';
        }

        $countLog2 = strpos(self::ITOA64, $setting[3]);
        if ($countLog2 === false || $countLog2 < 7 || $countLog2 > 30) {
            return '*';
        }

        $salt = substr($setting, 4, 8);
        if (strlen($salt) !== 8) {
            return $output;
        }

        $count = 1 << $countLog2;
        $hash = md5($salt . $password, true);

        do {
            $hash = md5($hash . $password, true);
        } while (--$count);

        return substr($setting, 0, 12) . $this->encode64($hash, 16);
    }

    private function encode64(string $input, int $count): string
    {
        $output = '';
        $index = 0;

        do {
            $value = ord($input[$index++]);
            $output .= self::ITOA64[$value & 0x3f];

            if ($index < $count) {
                $value |= ord($input[$index]) << 8;
            }

            $output .= self::ITOA64[($value >> 6) & 0x3f];

            if ($index++ >= $count) {
                break;
            }

            if ($index < $count) {
                $value |= ord($input[$index]) << 16;
            }

            $output .= self::ITOA64[($value >> 12) & 0x3f];

            if ($index++ >= $count) {
                break;
            }

            $output .= self::ITOA64[($value >> 18) & 0x3f];
        } while ($index < $count);

        return $output;
    }
}
