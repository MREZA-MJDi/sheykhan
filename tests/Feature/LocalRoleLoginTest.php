<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalRoleLoginTest extends TestCase
{
    use RefreshDatabase;
    public function test_local_login_page_lists_quick_fill_credentials_for_every_dashboard_role(): void
    {
        $this->seed();
        $mobiles = User::query()->whereIn('email', ['owner@sheykhan.test', 'teacher.math@sheykhan.test', 'student.armin@sheykhan.test', 'parent.armin@sheykhan.test', 'seo@sheykhan.com'])->get(['email', 'mobile'])->keyBy('email');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ورود سریع به نقش‌های سامانه')
            ->assertSee($mobiles['owner@sheykhan.test']->mobile)
            ->assertSee($mobiles['teacher.math@sheykhan.test']->mobile)
            ->assertSee($mobiles['student.armin@sheykhan.test']->mobile)
            ->assertSee($mobiles['parent.armin@sheykhan.test']->mobile)
            ->assertSee($mobiles['seo@sheykhan.com']->mobile)
            ->assertSee((string) config('role_access.test_password'))
            ->assertSee('data-test-login-fill', false);
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
                'role_access.allowed_test_ips' => ['127.0.0.1'],
            ]);

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('ورود سریع به نقش‌های سامانه')
                ->assertSee(User::query()->where('email', 'seo@sheykhan.com')->value('mobile'))
                ->assertSee('Staging-Only-Secret-2026')
                ->assertSee('data-test-login-fill', false);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
            $this->app['env'] = $previousEnvironment;
        }
    }

    public function test_production_can_render_synced_demo_credentials_when_explicitly_enabled(): void
    {
        $previousEnvironment = $this->app['env'];
        $password = 'Production-Demo-Only-2026!';

        config([
            'role_access.seed_password' => $password,
            'role_access.test_password' => $password,
        ]);
        $this->seed();

        try {
            $this->app['env'] = 'production';
            config([
                'role_access.show_test_accounts' => true,
                'role_access.test_password' => $password,
            ]);

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('ورود سریع به نقش‌های سامانه')
                ->assertSee($password)
                ->assertSee(User::query()->where('email', 'owner@sheykhan.test')->value('mobile'))
                ->assertSee('data-test-login-fill', false);
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }

    public function test_only_active_accounts_with_the_expected_role_are_listed(): void
    {
        $this->seed();
        $ownerMobile = User::query()->where('email', 'owner@sheykhan.test')->value('mobile');
        $studentMobile = User::query()->where('email', 'student.armin@sheykhan.test')->value('mobile');
        User::query()->where('email', 'student.armin@sheykhan.test')->update(['status' => 'inactive']);

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
            ->assertSee('حساب نمایشی با رمز تنظیم‌شده همگام نیست.');
    }

    public function test_demo_credentials_are_not_rendered_outside_local_and_testing(): void
    {
        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'production';
        config([
            'role_access.show_test_accounts' => false,
            'role_access.test_password' => null,
        ]);

        try {
            $this->get(route('login'))
                ->assertOk()
                ->assertDontSee('ورود سریع به نقش‌های سامانه')
                ->assertDontSee('data-test-login-fill', false)
                ->assertDontSee('owner@sheykhan.test');
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }
}
