<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Setting;
use App\Models\SpotPlayerLicense;
use App\Models\SpotPlayerLicenseIssuance;
use App\Services\CourseManagementService;
use App\Services\SpotPlayerService;
use App\Services\SpotPlayerDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

final class SpotPlayerSettingsController extends Controller
{
    public function edit(
        Request $request,
        SpotPlayerService $spotPlayer,
        CourseManagementService $courseManagement
    ): View {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $academyIds = $courseManagement->accessibleAcademies($request->user())->modelKeys();

        $courseQuery = Course::query()
            ->select(['id', 'academy_id', 'title', 'status', 'spotplayer_course_id', 'updated_at'])
            ->whereIn('academy_id', $academyIds);
        $licenseQuery = SpotPlayerLicense::query()
            ->whereHas('course', fn ($query) => $query->whereIn('academy_id', $academyIds));
        $issuanceQuery = SpotPlayerLicenseIssuance::query()
            ->whereHas('course', fn ($query) => $query->whereIn('academy_id', $academyIds));

        $issuanceCounts = (clone $issuanceQuery)
            ->select('status')
            ->selectRaw('COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('owner.spotplayer.settings', [
            'isConfigured' => $spotPlayer->isConfigured(),
            'mode' => $spotPlayer->mode(),
            'environmentKeyConfigured' => filled(config('services.spotplayer.api_key')),
            'storedKeyConfigured' => Setting::query()->where('key', 'spotplayer_api_key_encrypted')->exists(),
            'environmentModeOverride' => filled(config('services.spotplayer.mode')),
            'maxActiveDevices' => max(1, min(99, (int) config('services.spotplayer.max_active_devices', 1))),
            'webAppDevices' => max(0, min(99, (int) config('services.spotplayer.web_app_devices', 1))),
            'courseStats' => [
                'total' => (clone $courseQuery)->count(),
                'mapped' => (clone $courseQuery)->whereNotNull('spotplayer_course_id')->where('spotplayer_course_id', '<>', '')->count(),
                'unmapped' => (clone $courseQuery)->where(fn ($query) => $query->whereNull('spotplayer_course_id')->orWhere('spotplayer_course_id', ''))->count(),
            ],
            'licenseStats' => [
                'total' => (clone $licenseQuery)->count(),
                'active' => (clone $licenseQuery)->where('status', 'active')->count(),
                'test' => (clone $licenseQuery)->where('provider_mode', 'test')->count(),
                'production' => (clone $licenseQuery)->where('provider_mode', 'production')->count(),
            ],
            'issuanceCounts' => $issuanceCounts,
            'spotPlayerCourses' => (clone $courseQuery)
                ->with('academy:id,name')
                ->withCount('spotplayerLicenses')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->paginate(8)
                ->withQueryString(),
            'licenseRecords' => (clone $licenseQuery)
                ->with(['student:id,name', 'course:id,title,academy_id'])
                ->orderByDesc('issued_at')
                ->orderByDesc('id')
                ->limit(10)
                ->get(),
            'issuanceRecords' => (clone $issuanceQuery)
                ->whereIn('status', ['issuing', 'failed', 'review_required'])
                ->with(['student:id,name', 'course:id,title,academy_id'])
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->limit(8)
                ->get(),
        ]);
    }
    public function update(Request $request, SpotPlayerService $spotPlayer): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('courses.manage'), 403);

        $data = $request->validate([
            'api_key' => ['nullable', 'string', 'min:1', 'max:2048'],
            'mode' => ['required', 'in:test,production'],
            'clear_api_key' => ['sometimes', 'boolean'],
            'confirm_production' => ['sometimes', 'accepted'],
        ]);

        if ($data['mode'] === 'production' && ! ($data['confirm_production'] ?? false)) {
            throw ValidationException::withMessages([
                'confirm_production' => 'برای فعال‌کردن حالت Production باید تأیید صریح ثبت شود.',
            ]);
        }

        $newKey = trim((string) ($data['api_key'] ?? ''));
        if ($newKey !== '') {
            Setting::query()->updateOrCreate(
                ['key' => 'spotplayer_api_key_encrypted'],
                [
                    'value' => Crypt::encryptString($newKey),
                    'type' => 'encrypted',
                    'group' => 'integrations',
                    'is_public' => false,
                ],
            );
        } elseif ($request->boolean('clear_api_key')) {
            Setting::query()->where('key', 'spotplayer_api_key_encrypted')->delete();
        }

        Setting::query()->updateOrCreate(
            ['key' => 'spotplayer_mode'],
            [
                'value' => $data['mode'],
                'type' => 'string',
                'group' => 'integrations',
                'is_public' => false,
            ],
        );

        // Deliberately do not log request fields or the API key.
        Log::info('SpotPlayer integration settings updated', [
            'user_id' => $request->user()->id,
            'mode_selected' => $data['mode'],
            'api_key_changed' => $newKey !== '',
            'api_key_cleared_from_database' => $request->boolean('clear_api_key') && $newKey === '',
        ]);

        return redirect()
            ->route('owner.spotplayer.edit', ['tab' => 'settings'])
            ->with('success', $spotPlayer->isConfigured()
                ? 'تنظیمات SpotPlayer ذخیره شد. قبل از Production، پخش واقعی یک دورهٔ آزمایشی را بررسی کن.'
                : 'حالت اتصال ذخیره شد؛ برای فعال‌شدن صدور لایسنس، کلید API معتبر لازم است.');
    }
}
