<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationSessionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_login_rotates_the_session_reaches_the_expected_dashboard_and_logout_clears_auth(): void
    {
        $this->seed();

        // Exercise the production-style persistence backend rather than the
        // default in-memory session driver used by the test environment.
        config(['session.driver' => 'database']);

        $password = (string) (config('role_access.seed_password') ?? 'Sheykhan@12345');
        $accounts = [
            ['owner@sheykhan.test', 'owner.dashboard'],
            ['teacher.math@sheykhan.test', 'teacher.dashboard'],
            ['student.armin@sheykhan.test', 'student.dashboard'],
            ['parent.armin@sheykhan.test', 'parent.dashboard'],
        ];

        foreach ($accounts as [$email, $dashboardRoute]) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->get(route('login'))->assertOk();
            $sessionIdBeforeLogin = session()->getId();

            $login = $this->post(route('login.store'), [
                'identifier' => $email,
                'password' => $password,
            ]);

            $login->assertRedirect(route($dashboardRoute));
            $this->assertAuthenticatedAs($user);
            $this->assertNotSame(
                $sessionIdBeforeLogin,
                session()->getId(),
                'A successful login must rotate the session identifier.'
            );

            $this->get(route($dashboardRoute))->assertOk();

            $logout = $this->post(route('logout'));
            $logout->assertRedirect(route('home'))->assertSessionHas('success');
            $this->assertGuest();
        }
    }

    public function test_inactive_user_cannot_create_an_authenticated_session(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $student->forceFill(['status' => 'inactive'])->save();

        $response = $this->from(route('login'))->post(route('login.store'), [
            'identifier' => $student->email,
            'password' => (string) (config('role_access.seed_password') ?? 'Sheykhan@12345'),
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasErrors('identifier');
        $this->assertGuest();
    }
}
