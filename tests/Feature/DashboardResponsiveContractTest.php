<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardResponsiveContractTest extends TestCase
{
    public function test_owner_dashboard_feedback_stays_inside_the_owner_workspace(): void
    {
        $css = file_get_contents(resource_path('css/owner-dashboard-v2.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.owner-dashboard-feedback{grid-column:1/-1}', $css);
        $this->assertStringContainsString('.owner-dashboard-feedback .alert.is-success', $css);
        $this->assertStringContainsString('.owner-dashboard-feedback .alert.is-error', $css);
        $this->assertStringContainsString('.owner-admin-v2 .main-content .panel-page-enter', $css);
        $this->assertStringContainsString('@media(max-width:960px)', $css);
        $this->assertStringContainsString('@media(max-width:520px)', $css);
    }

    public function test_student_mobile_navigation_has_safe_gutters_and_content_padding_above_fixed_bar(): void
    {
        $css = file_get_contents(resource_path('css/student.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.tabbar{display:none}', $css);
        $this->assertStringContainsString('position:fixed', $css);
        $this->assertStringContainsString('env(safe-area-inset-bottom', $css);
        $this->assertStringContainsString('padding:0', $css);
        $this->assertStringContainsString('@media (max-width:900px)', $css);
    }

    public function test_owner_workspaces_share_readable_sections_and_the_online_calendar_is_responsive(): void
    {
        $css = file_get_contents(resource_path('css/owner-dashboard-v2.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.main-content .panel-page-enter {', $css);
        $this->assertStringContainsString('.main-content .panel-page-enter .owner-stat-card {', $css);
        $this->assertStringContainsString('.main-content .panel-page-enter label {', $css);
        $this->assertStringContainsString('.main-content .owner-online-links-page .weekly-calendar-grid {', $css);
        $this->assertStringContainsString('.main-content .owner-online-links-page .weekly-calendar-event-link {', $css);
        $this->assertStringContainsString('.main-content .owner-online-links-page .owner-schedule-form {', $css);
        $this->assertStringContainsString('@media (max-width: 1250px)', $css);
        $this->assertStringContainsString('@media (max-width: 900px)', $css);
        $this->assertStringContainsString('@media (max-width: 600px)', $css);
    }

    public function test_parent_mobile_navigation_and_workspace_are_responsive(): void
    {
        $css = file_get_contents(resource_path('css/parent.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.parent-shell', $css);
        $this->assertStringContainsString('.parent-shell .role-main', $css);
        $this->assertStringContainsString('@media (max-width: 760px)', $css);
        $this->assertStringContainsString('.communication-page__form--two-column', $css);
    }

    public function test_student_and_teacher_dashboard_shells_are_responsive_on_tablets_and_phones(): void
    {
        $studentCss = file_get_contents(resource_path('css/student.css'));
        $teacherCss = file_get_contents(resource_path('css/teacher.css'));

        $this->assertIsString($studentCss);
        $this->assertIsString($teacherCss);
        $this->assertStringContainsString('.student-shell .app', $studentCss);
        $this->assertStringContainsString('.shell{', $teacherCss);
        $this->assertStringContainsString('@media (max-width:900px)', $studentCss);
        $this->assertStringContainsString('@media(max-width:860px)', $teacherCss);
        $this->assertStringContainsString('.tabbar{display:none}', $studentCss);
        $this->assertStringContainsString('env(safe-area-inset-bottom', $studentCss);
        $this->assertStringContainsString('.bt{display:flex;position:fixed', $teacherCss);
        $this->assertStringContainsString('.burger{display:grid;place-items:center}', $teacherCss);
        $this->assertStringContainsString('padding:clamp(8px,2vw,24px)', $studentCss);
        $this->assertStringContainsString('.fab{bottom:84px;left:14px}', $teacherCss);
    }
}
