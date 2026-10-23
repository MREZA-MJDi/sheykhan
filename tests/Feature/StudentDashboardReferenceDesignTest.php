<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardReferenceDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_uses_reference_workspace_and_real_learning_sections(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('student-shell-dashboard', false)
            ->assertSee('student-dashboard-reference', false)
            ->assertSee('sdr-courses-title', false)
            ->assertSee('sdr-activity-title', false)
            ->assertSee('sdr-assignments-title', false)
            ->assertSee('sdr-teachers-title', false)
            ->assertSee('sdr-calendar-title', false)
            ->assertSee('sdr-shortcuts-title', false)
            ->assertSee('فعالیت ۷ روز اخیر')
            ->assertSee('دوره‌های فعال من');
    }

    public function test_dark_reference_theme_does_not_apply_to_other_student_workspaces(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.courses.index'))
            ->assertOk()
            ->assertDontSee('student-shell-dashboard', false)
            ->assertSee('role-layout', false);
    }

    public function test_student_dashboard_styles_are_rtl_with_distinct_solid_course_tiles(): void
    {
        $stylesheet = file_get_contents(resource_path('css/student-dashboard-reference.css'));

        $this->assertIsString($stylesheet);
        $this->assertStringContainsString('direction: rtl;', $stylesheet);
        $this->assertStringContainsString('.student-shell-dashboard .role-sidebar', $stylesheet);
        $this->assertStringContainsString('#e94e62', $stylesheet);
        $this->assertStringContainsString('#168fe1', $stylesheet);
        $this->assertStringContainsString('#12a77d', $stylesheet);
        $this->assertStringContainsString('.sdr-course-row:nth-child(4n + 1)', $stylesheet);
    }

}
