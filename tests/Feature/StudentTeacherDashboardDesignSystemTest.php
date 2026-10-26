<?php

namespace Tests\Feature;

use Tests\TestCase;

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

    public function test_shared_dashboard_system_exposes_tokens_and_only_shared_interactions(): void
    {
        $styles = file_get_contents(resource_path('css/role-dashboard-design-system.css'));
        $studentStyles = file_get_contents(resource_path('css/student.css'));
        $teacherStyles = file_get_contents(resource_path('css/teacher.css'));

        $this->assertIsString($styles);
        $this->assertIsString($studentStyles);
        $this->assertIsString($teacherStyles);

        $this->assertStringContainsString('--rds-primary:', $styles);
        $this->assertStringContainsString('--bg1: var(--rds-canvas)', $styles);
        $this->assertStringContainsString('--bg: var(--rds-canvas)', $styles);
        $this->assertStringContainsString('focus-visible', $styles);
        $this->assertStringContainsString('var(--rds-surface', $studentStyles);
        $this->assertStringContainsString('var(--rds-surface', $teacherStyles);

        // This file is only a token/interaction layer. Role-specific layouts stay in their own stylesheets.
        $this->assertStringNotContainsString('#dashboard', $styles);
        $this->assertStringNotContainsString('#teacher-dashboard', $styles);
        $this->assertStringNotContainsString('.owner-shell', $styles);
        $this->assertStringNotContainsString('.admin-shell', $styles);
    }

    public function test_both_dashboard_templates_use_role_specific_roots_with_the_shared_token_contract(): void
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
