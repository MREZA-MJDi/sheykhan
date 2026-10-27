<?php

namespace Tests\Feature;

use Tests\TestCase;

class DashboardResponsiveContractTest extends TestCase
{
    public function test_owner_dashboard_uses_a_route_scoped_responsive_stylesheet(): void
    {
        $css = file_get_contents(resource_path('css/admin-dashboard.css'));
        $layout = file_get_contents(resource_path('views/layouts/owner.blade.php'));
        $view = file_get_contents(resource_path('views/owner/dashboard.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertIsString($view);

        $this->assertStringContainsString('.owner-dashboard-page .admin-dashboard {', $css);
        $this->assertStringContainsString('.ad-dashboard__span-quarter { grid-column: span 3; }', $css);
        $this->assertStringContainsString('@media (max-width: 600px)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
        $this->assertStringContainsString("request()->routeIs('owner.dashboard')", $layout);
        $this->assertStringContainsString("@vite('resources/css/admin-dashboard.css')", $layout);
        $this->assertStringContainsString('class="admin-dashboard" data-admin-dashboard', $view);
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

    public function test_student_and_teacher_dashboard_shells_are_responsive_on_tablets_and_phones(): void
    {
        $css = file_get_contents(resource_path('css/panel-design-system.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('@media (min-width: 761px) and (max-width: 900px)', $css);
        $this->assertStringContainsString('.student-shell .app,', $css);
        $this->assertStringContainsString('.teacher-shell .app {', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr) !important;', $css);
        $this->assertStringContainsString('@media (max-width: 760px)', $css);
        $this->assertStringContainsString('body.student-shell,', $css);
        $this->assertStringContainsString('body.teacher-shell {', $css);
        $this->assertStringContainsString('padding: 8px !important;', $css);
        $this->assertStringContainsString('border-radius: 20px;', $css);
        $this->assertStringContainsString('padding-bottom: calc(124px + env(safe-area-inset-bottom, 0px)) !important;', $css);
    }

}
