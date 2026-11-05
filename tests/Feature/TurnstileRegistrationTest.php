<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_requires_a_server_verified_turnstile_token_when_enabled(): void
    {
        $this->seed();

        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret_key' => 'test-secret-key',
            'services.turnstile.verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        ]);

        Http::fakeSequence()
            ->push(['success' => false, 'error-codes' => ['invalid-input-response']], 200)
            ->push(['success' => true, 'hostname' => 'localhost', 'action' => 'register'], 200);

        $payload = [
            'name' => 'دانش‌آموز آزمایشی',
            'email' => 'turnstile.student@example.test',
            'account_type' => 'student',
            'password' => 'SafePass123!',
            'password_confirmation' => 'SafePass123!',
            'cf-turnstile-response' => 'invalid-token',
        ];

        $this->from(route('register'))
            ->post(route('register.store'), $payload)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
        $this->assertGuest();

        $payload['cf-turnstile-response'] = 'valid-token';

        $this->post(route('register.store'), $payload)
            ->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => $payload['email']]);
        Http::assertSentCount(2);
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && $request['secret'] === 'test-secret-key';
        });
    }

    public function test_public_registration_requires_turnstile_response_when_protection_is_enabled(): void
    {
        $this->seed();

        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret_key' => 'test-secret-key',
        ]);

        Http::fake();

        $this->from(route('register'))
            ->post(route('register.store'), [
                'name' => 'دانش‌آموز آزمایشی',
                'email' => 'missing.turnstile@example.test',
                'account_type' => 'student',
                'password' => 'SafePass123!',
                'password_confirmation' => 'SafePass123!',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['email' => 'missing.turnstile@example.test']);
        Http::assertNothingSent();
    }
}
