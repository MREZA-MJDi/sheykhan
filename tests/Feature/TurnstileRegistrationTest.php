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
            'first_name' => 'دانش‌آموز',
            'last_name' => 'آزمایشی',
            'mobile' => '09129997777',
            'account_type' => 'student',
            'cf-turnstile-response' => 'invalid-token',
        ];

        $this->from(route('register'))
            ->post(route('register.store'), $payload)
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['mobile' => $payload['mobile']]);
        $this->assertGuest();

        $payload['cf-turnstile-response'] = 'valid-token';

        $this->post(route('register.store'), $payload)
            ->assertRedirect(route('password.change.show'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['mobile' => $payload['mobile'], 'email' => null]);
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
                'first_name' => 'دانش‌آموز',
                'last_name' => 'آزمایشی',
                'mobile' => '09129997778',
                'account_type' => 'student',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['mobile' => '09129997778']);
        Http::assertNothingSent();
    }
}
