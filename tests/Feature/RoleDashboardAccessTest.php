<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MobileNumber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

        public function test_each_role_reaches_only_its_own_dashboard(): void
    {
        $this->seed();

        $accounts = [
            ['owner@sheykhan.test', 'owner.dashboard'],
            ['teacher.math@sheykhan.test', 'teacher.dashboard'],
            ['student.armin@sheykhan.test', 'student.dashboard'],
            ['parent.armin@sheykhan.test', 'parent.dashboard'],
        ];

        foreach ($accounts as [$email, $route]) {
            $user = User::where('email', $email)->firstOrFail();

            $response = $this->actingAs($user)->get(route($route))->assertOk();

            if ($route === 'owner.dashboard') {
                $response->assertSee('class="app"', false)
                    ->assertSee('id="burger"', false)
                    ->assertSee('id="side"', false)
                    ->assertSee('id="main"', false);
            } elseif ($route === 'teacher.dashboard') {
                $response->assertSee('<nav class="bt"', false)
                    ->assertSee('data-teacher-menu-open', false)
                    ->assertSee('data-teacher-menu-close', false);
            } elseif ($route === 'student.dashboard') {
                $response->assertSee('<nav class="tabbar"', false)
                    ->assertSee('<details class="more">', false);
            } else {
                $response->assertSee('role-mobile-bottom-nav', false)
                    ->assertSee('data-role-more-open', false);
            }
        }
    }

    public function test_demo_seeders_sync_one_configured_password_for_all_quick_login_roles(): void
    {
        $configuredPassword = 'Configured-Demo-Password-2026';
        config([
            'role_access.seed_password' => $configuredPassword,
            'role_access.test_password' => $configuredPassword,
            'role_access.show_test_accounts' => true,
        ]);

        // Simulate an existing owner left behind by an older deployment.
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $ownerRole = \App\Models\Role::query()
            ->where('slug', 'academy-owner')
            ->firstOrFail();
        $staleOwner = User::factory()->create([
            'email' => 'owner@sheykhan.test',
            'mobile' => '091779234175',
            'status' => 'inactive',
            'password' => 'Previous-Demo-Password-2025',
        ]);
        $staleOwner->roles()->attach($ownerRole->id);

        // DatabaseSeeder's testing path runs TestFixtureSeeder, which must use
        // the same account synchronizer as the explicit demo seeder.
        $this->seed();

        $repairedOwner = User::query()
            ->where('email', 'owner@sheykhan.test')
            ->firstOrFail();

        $this->assertTrue(
            MobileNumber::valid(MobileNumber::normalize($repairedOwner->mobile)),
            'Legacy malformed demo mobile must be repaired to a valid Iranian mobile.',
        );
        $this->assertNotSame('091779234175', $repairedOwner->mobile);

        foreach (config('role_access.test_accounts', []) as $account) {
            $user = User::query()
                ->where('email', $account['email'])
                ->firstOrFail();

            $this->assertTrue(
                MobileNumber::valid(MobileNumber::normalize($user->mobile)),
                $account['email'] . ' must have a valid login mobile',
            );
            $this->assertSame('active', $user->status, $account['email'] . ' must be active');
            $this->assertTrue($user->hasRole($account['role_key']), $account['email'] . ' must keep its expected role');
            $this->assertTrue(
                \Illuminate\Support\Facades\Hash::check($configuredPassword, (string) $user->getAuthPassword()),
                $account['email'] . ' must match the password displayed by quick login',
            );
        }

        $this->get(route('login'))
            ->assertOk()
            ->assertSee($configuredPassword)
            ->assertSee('ورود سریع به نقش‌های سامانه');
    }

    public function test_every_quick_login_account_authenticates_and_reaches_its_own_dashboard(): void
    {
        $this->seed();

        foreach (config('role_access.test_accounts', []) as $account) {
            $user = User::query()
                ->where('email', $account['email'])
                ->firstOrFail();

            $this->get(route('login'))
                ->assertOk()
                ->assertSee($user->mobile)
                ->assertSee($account['role']);

            $this->post(route('login.store'), [
                'mobile' => $user->mobile,
                'password' => (string) config('role_access.test_password'),
            ])->assertRedirect(route(config('role_access.dashboard_routes')[$account['role_key']]));

            $this->get(route(config('role_access.dashboard_routes')[$account['role_key']]))
                ->assertOk();

            $this->post(route('logout'))->assertRedirect(route('home'));
        }
    }

    public function test_each_panel_rejects_users_from_other_roles(): void
    {
        $this->seed();

        $panels = [
            ['owner@sheykhan.test', 'owner.dashboard'],
            ['teacher.math@sheykhan.test', 'teacher.dashboard'],
            ['student.armin@sheykhan.test', 'student.dashboard'],
            ['parent.armin@sheykhan.test', 'parent.dashboard'],
            [(string) config('role_access.seo_specialist_email'), 'seo.index'],
        ];

        foreach ($panels as [$email, $ownRoute]) {
            $user = User::query()->where('email', $email)->firstOrFail();

            foreach ($panels as [$otherEmail, $otherRoute]) {
                if ($email === $otherEmail) {
                    continue;
                }

                $otherUser = User::query()->where('email', $otherEmail)->firstOrFail();

                $this->actingAs($user)->get(route($otherRoute))->assertForbidden();
                $this->actingAs($otherUser)->get(route($ownRoute))->assertForbidden();
            }
        }
    }

    public function test_student_cannot_open_teacher_or_owner_dashboards(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('teacher.dashboard'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('owner.dashboard'))
            ->assertForbidden();
    }
}
