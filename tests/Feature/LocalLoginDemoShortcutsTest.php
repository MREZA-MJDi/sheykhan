<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocalLoginDemoShortcutsTest extends TestCase
{
    public function test_demo_login_shortcuts_are_hidden_outside_local_environment(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('data-demo-login', false)
            ->assertDontSee('seo@sheykhan.test');
    }

    public function test_local_login_page_offers_one_click_fill_for_each_dashboard_role(): void
    {
        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'local';

        try {
            $this->get(route('login'))
                ->assertOk()
                ->assertSee('data-demo-login', false)
                ->assertSee('data-demo-identifier="owner@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="teacher.math@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="student.armin@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="parent.armin@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="seo@sheykhan.test"', false);
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }
}
