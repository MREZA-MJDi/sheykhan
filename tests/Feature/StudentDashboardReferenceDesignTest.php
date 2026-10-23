<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardReferenceDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_is_ready_for_replacement_ui_without_legacy_design(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.')
            ->assertDontSee('student-dashboard-reference', false)
            ->assertDontSee('sdr-hero', false);
    }

    public function test_other_student_routes_use_the_neutral_placeholder_shell(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.courses.index'))
            ->assertOk()
            ->assertDontSee('student-shell-dashboard', false)
            ->assertSee('role-layout', false)
            ->assertSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.');
    }

    public function test_legacy_dashboard_styles_are_removed_from_the_placeholder_stylesheet(): void
    {
        $stylesheet = file_get_contents(resource_path('css/student-dashboard-reference.css'));

        $this->assertIsString($stylesheet);
        $this->assertStringNotContainsString('.student-shell-dashboard .role-sidebar', $stylesheet);
        $this->assertStringNotContainsString('.sdr-course-row:nth-child(4n + 1)', $stylesheet);
        $this->assertStringContainsString('Legacy student dashboard styles removed', $stylesheet);
    }
}
