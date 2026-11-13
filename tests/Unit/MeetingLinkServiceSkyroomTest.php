<?php

namespace Tests\Unit;

use App\Services\MeetingLinkService;
use PHPUnit\Framework\TestCase;

final class MeetingLinkServiceSkyroomTest extends TestCase
{
    public function test_it_accepts_a_real_skyroom_channel_url(): void
    {
        $service = new MeetingLinkService();

        self::assertTrue($service->urlMatchesProvider(
            MeetingLinkService::SKYROOM,
            'https://www.skyroom.online/ch/sheykhan/math-101',
        ));
    }

    public function test_it_accepts_skyroom_ticket_urls_for_an_existing_channel(): void
    {
        $service = new MeetingLinkService();

        self::assertTrue($service->urlMatchesProvider(
            MeetingLinkService::SKYROOM,
            'https://skyroom.online/ch/sheykhan/math-101/t/eyJhbGciOiJIUzI1NiJ9',
        ));
    }

    public function test_it_rejects_non_skyroom_hosts_and_non_channel_urls(): void
    {
        $service = new MeetingLinkService();

        self::assertFalse($service->urlMatchesProvider(
            MeetingLinkService::SKYROOM,
            'https://skyroom.online/',
        ));
        self::assertFalse($service->urlMatchesProvider(
            MeetingLinkService::SKYROOM,
            'https://skyroom.online.evil.example/ch/sheykhan/math-101',
        ));
        self::assertFalse($service->urlMatchesProvider(
            MeetingLinkService::SKYROOM,
            'http://www.skyroom.online/ch/sheykhan/math-101',
        ));
    }

    public function test_it_does_not_generate_fake_skyroom_rooms(): void
    {
        $service = new MeetingLinkService();

        self::assertSame(
            'لینک باید از اتاق واقعی Skyroom با قالب https://www.skyroom.online/ch/نام‌کاربری/نام‌اتاق باشد.',
            $service->validationMessage(MeetingLinkService::SKYROOM),
        );
    }
}
