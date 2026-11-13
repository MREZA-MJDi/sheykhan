<?php

namespace Tests\Feature;

use Tests\TestCase;

final class LearningExperienceUiContractTest extends TestCase
{
    public function test_owner_dashboard_has_a_scoped_operational_design(): void
    {
        $view = file_get_contents(resource_path('views/owner/dashboard.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/owner.blade.php'));
        $styles = file_get_contents(resource_path('css/owner-admin.css'));

        $this->assertIsString($view);
        $this->assertIsString($layout);
        $this->assertIsString($styles);
        $this->assertStringContainsString("owner-dashboard-live", $view);
        $this->assertStringContainsString("@yield('body-class')", $layout);
        $this->assertStringContainsString('.owner-admin-v2.owner-dashboard-live', $styles);
        $this->assertStringContainsString('.owner-dashboard-kpi', $view);
        $this->assertStringContainsString('.owner-dashboard-shortcuts', $styles);
    }

    public function test_owner_dashboard_shows_real_student_resource_open_events(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Owner/DashboardController.php'));
        $joinController = file_get_contents(app_path('Http/Controllers/Student/LiveClassController.php'));
        $model = file_get_contents(app_path('Models/ProtectedLearningMediaView.php'));
        $partial = file_get_contents(resource_path('views/owner/dashboard/partials/activity.blade.php'));

        foreach ([$controller, $joinController, $model, $partial] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('ProtectedLearningMediaView::query()', $controller);
        $this->assertStringContainsString('student_membership.academy_id', $controller);
        $this->assertStringContainsString("where('event_type', 'open')", $controller);
        $this->assertStringContainsString("belongsTo(LearningResource::class, 'learning_resource_id')", $model);
        $this->assertStringContainsString('جزوه را باز کرد', $partial);
        $this->assertStringContainsString('recentClassJoinRequests', $partial);
        $this->assertStringContainsString('برای ورود به اتاق اقدام کرد', $partial);
        $this->assertStringContainsString('live_class_join_requested', $joinController);
        $this->assertStringContainsString('weekly_schedule_join_requested', $joinController);
    }

    public function test_student_dashboard_uses_real_weekly_activity_and_learning_data(): void
    {
        $view = file_get_contents(resource_path('views/student/dashboard.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $styles = file_get_contents(resource_path('css/student.css'));
        $service = file_get_contents(app_path('Services/StudentDashboardService.php'));

        foreach ([$view, $layout, $styles, $service] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString("student-dashboard-live", $view);
        $this->assertStringContainsString("@yield('body-class')", $layout);
        $this->assertStringContainsString('student-weekly-bars', $view);
        $this->assertStringContainsString("'studyWeek' =>", $service);
        $this->assertStringContainsString('student-dashboard-weekly', $styles);
        $this->assertStringContainsString('student-dashboard-learning-feed', $view);
        $this->assertStringNotContainsString('روند ماهانه هنوز ثبت نشده', $view);
    }

    public function test_public_home_has_an_inline_challenge_and_avoids_platform_internal_sections(): void
    {
        $view = file_get_contents(resource_path('views/pages/home.blade.php'));
        $challenge = file_get_contents(resource_path('views/pages/home/partials/free-challenge.blade.php'));
        $runtime = file_get_contents(resource_path('js/home.js'));
        $styles = file_get_contents(resource_path('css/home.css'));

        foreach ([$view, $challenge, $runtime, $styles] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('pages.home.partials.free-challenge', $view);
        $this->assertStringContainsString('data-free-challenge', $challenge);
        $this->assertStringContainsString('no form submit or page reload', $runtime);
        $this->assertStringContainsString('home-free-challenge__option', $styles);
        $this->assertStringNotContainsString('home-platform-model', $view);
        $this->assertStringNotContainsString('home-role-grid home-feature-grid', $view);
    }
}
