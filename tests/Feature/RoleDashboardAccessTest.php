<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function seedDemo(): void
    {
        $this->seed();
    }

    public function test_each_role_reaches_only_its_own_dashboard(): void
    {
        $this->seedDemo();

        $accounts = [
            ['owner@sheykhan.test', 'owner.dashboard'],
            ['teacher1@sheykhan.test', 'teacher.dashboard'],
            ['student1@sheykhan.test', 'student.dashboard'],
            ['parent@sheykhan.test', 'parent.dashboard'],
        ];

        foreach ($accounts as [$email, $route]) {
            $user = User::where('email', $email)->firstOrFail();

            $this->actingAs($user)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_student_cannot_open_teacher_or_owner_dashboards(): void
    {
        $this->seedDemo();

        $student = User::where('email', 'student1@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('teacher.dashboard'))
            ->assertForbidden();

        $this->actingAs($student)
            ->get(route('owner.dashboard'))
            ->assertForbidden();
    }
}
