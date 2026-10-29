<?php

namespace App\Services;

use App\Models\Course;
use App\Models\SpotPlayerLicense;
use App\Models\SpotPlayerLicenseIssuance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

final class SpotPlayerService
{
    public function isConfigured(): bool
    {
        return filled($this->apiKey())
            && in_array($this->mode(), ['test', 'production'], true);
    }

    public function mode(): string
    {
        $environmentMode = config('services.spotplayer.mode');

        if (is_string($environmentMode) && $environmentMode !== '') {
            return $environmentMode;
        }

        $storedMode = Setting::query()->where('key', 'spotplayer_mode')->value('value');

        $configuredMode = config('services.spotplayer.mode');

        return filled($storedMode)
            ? (string) $storedMode
            : (filled($configuredMode) ? (string) $configuredMode : 'test');
    }

    private function apiKey(): string
    {
        $environmentKey = (string) config('services.spotplayer.api_key');
        if (filled($environmentKey)) {
            return $environmentKey;
        }

        $encryptedKey = Setting::query()->where('key', 'spotplayer_api_key_encrypted')->value('value');
        if (! filled($encryptedKey)) {
            return '';
        }

        try {
            return Crypt::decryptString((string) $encryptedKey);
        } catch (Throwable $exception) {
            report($exception);

            return '';
        }
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

        $mode = $this->mode();

        // Reserve an issuance in a short transaction. The outbound HTTP request
        // is intentionally outside the transaction; a timeout is an unknown
        // provider outcome and must never trigger an automatic duplicate issue.
        $reservation = DB::transaction(function () use ($student, $course, $externalCourseId, $mode): array {
            User::query()->whereKey($student->getKey())->lockForUpdate()->firstOrFail();

            if (! app(StudentAccessService::class)->course($student, $course)) {
                return ['kind' => 'access_denied'];
            }

            $license = SpotPlayerLicense::query()
                ->where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            $issuance = SpotPlayerLicenseIssuance::query()
                ->where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($license) {
                if (
                    $license->status === 'active'
                    && $issuance
                    && $issuance->status === 'active'
                    && hash_equals((string) $issuance->provider_course_id, $externalCourseId)
                    && hash_equals((string) $issuance->provider_mode, $mode)
                ) {
                    return ['kind' => 'license', 'license' => $license];
                }

                $this->requireManualReconciliation(
                    $issuance,
                    $student,
                    $course,
                    $externalCourseId,
                    $mode,
                    'Existing license record requires manual provider reconciliation before any reissue.',
                );

                return ['kind' => 'review_required'];
            }

            if (! $issuance) {
                $issuance = SpotPlayerLicenseIssuance::query()->create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'provider_course_id' => $externalCourseId,
                    'provider_mode' => $mode,
                    'status' => 'issuing',
                    'attempt_count' => 1,
                    'started_at' => now(),
                    'last_error' => null,
                ]);

                return ['kind' => 'reserved', 'issuance_id' => $issuance->id];
            }

            if ($issuance->status === 'review_required') {
                return ['kind' => 'review_required'];
            }

            if ($issuance->status === 'active') {
                $issuance->forceFill([
                    'status' => 'review_required',
                    'completed_at' => now(),
                    'last_error' => 'Issuance is active but its local license record is missing.',
                ])->save();

                return ['kind' => 'review_required'];
            }

            if ($issuance->status === 'issuing') {
                if ($issuance->started_at && $issuance->started_at->greaterThan(now()->subMinutes(3))) {
                    return ['kind' => 'issuing'];
                }

                $issuance->forceFill([
                    'status' => 'review_required',
                    'completed_at' => now(),
                    'last_error' => 'Issuance timed out; provider status must be checked before retry.',
                ])->save();

                return ['kind' => 'review_required'];
            }

            if ($issuance->status === 'failed') {
                $issuance->forceFill([
                    'provider_course_id' => $externalCourseId,
                    'provider_mode' => $mode,
                    'status' => 'issuing',
                    'attempt_count' => ((int) $issuance->attempt_count) + 1,
                    'started_at' => now(),
                    'completed_at' => null,
                    'last_error' => null,
                ])->save();

                return ['kind' => 'reserved', 'issuance_id' => $issuance->id];
            }

            $issuance->forceFill([
                'status' => 'review_required',
                'completed_at' => now(),
                'last_error' => 'Unknown issuance state; provider status must be checked.',
            ])->save();

            return ['kind' => 'review_required'];
        }, 3);

        if ($reservation['kind'] === 'license') {
            return $reservation['license'];
        }

        if ($reservation['kind'] === 'access_denied') {
            abort(404);
        }

        if ($reservation['kind'] === 'issuing') {
            throw ValidationException::withMessages([
                'spotplayer' => 'درخواست صدور لایسنس در حال بررسی است؛ چند لحظه بعد صفحه را تازه‌سازی کنید.',
            ]);
        }

        if ($reservation['kind'] === 'review_required') {
            throw ValidationException::withMessages([
                'spotplayer' => 'وضعیت لایسنس نیازمند تطبیق با پنل ناشر SpotPlayer است. برای جلوگیری از لایسنس تکراری، درخواست جدید ارسال نشد.',
            ]);
        }

        $issuanceId = (int) $reservation['issuance_id'];
        $apiKey = $this->apiKey();
        $maxDevices = max(1, min(99, (int) config('services.spotplayer.max_active_devices', 1)));
        $webDevices = max(0, min($maxDevices, (int) config('services.spotplayer.web_app_devices', 1)));

        $requestBody = [
            'test' => $mode === 'test',
            'name' => mb_substr(trim($student->name), 0, 120),
            'course' => [$externalCourseId],
            'watermark' => [
                'texts' => [
                    ['text' => mb_substr(trim($student->name), 0, 100)],
                    ['text' => 'Sheykhan ID ' . $student->id],
                ],
            ],
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
            $response = Http::acceptJson()
                ->withHeaders([
                    '$API' => $apiKey,
                    '$LEVEL' => '-1',
                ])
                ->timeout(12)
                ->post('https://panel.spotplayer.ir/license/edit/', $requestBody);
        } catch (Throwable $exception) {
            $this->setIssuanceStatus(
                $issuanceId,
                'review_required',
                'Network result is unknown; provider status must be checked before retry.',
            );
            report($exception);

            throw ValidationException::withMessages([
                'spotplayer' => 'پاسخ قطعی از SpotPlayer دریافت نشد. ممکن است لایسنس در پنل ناشر ساخته شده باشد؛ برای جلوگیری از تکرار، ابتدا پنل بررسی شود.',
            ]);
        }

        if (! $response->successful()) {
            $definitiveRejection = $response->clientError();
            $this->setIssuanceStatus(
                $issuanceId,
                $definitiveRejection ? 'failed' : 'review_required',
                $definitiveRejection
                    ? 'Provider rejected the issuance request.'
                    : 'Provider returned a server error; status is uncertain.',
            );

            throw ValidationException::withMessages([
                'spotplayer' => $definitiveRejection
                    ? 'SpotPlayer درخواست صدور لایسنس را رد کرد؛ شناسهٔ دوره و تنظیمات حساب ناشر را بررسی کنید.'
                    : 'SpotPlayer خطای سرور داد و وضعیت صدور قطعی نیست؛ برای جلوگیری از لایسنس تکراری، ابتدا پنل ناشر بررسی شود.',
            ]);
        }

        $body = $response->json();
        if (is_array($body) && filled(data_get($body, 'ex.msg'))) {
            $this->setIssuanceStatus($issuanceId, 'failed', 'Provider explicitly rejected the issuance request.');

            throw ValidationException::withMessages([
                'spotplayer' => 'SpotPlayer صدور لایسنس را رد کرد؛ شناسهٔ دوره و وضعیت حساب ناشر را بررسی کنید.',
            ]);
        }

        if (! is_array($body) || blank($body['_id'] ?? null) || blank($body['key'] ?? null) || blank($body['url'] ?? null)) {
            $this->setIssuanceStatus(
                $issuanceId,
                'review_required',
                'Provider response did not include a verifiable license payload.',
            );

            throw ValidationException::withMessages([
                'spotplayer' => 'پاسخ SpotPlayer برای لایسنس کامل نبود؛ وضعیت پنل ناشر باید بررسی شود و درخواست خودکار تکرار نمی‌شود.',
            ]);
        }

        $licenseUrl = $this->normalizeLicenseUrl((string) $body['url']);
        if ($licenseUrl === null) {
            $this->setIssuanceStatus(
                $issuanceId,
                'review_required',
                'Provider returned a license URL outside the allowed host.',
            );

            throw ValidationException::withMessages([
                'spotplayer' => 'آدرس لایسنس دریافتی معتبر نیست. لایسنس خام ذخیره نشد و وضعیت پنل باید بررسی شود.',
            ]);
        }

        try {
            $license = DB::transaction(function () use (
                $student,
                $course,
                $externalCourseId,
                $mode,
                $issuanceId,
                $body,
                $licenseUrl,
            ): ?SpotPlayerLicense {
                $issuance = SpotPlayerLicenseIssuance::query()->whereKey($issuanceId)->lockForUpdate()->firstOrFail();

                if ($issuance->status !== 'issuing') {
                    return null;
                }

                if (! app(StudentAccessService::class)->course($student, $course)) {
                    $issuance->forceFill([
                        'status' => 'review_required',
                        'completed_at' => now(),
                        'last_error' => 'Student access changed while provider issuance was in flight.',
                    ])->save();

                    return null;
                }

                $activeExists = SpotPlayerLicense::query()
                    ->where('student_id', $student->id)
                    ->where('course_id', $course->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->exists();

                if ($activeExists) {
                    $issuance->forceFill([
                        'status' => 'review_required',
                        'completed_at' => now(),
                        'last_error' => 'An active license appeared during provider issuance.',
                    ])->save();

                    return null;
                }

                $license = SpotPlayerLicense::query()->create([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'external_license_id' => mb_substr((string) $body['_id'], 0, 120),
                    'license_key' => (string) $body['key'],
                    'license_url' => $licenseUrl,
                    'status' => 'active',
                    'issued_at' => now(),
                ]);

                $issuance->forceFill([
                    'status' => 'active',
                    'completed_at' => now(),
                    'last_error' => null,
                ])->save();

                return $license;
            }, 3);
        } catch (Throwable $exception) {
            $this->setIssuanceStatus(
                $issuanceId,
                'review_required',
                'Provider may have issued a license but local persistence did not complete.',
            );
            report($exception);

            throw ValidationException::withMessages([
                'spotplayer' => 'صدور لایسنس در پایگاه داده نهایی نشد. برای جلوگیری از صدور تکراری، ابتدا وضعیت پنل ناشر بررسی شود.',
            ]);
        }

        if (! $license) {
            throw ValidationException::withMessages([
                'spotplayer' => 'دسترسی دوره هنگام صدور تغییر کرد یا لایسنس دیگری ثبت شد. وضعیت پنل ناشر باید بررسی شود.',
            ]);
        }

        return $license;
    }

    private function requireManualReconciliation(
        ?SpotPlayerLicenseIssuance $issuance,
        User $student,
        Course $course,
        string $externalCourseId,
        string $mode,
        string $reason,
    ): void {
        if (! $issuance) {
            $issuance = new SpotPlayerLicenseIssuance([
                'student_id' => $student->id,
                'course_id' => $course->id,
                'provider_course_id' => $externalCourseId,
                'provider_mode' => $mode,
                'attempt_count' => 0,
                'started_at' => now(),
            ]);
        }

        $issuance->forceFill([
            'status' => 'review_required',
            'completed_at' => now(),
            'last_error' => mb_substr($reason, 0, 190),
        ])->save();
    }

    private function setIssuanceStatus(int $issuanceId, string $status, string $safeReason): void
    {
        SpotPlayerLicenseIssuance::query()
            ->whereKey($issuanceId)
            ->where('status', 'issuing')
            ->update([
                'status' => $status,
                'completed_at' => now(),
                'last_error' => mb_substr($safeReason, 0, 190),
                'updated_at' => now(),
            ]);
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
