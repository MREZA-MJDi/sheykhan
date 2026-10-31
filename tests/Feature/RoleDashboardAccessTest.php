<?php

namespace Tests\Feature;

use App\Models\User;
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
            } else {
                $response->assertSee('role-mobile-bottom-nav', false)
                    ->assertSee('data-role-more-open', false);
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
