<?php

namespace Tests\\Feature;

use Tests\\TestCase;

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
            ->assertSee(config('role_access.test_password'))
            ->assertSee('data-test-login-fill', false);
    }

    public function test_demo_credentials_are_not_rendered_outside_local_and_testing(): void
    {
        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'production';

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
