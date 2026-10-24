<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

final class OwnerAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_owner_route_requires_authentication_active_account_and_a_permission(): void
    {
        $ownerRoutes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route): bool => str_starts_with((string) $route->getName(), 'owner.'));

        $this->assertGreaterThan(0, $ownerRoutes->count(), 'Expected named Owner routes to be registered.');

        foreach ($ownerRoutes as $route) {
            $middleware = strtolower(implode('|', $route->gatherMiddleware()));
            $identity = sprintf('%s [%s]', $route->getName(), implode('|', $route->methods()));

            $this->assertStringContainsString('auth', $middleware, $identity . ' must require authentication.');
            $this->assertStringContainsString('active', $middleware, $identity . ' must require an active account.');
            $this->assertStringContainsString('permission', $middleware, $identity . ' must be permission-gated.');
        }
    }

    public function test_unscoped_owner_workspaces_reject_an_academy_owned_by_another_user(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $permissionNames = [
            'attendance.view', 'attendance.manage',
            'parents.view', 'parents.manage',
            'exams.view', 'exams.manage',
            'live_classes.view', 'live_classes.manage',
            'teacher_payments.view', 'teacher_payments.manage',
        ];
        $permissionIds = Permission::query()->whereIn('name', $permissionNames)->pluck('id');
        $this->assertCount(count($permissionNames), $permissionIds, 'The route authorization test depends on the canonical permission seeder.');
        $ownerRole->permissions()->sync($permissionIds);
        $owner->unsetRelation('roles');
        Cache::flush();

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آموزشگاه خارج از محدوده',
            'slug' => 'foreign-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);

        $readRoutes = [
            'owner.attendance.index',
            'owner.communications.index',
            'owner.messages.index',
            'owner.counseling.index',
            'owner.exams.index',
            'owner.recordings.index',
            'owner.teacher-payments.index',
        ];

        foreach ($readRoutes as $routeName) {
            $this->actingAs($owner)
                ->get(route($routeName, $foreignAcademy))
                ->assertNotFound();
        }
    }

    public function test_owner_cannot_create_messages_or_teacher_payments_for_a_foreign_academy(): void
    {
        $this->seed();
        Cache::flush();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $ownerRole = Role::query()->where('slug', 'academy-owner')->firstOrFail();
        $permissionNames = [
            'parents.view', 'parents.manage',
            'teacher_payments.view', 'teacher_payments.manage',
        ];
        $ownerRole->permissions()->sync(
            Permission::query()->whereIn('name', $permissionNames)->pluck('id')
        );
        $owner->unsetRelation('roles');
        Cache::flush();

        $foreignOwner = User::factory()->create(['status' => 'active']);
        $foreignAcademy = Academy::query()->create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آموزشگاه مقصد غیرمجاز',
            'slug' => 'foreign-write-' . Str::lower(Str::random(10)),
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.communications.store', $foreignAcademy), [
                'target' => 'all_parents',
                'category' => 'announcement',
                'subject' => 'نباید ثبت شود',
                'body' => 'این اعلان برای آموزشگاه متعلق به مدیر دیگری است.',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('academy_messages', [
            'academy_id' => $foreignAcademy->id,
            'subject' => 'نباید ثبت شود',
        ]);

        $teacher = User::factory()->create(['status' => 'active']);
        $foreignAcademy->users()->attach($teacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('owner.teacher-payments.store', $foreignAcademy), [
                'teacher_id' => $teacher->id,
                'title' => 'نباید پرداخت ثبت شود',
                'amount' => 100000,
                'status' => 'paid',
                'reference' => 'cross-academy-test',
            ])
            ->assertNotFound();

        $this->assertDatabaseMissing('teacher_payments', [
            'academy_id' => $foreignAcademy->id,
            'title' => 'نباید پرداخت ثبت شود',
        ]);
    }
}
