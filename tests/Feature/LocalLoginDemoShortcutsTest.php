<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocalLoginDemoShortcutsTest extends TestCase
{
    public function test_demo_login_shortcuts_are_hidden_by_default_outside_local_environment(): void
    {
        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'production';
        config([
            'app.demo_login_enabled' => false,
            'app.seed_user_password' => '',
        ]);

        try {
            $this->get(route('login'))
                ->assertOk()
                ->assertDontSee('data-demo-login', false)
                ->assertDontSee('seo@sheykhan.test');
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }

    public function test_opted_in_demo_login_shortcuts_fill_each_role_credentials(): void
    {
        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'production';
        config([
            'app.demo_login_enabled' => true,
            'app.seed_user_password' => 'DeployDemoPass!2026',
        ]);

        try {
            $this->get(route('login'))
                ->assertOk()
                ->assertSee('data-demo-login', false)
                ->assertSee('data-demo-identifier="owner@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="teacher.math@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="student.armin@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="parent.armin@sheykhan.test"', false)
                ->assertSee('data-demo-identifier="seo@sheykhan.test"', false)
                ->assertSee('data-demo-password="DeployDemoPass!2026"', false);
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }

    public function test_database_seeder_creates_demo_role_accounts_only_after_explicit_opt_in(): void
    {
        $previousEnvironment = $this->app['env'];
        $this->app['env'] = 'production';
        config([
            'app.demo_login_enabled' => true,
            'app.seed_user_password' => 'DeployDemoPass!2026',
            'legacy_students.import_on_db_seed' => false,
        ]);

        try {
            $this->seed(\Database\Seeders\DatabaseSeeder::class);

            $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();

            $this->assertTrue(Hash::check('DeployDemoPass!2026', $owner->password));
            $this->assertDatabaseHas('users', ['email' => 'teacher.math@sheykhan.test']);
            $this->assertDatabaseHas('users', ['email' => 'student.armin@sheykhan.test']);
            $this->assertDatabaseHas('users', ['email' => 'parent.armin@sheykhan.test']);
            $this->assertDatabaseHas('users', ['email' => 'seo@sheykhan.test']);
        } finally {
            $this->app['env'] = $previousEnvironment;
        }
    }
}
