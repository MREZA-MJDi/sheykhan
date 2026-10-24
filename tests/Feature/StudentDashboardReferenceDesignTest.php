<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardReferenceDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_uses_the_new_role_shell_and_backend_data(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('خانه یادگیری من')
            ->assertSee('دوره‌های فعال')
            ->assertSee('تکالیف ارسال‌نشده')
            ->assertSee('student-shell', false)
            ->assertSee('data-avatar-fallback', false)
            ->assertDontSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.')
            ->assertDontSee('sdr-hero', false);
    }

    public function test_student_course_page_uses_the_new_role_shell_and_real_course_content(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $this->actingAs($student)
            ->get(route('student.courses.index'))
            ->assertOk()
            ->assertSee('دوره‌های در دسترس')
            ->assertSee('student-shell', false)
            ->assertDontSee('رابط کاربری جدید بخش دانش‌آموز در حال آماده‌سازی است.')
            ->assertDontSee('student-shell-dashboard', false);
    }

    public function test_legacy_dashboard_stylesheet_is_removed_from_the_repository(): void
    {
        $this->assertFileDoesNotExist(resource_path('css/student-dashboard-reference.css'));

        $vite = file_get_contents(base_path('vite.config.js'));
        $this->assertIsString($vite);
        $this->assertStringNotContainsString('resources/css/student-dashboard-reference.css', $vite);
    }
}
