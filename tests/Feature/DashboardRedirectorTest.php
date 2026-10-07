<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DashboardRedirector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRedirectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_has_a_dedicated_dashboard(): void
    {
        $this->seed();

        $redirector = app(DashboardRedirector::class);

        $expectations = [
            'owner@sheykhan.test' => 'owner.dashboard',
            'teacher.math@sheykhan.test' => 'teacher.dashboard',
            'student.armin@sheykhan.test' => 'student.dashboard',
            'parent.armin@sheykhan.test' => 'parent.dashboard',
        ];

        foreach ($expectations as $email => $route) {
            $user = User::where('email', $email)->firstOrFail();

            $this->assertSame($route, $redirector->routeName($user));
        }
    }

    public function test_student_login_redirect_does_not_replay_an_intended_owner_url(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $response = app(DashboardRedirector::class)->redirect($student);

        $response->assertRedirect(route('student.dashboard'));
    }
}
