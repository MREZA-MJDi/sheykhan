<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentTeacherDashboardDesignSystemTest extends TestCase
{
    public function test_student_and_teacher_layouts_use_their_consolidated_role_bundles(): void
    {
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $themePartial = file_get_contents(resource_path('views/layouts/partials/panel-theme-init.blade.php'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        $this->assertIsString($studentLayout);
        $this->assertIsString($teacherLayout);
        $this->assertIsString($themePartial);
        $this->assertIsString($viteConfig);

        $this->assertStringContainsString("@include('layouts.partials.panel-theme-init')", $studentLayout);
        $this->assertStringContainsString("@include('layouts.partials.panel-theme-init')", $teacherLayout);
        $this->assertStringContainsString('resources/css/student.css', $studentLayout);
        $this->assertStringContainsString('resources/css/teacher.css', $teacherLayout);
        $this->assertStringNotContainsString('role-dashboard-design-system.css', $studentLayout);
        $this->assertStringNotContainsString('role-dashboard-design-system.css', $teacherLayout);
        $this->assertStringContainsString("'resources/css/student.css'", $viteConfig);
        $this->assertStringContainsString("'resources/css/teacher.css'", $viteConfig);
        $this->assertStringNotContainsString('role-dashboard-design-system.css', $viteConfig);

        $this->assertStringContainsString("localStorage.getItem('sheykhan.panel.theme')", $themePartial);
        $this->assertStringContainsString("localStorage.getItem('sheykhan.student.theme')", $themePartial);
        $this->assertStringContainsString("localStorage.getItem('theme')", $themePartial);

        // Local fonts are bundled into the role stylesheets; layouts do not request Google Fonts.
        $this->assertStringNotContainsString('fonts.googleapis.com', $teacherLayout);
    }

    public function test_role_specific_bundles_include_their_design_tokens_and_accessible_focus_states(): void
    {
        $studentStyles = file_get_contents(resource_path('css/student.css'));
        $teacherStyles = file_get_contents(resource_path('css/teacher.css'));

        $this->assertIsString($studentStyles);
        $this->assertIsString($teacherStyles);

        foreach ([$studentStyles, $teacherStyles] as $styles) {
            $this->assertStringContainsString('--rds-primary:', $styles);
            $this->assertStringContainsString('--rds-surface', $styles);
            $this->assertStringContainsString('focus-visible', $styles);
            $this->assertStringNotContainsString('role-dashboard-design-system.css', $styles);
        }

        $this->assertStringContainsString('#dashboard', $studentStyles);
        $this->assertStringContainsString('#teacher-dashboard', $teacherStyles);
        $this->assertStringNotContainsString('.owner-shell', $studentStyles);
        $this->assertStringNotContainsString('.owner-shell', $teacherStyles);
    }

    public function test_avatar_fallback_behavior_is_shared_and_scoped_to_student_and_teacher(): void
    {
        $sharedScript = file_get_contents(resource_path('js/panel-base.js'));
        $studentScript = file_get_contents(resource_path('js/student.js'));
        $teacherScript = file_get_contents(resource_path('js/teacher.js'));

        $this->assertIsString($sharedScript);
        $this->assertIsString($studentScript);
        $this->assertIsString($teacherScript);

        $this->assertStringContainsString("document.body.matches('.student-shell, .teacher-shell')", $sharedScript);
        $this->assertStringContainsString("document.querySelectorAll('img[data-avatar-image]')", $sharedScript);
        $this->assertStringContainsString("image.addEventListener('error', showFallback, { once: true })", $sharedScript);
        $this->assertStringNotContainsString("querySelectorAll('[data-avatar-image]')", $studentScript);
        $this->assertStringNotContainsString("querySelectorAll('[data-avatar-image]')", $teacherScript);
        $this->assertStringContainsString('bootRoleAvatarFallback();', $sharedScript);
    }

    public function test_dashboard_markup_and_layout_rules_remain_role_specific(): void
    {
        $studentDashboard = file_get_contents(resource_path('views/student/dashboard.blade.php'));
        $teacherDashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));
        $studentStyles = file_get_contents(resource_path('css/student.css'));
        $teacherStyles = file_get_contents(resource_path('css/teacher.css'));

        $this->assertIsString($studentDashboard);
        $this->assertIsString($teacherDashboard);
        $this->assertIsString($studentStyles);
        $this->assertIsString($teacherStyles);

        $this->assertStringContainsString('id="dashboard"', $studentDashboard);
        $this->assertStringContainsString('#dashboard .hero', $studentStyles);
        $this->assertStringContainsString('id="teacher-dashboard"', $teacherDashboard);
        $this->assertStringContainsString('#teacher-dashboard', $teacherStyles);
        $this->assertStringNotContainsString('style="margin-top:18px"', $studentDashboard);
    }
}
