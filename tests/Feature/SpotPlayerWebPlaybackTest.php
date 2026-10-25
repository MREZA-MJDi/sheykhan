<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class SpotPlayerWebPlaybackTest extends TestCase
{
    use RefreshDatabase;

    public function test_entitled_student_can_refresh_the_spotplayer_web_cookie(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        $newCookie = str_repeat('b', 24)
            . str_pad(dechex((int) floor(microtime(true) * 1000) + 3_600_000), 12, '0', STR_PAD_LEFT);

        Http::fake([
            'https://app.spotplayer.ir/' => Http::response('', 200, [
                'Set-Cookie' => 'X=' . $newCookie . '; Path=/; Secure; HttpOnly',
            ]),
        ]);

        $response = $this->actingAs($student)
            ->get(route('student.courses.spotplayer-cookie', $course));

        $response->assertNoContent();
        $this->assertStringContainsString(
            'SPOTPLAYER_X=' . $newCookie,
            (string) $response->headers->get('Set-Cookie')
        );

        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool =>
            $request->method() === 'HEAD'
            && $request->url() === 'https://app.spotplayer.ir/'
        );
    }

    public function test_student_without_course_access_cannot_refresh_the_spotplayer_cookie(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.nika@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        Http::fake();

        $this->actingAs($student)
            ->get(route('student.courses.spotplayer-cookie', $course))
            ->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_unexpired_spotplayer_cookie_does_not_trigger_an_upstream_request(): void
    {
        $this->seed();

        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course->update(['spotplayer_course_id' => 'spot-course-123']);

        $currentCookie = str_repeat('a', 24)
            . str_pad(dechex((int) floor(microtime(true) * 1000) + 3_600_000), 12, '0', STR_PAD_LEFT);

        Http::fake();

        $this->actingAs($student)
            ->withUnencryptedCookie('SPOTPLAYER_X', $currentCookie)
            ->get(route('student.courses.spotplayer-cookie', $course))
            ->assertNoContent();

        Http::assertNothingSent();
    }
}
