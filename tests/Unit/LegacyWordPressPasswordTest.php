<?php

namespace Tests\Unit;

use App\Services\LegacyWordPressPassword;
use PHPUnit\Framework\TestCase;

class LegacyWordPressPasswordTest extends TestCase
{
    public function test_ph_pass_hash_is_verified_with_the_original_password(): void
    {
        $service = new LegacyWordPressPassword();

        $this->assertTrue($service->verify(
            'legacy-import-password',
            '$P$B12345678KrMX6cdabhk0avaPBrG9b.'
        ));
    }

    public function test_ph_pass_hash_rejects_an_incorrect_password_and_malformed_hash(): void
    {
        $service = new LegacyWordPressPassword();

        $this->assertFalse($service->verify(
            'incorrect-password',
            '$P$B12345678KrMX6cdabhk0avaPBrG9b.'
        ));
        $this->assertFalse($service->verify('legacy-import-password', '$P$short'));
        $this->assertFalse($service->verify('legacy-import-password', '$2y$10$not-phpass'));
    }

    public function test_wrapped_wordpress_bcrypt_hash_is_verified_then_can_be_upgraded(): void
    {
        $plain = 'legacy-wp-bcrypt-test-password';
        $prehashed = base64_encode(hash_hmac('sha384', $plain, 'wp-sha384', true));
        $stored = '$wp' . password_hash($prehashed, PASSWORD_BCRYPT);
        $service = new LegacyWordPressPassword();

        $this->assertTrue($service->isLegacyHash($stored));
        $this->assertTrue($service->supportsHashFormat($stored));
        $this->assertTrue($service->verify($plain, $stored));
        $this->assertFalse($service->verify('wrong-test-password', $stored));
        $this->assertFalse($service->supportsHashFormat('$wp$2y$10$short'));
    }
}
