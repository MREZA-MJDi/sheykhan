<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
                'mobile' => $user->mobile,
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
            'mobile' => $student->mobile,
            'password' => (string) (config('role_access.seed_password') ?? 'Sheykhan@12345'),
        ]);

        $response->assertRedirect(route('login'))->assertSessionHasErrors('mobile');
        $this->assertGuest();
    }
    public function test_email_is_not_accepted_as_a_login_identifier(): void
    {
        $this->seed();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->from(route('login'))->post(route('login.store'), [
            'identifier' => $student->email,
            'password' => (string) (config('role_access.seed_password') ?? 'Sheykhan@12345'),
        ])->assertRedirect(route('login'))->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_public_registration_generates_a_temporary_password_for_mobile_account(): void
    {
        $this->seed();
        $mobile = '09129998877';

        $response = $this->post(route('register.store'), [
            'first_name' => 'کاربر',
            'last_name' => 'جدید',
            'mobile' => $mobile,
            'account_type' => 'student',
        ]);

        $response->assertRedirect(route('password.change.show'));
        $this->assertAuthenticated();
        $user = User::query()->where('mobile', $mobile)->firstOrFail();
        $temporaryPassword = $response->getSession()->get('registration_temporary_password');

        $this->assertNull($user->email);
        $this->assertTrue((bool) $user->must_change_password);
        $this->assertIsString($temporaryPassword);
        $this->assertTrue(Hash::check($temporaryPassword, $user->password));

        $this->get(route('password.change.show'))
            ->assertOk()
            ->assertSee($mobile)
            ->assertSee($temporaryPassword);
    }

}
