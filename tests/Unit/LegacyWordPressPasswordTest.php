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
            '$P$B12345678KrcV6c7XbhUsava2BrG7b.'
        ));
    }

    public function test_ph_pass_hash_rejects_an_incorrect_password_and_malformed_hash(): void
    {
        $service = new LegacyWordPressPassword();

        $this->assertFalse($service->verify(
            'incorrect-password',
            '$P$B12345678KrcV6c7XbhUsava2BrG7b.'
        ));
        $this->assertFalse($service->verify('legacy-import-password', '$P$short'));
        $this->assertFalse($service->verify('legacy-import-password', '$2y$10$not-phpass'));
    }
}
