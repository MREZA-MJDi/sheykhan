<?php

namespace Tests\\Feature;

use Tests\\TestCase;

class StudentTeacherDashboardDesignSystemTest extends TestCase
{
    public function test_student_and_teacher_layouts_share_theme_bootstrap_and_dashboard_styles(): void
    {
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $themePartial = file_get_contents(resource_path('views/layouts/partials/panel-theme-init.blade.php'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        $this->assertIsString($studentLayout);
        $this->assertIsString($teacherLayout);
        $this->assertIsString($themePartial);
        $this->assertIsString($viteConfig);

        foreach ([$studentLayout, $teacherLayout] as $layout) {
            $this->assertStringContainsString("@include('layouts.partials.panel-theme-init')", $layout);
            $this->assertStringContainsString('resources/css/role-dashboard-design-system.css', $layout);
        }

        $this->assertStringContainsString("localStorage.getItem('sheykhan.panel.theme')", $themePartial);
        $this->assertStringContainsString("localStorage.getItem('sheykhan.student.theme')", $themePartial);
        $this->assertStringContainsString("localStorage.getItem('theme')", $themePartial);
        $this->assertStringContainsString("'resources/css/role-dashboard-design-system.css'", $viteConfig);

        // app.css already serves the bundled local Vazirmatn font for both roles.
        $this->assertStringNotContainsString('fonts.googleapis.com', $teacherLayout);
    }

    public function test_shared_dashboard_styles_are_scoped_to_student_and_teacher(): void
    {
        $styles = file_get_contents(resource_path('css/role-dashboard-design-system.css'));

        $this->assertIsString($styles);
        $this->assertStringContainsString('body.student-shell', $styles);
        $this->assertStringContainsString('body.teacher-shell', $styles);
        $this->assertStringContainsString('#dashboard.role-dashboard', $styles);
        $this->assertStringContainsString('#teacher-dashboard.role-dashboard', $styles);
        $this->assertStringNotContainsString('.owner-shell', $styles);
        $this->assertStringNotContainsString('.admin-shell', $styles);
    }

    public function test_both_dashboard_templates_use_the_shared_design_contract(): void
    {
        $studentDashboard = file_get_contents(resource_path('views/student/dashboard.blade.php'));
        $teacherDashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));

        $this->assertIsString($studentDashboard);
        $this->assertIsString($teacherDashboard);

        $this->assertStringContainsString('role-dashboard role-dashboard--student', $studentDashboard);
        $this->assertStringContainsString('data-role="student"', $studentDashboard);
        $this->assertStringContainsString('role-dashboard role-dashboard--teacher', $teacherDashboard);
        $this->assertStringContainsString('data-role="teacher"', $teacherDashboard);
        $this->assertStringNotContainsString('style="margin-top:18px"', $studentDashboard);
    }
}
