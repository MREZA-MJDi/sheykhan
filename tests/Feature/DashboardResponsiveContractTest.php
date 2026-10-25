<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardResponsiveContractTest extends TestCase
{
    public function test_owner_dashboard_alert_is_content_sized_and_cannot_stretch_into_a_full_page_warning_strip(): void
    {
        $css = file_get_contents(resource_path('css/admin.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('grid-auto-rows: max-content;', $css);
        $this->assertStringContainsString('.owner-shell .owner-dashboard > .owner-focus-card {', $css);
        $this->assertStringContainsString('height: fit-content !important;', $css);
        $this->assertStringContainsString('align-self: start;', $css);
        $this->assertStringContainsString('background: #fff8e9 !important;', $css);
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
        $css = file_get_contents(resource_path('css/panel-design-system.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media (max-width: 700px)', $css);
        $this->assertStringContainsString('.parent-shell .role-main {', $css);
        $this->assertStringContainsString('padding-bottom: calc(112px + env(safe-area-inset-bottom, 0px)) !important;', $css);
    }

}
