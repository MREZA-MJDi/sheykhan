<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionSeederIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeder_preserves_existing_mobile_and_login_shows_all_five_demo_roles(): void
    {
        config(['legacy_students.import_on_db_seed' => false]);
        $this->seed(RolePermissionSeeder::class);

        $existingOwner = User::query()->create([
            'name' => 'مدیر قبلی',
            'email' => 'owner@sheykhan.test',
            'mobile' => '09123456789',
            'status' => 'inactive',
            'password' => 'old-demo-password',
        ]);
        $existingOwner->roles()->attach(Role::query()->where('slug', 'academy-owner')->value('id'));

        $this->app->detectEnvironment(static fn (): string => 'production');

        $accounts = [
            ['email' => 'owner@sheykhan.test', 'role' => 'مدیر آموزشگاه', 'role_key' => 'academy-owner'],
            ['email' => 'seo.demo@sheykhan.test', 'role' => 'متخصص SEO', 'role_key' => 'seo-specialist'],
            ['email' => 'teacher.math@sheykhan.test', 'role' => 'مدرس', 'role_key' => 'teacher'],
            ['email' => 'student.armin@sheykhan.test', 'role' => 'دانش‌آموز', 'role_key' => 'student'],
            ['email' => 'parent.armin@sheykhan.test', 'role' => 'والد', 'role_key' => 'parent'],
        ];

        try {
            $this->artisan('db:seed', ['--force' => true])->assertExitCode(0);

            config([
                'role_access.show_test_accounts' => true,
                'role_access.test_password' => 'password',
                'role_access.test_accounts' => $accounts,
            ]);

            foreach ($accounts as $account) {
                $user = User::query()->where('email', $account['email'])->firstOrFail();

                $this->assertSame('active', $user->status);
                $this->assertNotEmpty($user->mobile);
                $this->assertTrue(Hash::check('password', (string) $user->getAuthPassword()));
                $this->assertTrue($user->roles()->where('slug', $account['role_key'])->exists());
            }

            $this->assertSame('09123456789', $existingOwner->fresh()->mobile);

            $response = $this->get(route('login'))
                ->assertOk()
                ->assertSee('ورود سریع به نقش‌های سامانه')
                ->assertSee('data-test-login-fill', false);

            foreach ($accounts as $account) {
                $user = User::query()->where('email', $account['email'])->firstOrFail();

                $response->assertSee($account['role']);
                $response->assertSee($user->mobile);
                $response->assertSee('data-username="' . $user->mobile . '"', false);
            }

            $this->assertDatabaseCount('courses', 0);
            $this->assertDatabaseCount('products', 0);
            $this->assertDatabaseCount('home_hero_slides', 0);
        } finally {
            $this->app->detectEnvironment(static fn (): string => 'testing');
        }
    }
}
