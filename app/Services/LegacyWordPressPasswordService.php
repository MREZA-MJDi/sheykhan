<?php

namespace App\Services;

/**
 * Verifies password hashes from the legacy WordPress installation.
 *
 * A successful legacy verification must be followed by a rehash using the
 * application's configured Laravel hasher. Never strip a format prefix and
 * pass the result directly to Hash::check().
 */
final class LegacyWordPressPasswordService
{
    private const ITOA64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    public function isLegacyHash(string $hash): bool
    {
        return str_starts_with($hash, '$wp$2y$')
            || str_starts_with($hash, '$P$')
            || str_starts_with($hash, '$H$');
    }

    public function supportsHashFormat(string $hash): bool
    {
        if (str_starts_with($hash, '$wp$2y$')) {
            return strlen(substr($hash, 3)) === 60;
        }

        return (str_starts_with($hash, '$P$') || str_starts_with($hash, '$H$'))
            && strlen($hash) === 34;
    }

    public function verify(string $plainPassword, string $storedHash): bool
    {
        if (str_starts_with($storedHash, '$wp$2y$')) {
            $bcryptHash = substr($storedHash, 3);
            $wordpressPassword = base64_encode(
                hash_hmac('sha384', $plainPassword, 'wp-sha384', true)
            );

            return password_verify($wordpressPassword, $bcryptHash);
        }

        if (!str_starts_with($storedHash, '$P$') && !str_starts_with($storedHash, '$H$')) {
            return false;
        }

        if (strlen($storedHash) !== 34) {
            return false;
        }

        $countLog2 = strpos(self::ITOA64, $storedHash[3]);
        $salt = substr($storedHash, 4, 8);

        if ($countLog2 === false || $countLog2 < 7 || $countLog2 > 30 || strlen($salt) !== 8) {
            return false;
        }

        $count = 1 << $countLog2;
        $hash = md5($salt . $plainPassword, true);

        do {
            $hash = md5($hash . $plainPassword, true);
        } while (--$count);

        $calculated = substr($storedHash, 0, 12) . $this->encode64($hash, 16);

        return hash_equals($storedHash, $calculated);
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
