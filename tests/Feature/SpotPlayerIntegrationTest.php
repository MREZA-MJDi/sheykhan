<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\SpotPlayerLicense;
use App\Models\User;
use App\Services\SpotPlayerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class SpotPlayerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_entitled_student_receives_one_encrypted_spotplayer_license_and_reuses_it(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        config([
            'services.spotplayer.api_key' => 'fake-secret-key',
            'services.spotplayer.mode' => 'test',
            'services.spotplayer.max_active_devices' => 1,
            'services.spotplayer.web_app_devices' => 1,
        ]);

        Http::fake([
            'https://panel.spotplayer.ir/*' => Http::response([
                '_id' => 'spot-license-' . $student->id,
                'key' => 'abcdef123456licensekey',
                'url' => 'https://dl.spotplayer.ir/licenses/spot-license-file',
            ], 200),
        ]);

        $this->actingAs($student)
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertRedirect(route('student.courses.show', $course));

        $this->assertDatabaseCount('spot_player_licenses', 1);

        $license = SpotPlayerLicense::query()
            ->where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        $this->assertSame('abcdef123456licensekey', $license->license_key);
        $this->assertSame('active', $license->status);
        $this->assertNotSame('abcdef123456licensekey', $license->getRawOriginal('license_key'));

        $this->actingAs($student)
            ->get(route('student.courses.show', $course))
            ->assertOk()
            ->assertSee('abcdef123456licensekey')
            ->assertSee('https://app.spotplayer.ir/', false)
            ->assertDontSee('fake-secret-key');

        $this->actingAs($student)
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertRedirect(route('student.courses.show', $course));

        $this->assertDatabaseCount('spot_player_licenses', 1);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool =>
            $request->url() === 'https://panel.spotplayer.ir/license/edit/'
            && $request->hasHeader('$API', 'fake-secret-key')
            && $request->hasHeader('$LEVEL', '-1')
            && ($request['course'] ?? null) === ['spot-course-123']
            && ($request['test'] ?? null) === true
            && ($request['device']['p6'] ?? null) === 1
            && ($request['device']['p1'] ?? null) === 0
        );
    }

    public function test_student_without_course_access_cannot_generate_a_spotplayer_license(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.nika@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        config([
            'services.spotplayer.api_key' => 'fake-secret-key',
            'services.spotplayer.mode' => 'test',
        ]);
        Http::fake();

        $this->actingAs($student)
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertNotFound();

        $this->assertDatabaseCount('spot_player_licenses', 0);
        Http::assertNothingSent();
    }

    public function test_spotplayer_does_not_store_a_license_when_provider_response_is_invalid(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        config([
            'services.spotplayer.api_key' => 'fake-secret-key',
            'services.spotplayer.mode' => 'test',
        ]);
        Http::fake([
            'https://panel.spotplayer.ir/*' => Http::response(['error' => 'missing key'], 200),
        ]);

        $this->actingAs($student)
            ->from(route('student.courses.show', $course))
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHasErrors('spotplayer');

        $this->assertDatabaseCount('spot_player_licenses', 0);
    }
    public function test_spotplayer_requires_server_api_key_and_a_supported_mode(): void
    {
        config([
            'services.spotplayer.api_key' => '',
            'services.spotplayer.mode' => 'test',
        ]);

        $service = app(SpotPlayerService::class);
        $this->assertFalse($service->isConfigured());

        config([
            'services.spotplayer.api_key' => 'server-only-key',
            'services.spotplayer.mode' => 'staging',
        ]);
        $this->assertFalse($service->isConfigured());

        config(['services.spotplayer.mode' => 'production']);
        $this->assertTrue($service->isConfigured());
    }

    public function test_missing_spotplayer_course_mapping_fails_closed_without_an_api_call(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => null]);

        config([
            'services.spotplayer.api_key' => 'fake-secret-key',
            'services.spotplayer.mode' => 'test',
        ]);
        Http::fake();

        $this->actingAs($student)
            ->from(route('student.courses.show', $course))
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHasErrors('spotplayer');

        $this->assertDatabaseCount('spot_player_licenses', 0);
        Http::assertNothingSent();
    }

    public function test_provider_http_failure_does_not_persist_a_license(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        config([
            'services.spotplayer.api_key' => 'fake-secret-key',
            'services.spotplayer.mode' => 'test',
        ]);
        Http::fake([
            'https://panel.spotplayer.ir/*' => Http::response(['error' => 'provider unavailable'], 503),
        ]);

        $this->actingAs($student)
            ->from(route('student.courses.show', $course))
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHasErrors('spotplayer');

        $this->assertDatabaseCount('spot_player_licenses', 0);
        Http::assertSentCount(1);
    }

    public function test_unsafe_license_download_host_is_rejected_without_storing_the_license(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        config([
            'services.spotplayer.api_key' => 'fake-secret-key',
            'services.spotplayer.mode' => 'test',
        ]);
        Http::fake([
            'https://panel.spotplayer.ir/*' => Http::response([
                '_id' => 'spot-license-' . $student->id,
                'key' => 'abcdef123456licensekey',
                'url' => 'https://attacker.example/license',
            ], 200),
        ]);

        $this->actingAs($student)
            ->from(route('student.courses.show', $course))
            ->post(route('student.courses.spotplayer-license', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHasErrors('spotplayer');

        $this->assertDatabaseCount('spot_player_licenses', 0);
        Http::assertSentCount(1);
    }

}
