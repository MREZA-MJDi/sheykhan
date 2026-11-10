<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LocalRoleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_lists_quick_fill_credentials_for_every_dashboard_role(): void
    {
        $this->seed();

        $accounts = collect(config('role_access.test_accounts'));
        $usersByEmail = User::query()
            ->whereIn('email', $accounts->pluck('email')->all())
            ->get(['email', 'mobile'])
            ->keyBy('email');

        $this->assertCount(5, $accounts);
        $this->assertCount(5, $usersByEmail);

        $response = $this->get(route('login'))
            ->assertOk()
            ->assertSee('ورود سریع به نقش‌های سامانه')
            ->assertSee((string) config('role_access.test_password'))
            ->assertSee('data-test-login-fill', false);

        foreach ($accounts as $account) {
            $this->assertNotEmpty($usersByEmail->get($account['email'])?->mobile);
            $response->assertSee($account['role']);
            $response->assertSee($usersByEmail->get($account['email'])->mobile);
        }
    }

    public function test_quick_login_uses_the_persisted_mobile_when_it_differs_from_the_seed_mobile(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $persistedMobile = '091779234175';
        $owner->forceFill(['mobile' => $persistedMobile])->save();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-username="' . $persistedMobile . '"', false);

        $this->post(route('login.store'), [
            'mobile' => $persistedMobile,
            'password' => config('role_access.test_password'),
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($owner);
    }

    public function test_all_five_demo_roles_can_authenticate_with_their_persisted_mobile(): void
    {
        $this->seed();

        foreach (config('role_access.test_accounts') as $account) {
            $user = User::query()->where('email', $account['email'])->firstOrFail();

            $this->post(route('login.store'), [
                'mobile' => $user->mobile,
                'password' => config('role_access.test_password'),
            ])->assertSessionHasNoErrors();

            $this->assertAuthenticatedAs($user);
            Auth::logout();
            $this->app['auth']->forgetGuards();
        }
    }

    public function test_staging_can_enable_quick_fill_with_a_private_seed_password(): void
    {
        $previousEnvironment = $this->app['env'];
        config(['role_access.seed_password' => 'Staging-Only-Secret-2026']);

        $this->seed();

        try {
            $this->app->detectEnvironment(static fn (): string => 'staging');
            config([
                'role_access.show_test_accounts' => true,
                'role_access.test_password' => 'Staging-Only-Secret-2026',
            ]);

            $seoEmail = config('role_access.seo_specialist_email');

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('ورود سریع به نقش‌های سامانه')
                ->assertSee(User::query()->where('email', $seoEmail)->value('mobile'))
                ->assertSee('Staging-Only-Secret-2026')
                ->assertSee('data-test-login-fill', false);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
            $this->app['env'] = $previousEnvironment;
        }
    }

    public function test_only_active_accounts_with_the_expected_role_are_listed(): void
    {
        $this->seed();

        $ownerMobile = User::query()->where('email', 'owner@sheykhan.test')->value('mobile');
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $studentMobile = $student->mobile;
        $student->update(['status' => 'inactive']);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee($ownerMobile)
            ->assertDontSee($studentMobile);
    }

    public function test_role_credentials_are_hidden_when_the_configured_password_does_not_match_the_account(): void
    {
        $this->seed();
        config(['role_access.test_password' => 'password-that-does-not-match']);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(User::query()->where('email', 'owner@sheykhan.test')->value('mobile'))
            ->assertSee('حساب فعالی برای نمایش پیدا نشد');
    }

    public function test_invalid_mobile_is_still_rejected_for_non_demo_users(): void
    {
        $this->seed();

        $studentRoleId = Role::query()->where('slug', 'student')->value('id');
        $user = User::query()->create([
            'name' => 'کاربر عادی',
            'email' => 'ordinary-user@sheykhan.test',
            'mobile' => '091779234176',
            'status' => 'active',
            'password' => 'password',
        ]);
        $user->roles()->attach($studentRoleId);

        $this->post(route('login.store'), [
            'mobile' => $user->mobile,
            'password' => 'password',
        ])->assertSessionHasErrors('mobile');

        $this->assertGuest();
    }

    public function test_demo_credentials_are_rendered_in_production_too(): void
    {
        $this->seed();

        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            config([
                'role_access.show_test_accounts' => true,
                'role_access.test_password' => 'password',
            ]);

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('ورود سریع به نقش‌های سامانه')
                ->assertSee(User::query()->where('email', 'owner@sheykhan.test')->value('mobile'))
                ->assertSee(User::query()->where('email', 'teacher.math@sheykhan.test')->value('mobile'))
                ->assertSee(User::query()->where('email', 'student.armin@sheykhan.test')->value('mobile'))
                ->assertSee(User::query()->where('email', 'parent.armin@sheykhan.test')->value('mobile'))
                ->assertSee(User::query()->where('email', config('role_access.seo_specialist_email'))->value('mobile'))
                ->assertSee('data-test-login-fill', false);
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }
}
