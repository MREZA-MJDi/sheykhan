<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardResponsiveContractTest extends TestCase
{
    public function test_owner_workspace_feedback_and_page_roots_remain_content_sized_and_responsive(): void
    {
        $css = file_get_contents(resource_path('css/owner-dashboard-v2.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.owner-dashboard-feedback', $css);
        $this->assertStringContainsString('.owner-admin-v2 .main-content', $css);
        $this->assertStringContainsString('grid-column:1/-1;min-width:0;width:100%', $css);
        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*700px\)/', $css);
        $this->assertStringContainsString('min-width:0', $css);
    }

    public function test_student_mobile_navigation_has_safe_gutters_and_content_padding_above_fixed_bar(): void
    {
        $css = file_get_contents(resource_path('css/student.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.student-shell .role-mobile-bottom-nav {', $css);
        $this->assertStringContainsString('position: fixed !important;', $css);
        $this->assertStringContainsString('left: max(8px, env(safe-area-inset-left, 0px)) !important;', $css);
        $this->assertStringContainsString('bottom: calc(8px + env(safe-area-inset-bottom, 0px)) !important;', $css);
        $this->assertStringContainsString('padding-bottom: calc(112px + env(safe-area-inset-bottom, 0px)) !important;', $css);
        $this->assertStringContainsString('@media (max-width: 380px)', $css);
    }

    public function test_parent_mobile_navigation_does_not_cover_the_last_content_row(): void
    {
        $css = file_get_contents(resource_path('css/parent.css'));

        $this->assertIsString($css);
        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*700px\)/', $css);
        $this->assertStringContainsString('.parent-shell .role-main {', $css);
        $this->assertStringContainsString('padding-bottom:calc(96px + env(safe-area-inset-bottom,0px));', $css);
    }

    public function test_student_and_teacher_dashboard_shells_are_responsive_on_tablets_and_phones(): void
    {
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));

        $this->assertIsString($studentCss);
        $this->assertIsString($teacherCss);
        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*900px\)/', $studentCss);
        $this->assertStringContainsString('.student-shell .app', $studentCss);
        $this->assertStringContainsString('@media (max-width: 380px)', $studentCss);
        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*980px\)/', $teacherCss);
        $this->assertStringContainsString('.teacher-shell .app', $teacherCss);
        $this->assertStringContainsString('grid-template-columns:1fr', $teacherCss);
        $this->assertStringContainsString('@media (max-width: 760px)', $teacherCss);
    }
}
