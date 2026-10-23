<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalRoleLoginTest extends TestCase
{
    public function test_local_login_page_lists_quick_fill_credentials_for_every_dashboard_role(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('حساب‌های تست همکار')
            ->assertSee('owner@sheykhan.test')
            ->assertSee('teacher.math@sheykhan.test')
            ->assertSee('student.armin@sheykhan.test')
            ->assertSee('parent.armin@sheykhan.test')
            ->assertSee('seo@sheykhan.test')
            ->assertSee((string) config('role_access.test_password'))
            ->assertSee('data-test-login-fill', false);
    }

    public function test_staging_can_enable_quick_fill_with_a_private_seed_password(): void
    {
        $previousEnvironment = $this->app['env'];

        try {
            $this->app->detectEnvironment(static fn (): string => 'staging');
            config([
                'role_access.show_test_accounts' => true,
                'role_access.test_password' => 'Staging-Only-Secret-2026',
                'role_access.allowed_test_ips' => ['127.0.0.1'],
            ]);

            $this->get(route('login'))
                ->assertOk()
                ->assertSee('حساب‌های تست همکار')
                ->assertSee('seo@sheykhan.test')
                ->assertSee('Staging-Only-Secret-2026')
                ->assertSee('data-test-login-fill', false);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
            $this->app['env'] = $previousEnvironment;
        }
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
                ->assertDontSee('حساب‌های تست همکار')
                ->assertDontSee('data-test-login-fill', false)
                ->assertDontSee('owner@sheykhan.test');
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }
}
