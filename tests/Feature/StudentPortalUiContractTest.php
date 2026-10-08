<?php

namespace TestsFeature;

use AppModelsUser;
use IlluminateFoundationTestingRefreshDatabase;
use TestsTestCase;

class StudentPortalUiContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_workspace_tabs_render_through_shared_portal_shell(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        foreach ([
            'student.dashboard',
            'student.courses.index',
            'student.live-classes.index',
            'student.assignments.index',
            'student.exams.index',
            'student.resources.index',
            'student.results.index',
            'student.attendance.index',
            'student.achievements.index',
            'student.notes.index',
            'student.profile.edit',
        ] as $route) {
            $this->actingAs($student)
                ->get(route($route))
                ->assertOk()
                ->assertSee('role-layout', false);
        }
    }

    public function test_student_cannot_enter_teacher_profile_workspace(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('teacher.profile.edit'))
            ->assertForbidden();
    }
}
