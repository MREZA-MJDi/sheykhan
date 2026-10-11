<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

final class MeetingLinkService
{
    public const JITSI = 'Jitsi';
    public const GOOGLE_MEET = 'Google Meet';
    public const ZOOM = 'Zoom';
    public const OTHER = 'سایر';

    /**
     * Resolve a provider URL without pretending that external providers can be
     * provisioned without their APIs. Jitsi rooms need no account/API; Google
     * Meet and Zoom URLs must first be created in their respective services.
     */
    public function resolve(string $provider, ?string $meetingUrl): string
    {
        $meetingUrl = trim((string) $meetingUrl);

        if ($provider === self::JITSI && $meetingUrl === '') {
            return $this->generateJitsiUrl();
        }

        if (!$this->urlMatchesProvider($provider, $meetingUrl)) {
            throw ValidationException::withMessages([
                'meeting_url' => $this->validationMessage($provider),
            ]);
        }

        return $meetingUrl;
    }

    public function generateJitsiUrl(): string
    {
        // The room materializes at Jitsi when the first participant opens it.
        // The unpredictable room name reduces accidental room collisions.
        return 'https://meet.jit.si/Sheykhan-' . Str::lower(Str::random(32));
    }

    public function urlMatchesProvider(string $provider, ?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $parts = parse_url(trim($url));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower(rtrim((string) ($parts['host'] ?? ''), '.'));

        if (
            $scheme !== 'https'
            || $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)
        ) {
            return false;
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');
        if ($provider !== self::OTHER && $path === '') {
            // The official provider home page is not a room invitation.
            return false;
        }

        return match ($provider) {
            self::JITSI => $host === 'meet.jit.si',
            self::GOOGLE_MEET => $host === 'meet.google.com',
            self::ZOOM => $host === 'zoom.us' || str_ends_with($host, '.zoom.us'),
            self::OTHER => true,
            default => false,
        };
    }

    public function validationMessage(string $provider): string
    {
        return match ($provider) {
            self::JITSI => 'برای Jitsi باید لینک https://meet.jit.si باشد؛ در صورت خالی بودن، شیخان لینک یکتا می‌سازد.',
            self::GOOGLE_MEET => 'لینک باید از دامنه رسمی https://meet.google.com باشد. ابتدا جلسه را در Google Meet بساز و لینک آن را اینجا قرار بده.',
            self::ZOOM => 'لینک باید از دامنه رسمی Zoom باشد. ابتدا جلسه را در Zoom بساز و لینک ورود آن را اینجا قرار بده.',
            self::OTHER => 'لینک سرویس دیگر باید یک آدرس HTTPS معتبر باشد.',
            default => 'سرویس جلسه معتبر نیست.',
        };
    }
}
