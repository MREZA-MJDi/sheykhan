<?php

namespace App\Services;

use App\Models\Course;
use App\Models\SpotPlayerLicense;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SpotPlayerService
{
    public function isConfigured(): bool
    {
        return filled(config('services.spotplayer.api_key'))
            && in_array(config('services.spotplayer.mode'), ['test', 'production'], true);
    }

    public function licenseForStudent(User $student, Course $course): SpotPlayerLicense
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'spotplayer' => 'اتصال SpotPlayer هنوز در تنظیمات سرور فعال نشده است.',
            ]);
        }

        abort_unless(app(StudentAccessService::class)->course($student, $course), 404);

        $externalCourseId = trim((string) $course->spotplayer_course_id);
        if ($externalCourseId === '') {
            throw ValidationException::withMessages([
                'spotplayer' => 'برای این دوره شناسهٔ معتبر SpotPlayer تنظیم نشده است.',
            ]);
        }

        return DB::transaction(function () use ($student, $course, $externalCourseId): SpotPlayerLicense {
            User::query()->whereKey($student->getKey())->lockForUpdate()->firstOrFail();

            $existing = SpotPlayerLicense::query()
                ->where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('status', 'active')
                ->first();

            if ($existing) {
                return $existing;
            }

            $apiKey = (string) config('services.spotplayer.api_key');
            $maxDevices = max(1, min(99, (int) config('services.spotplayer.max_active_devices', 1)));
            $webDevices = max(0, min($maxDevices, (int) config('services.spotplayer.web_app_devices', 1)));
            $watermarkTexts = [
                ['text' => mb_substr(trim($student->name), 0, 100)],
                ['text' => 'Sheykhan ID ' . $student->id],
            ];
            $requestBody = [
                'test' => config('services.spotplayer.mode') === 'test',
                'name' => mb_substr(trim($student->name), 0, 120),
                'course' => [$externalCourseId],
                'watermark' => ['texts' => $watermarkTexts],
                'device' => [
                    'p0' => $maxDevices,
                    'p1' => 0,
                    'p2' => 0,
                    'p3' => 0,
                    'p4' => 0,
                    'p5' => 0,
                    'p6' => $webDevices,
                ],
                'payload' => json_encode([
                    'platform' => 'sheykhan',
                    'student_id' => (int) $student->id,
                    'course_id' => (int) $course->id,
                ], JSON_UNESCAPED_SLASHES),
            ];

            try {
                // TLS verification stays enabled; the third-party wrapper disables it.
                $response = Http::acceptJson()
                    ->withHeaders([
                        '$API' => $apiKey,
                        '$LEVEL' => '-1',
                    ])
                    ->timeout(12)
                    ->post('https://panel.spotplayer.ir/license/edit/', $requestBody);
            } catch (Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'spotplayer' => 'ارتباط با SpotPlayer برقرار نشد؛ کمی بعد دوباره تلاش کن.',
                ]);
            }

            if (! $response->successful()) {
                throw ValidationException::withMessages([
                    'spotplayer' => 'SpotPlayer صدور لایسنس را تأیید نکرد. تنظیمات اتصال را بررسی کن.',
                ]);
            }

            $body = $response->json();
            if (! is_array($body) || blank($body['_id'] ?? null) || blank($body['key'] ?? null) || blank($body['url'] ?? null)) {
                throw ValidationException::withMessages([
                    'spotplayer' => 'پاسخ SpotPlayer برای صدور لایسنس ناقص است؛ لایسنس ذخیره نشد.',
                ]);
            }

            $licenseUrl = $this->normalizeLicenseUrl((string) $body['url']);
            if ($licenseUrl === null) {
                throw ValidationException::withMessages([
                    'spotplayer' => 'آدرس لایسنس دریافتی از SpotPlayer معتبر نیست؛ لایسنس ذخیره نشد.',
                ]);
            }

            return SpotPlayerLicense::query()->create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'external_license_id' => mb_substr((string) $body['_id'], 0, 120),
                'license_key' => (string) $body['key'],
                'license_url' => $licenseUrl,
                'status' => 'active',
                'issued_at' => now(),
            ]);
        }, 1);
    }

    private function normalizeLicenseUrl(string $url): ?string
    {
        $url = trim($url);
        if (str_starts_with($url, '/')) {
            $url = 'https://dl.spotplayer.ir' . $url;
        }

        $parts = parse_url($url);
        if (
            ! is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || strtolower((string) ($parts['host'] ?? '')) !== 'dl.spotplayer.ir'
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return null;
        }

        return $url;
    }
}
