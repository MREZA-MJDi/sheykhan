<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelDesignTokenContractTest extends TestCase
{
    public function test_each_role_owns_its_calendar_and_presentation_styles(): void
    {
        $entries = [
            'parent' => 'parent.css',
            'student' => 'student.css',
            'owner' => 'owner-dashboard-v2.css',
            'seo' => 'seo-dashboard.css',
        ];

        foreach ($entries as $role => $entry) {
            $source = file_get_contents(resource_path('css/' . $entry));
            $calendar = file_get_contents(resource_path('css/panels/' . $role . '/calendar.css'));
            $components = file_get_contents(resource_path('css/panels/' . $role . '/components.css'));

            foreach ([$source, $calendar, $components] as $css) {
                $this->assertIsString($css);
            }

            $this->assertStringContainsString("@import './panels/{$role}/calendar.css';", $source, $role);
            $this->assertStringContainsString("@import './panels/{$role}/components.css';", $source, $role);
            $this->assertStringContainsString('.jalali-calendar-popup', $calendar, $role);
            $this->assertStringContainsString('--panel-control-height', $components, $role);
            $this->assertStringNotContainsString("@import './panel-design-tokens.css';", $source, $role);
            $this->assertStringNotContainsString("@import './persian-calendar.css';", $source, $role);
        }
    }

    public function test_public_site_owns_a_separate_calendar_stylesheet(): void
    {
        $source = file_get_contents(resource_path('css/app.css'));
        $calendar = file_get_contents(resource_path('css/public/calendar.css'));

        $this->assertIsString($source);
        $this->assertIsString($calendar);
        $this->assertStringContainsString("@import './public/calendar.css';", $source);
        $this->assertStringContainsString('.jalali-calendar-popup', $calendar);
    }

    public function test_teacher_brand_and_calendar_presentation_are_teacher_owned(): void
    {
        $teacher = file_get_contents(resource_path('css/teacher.css'));
        $this->assertIsString($teacher);
        $this->assertStringContainsString('--bg:#e6e9f7', $teacher);
        $this->assertStringContainsString('--pri:#4a5fe0', $teacher);
        $this->assertStringContainsString('.teacher-panel', $teacher);
        $this->assertStringContainsString('.weekly-calendar', $teacher);
        $this->assertStringContainsString('.teacher-stat-card', $teacher);
        $this->assertStringNotContainsString("@import './panel-design-tokens.css';", $teacher);
        $this->assertStringNotContainsString('resources/css/student.css', $teacher);
        $this->assertStringNotContainsString('resources/css/owner-dashboard-v2.css', $teacher);
    }

    public function test_calendar_and_money_runtime_are_behavior_only_and_canonical(): void
    {
        $calendar = file_get_contents(resource_path('js/shared/persian-calendar.js'));
        $calendarCore = file_get_contents(resource_path('js/shared/persian-calendar-core.mjs'));
        $money = file_get_contents(resource_path('js/shared/persian-money.js'));
        foreach ([$calendar, $calendarCore, $money] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringNotContainsString('resources/css/', $calendar);
        $this->assertStringNotContainsString('resources/css/', $money);
        $this->assertFileDoesNotExist(resource_path('css/panel-design-tokens.css'));
        $this->assertFileDoesNotExist(resource_path('css/persian-calendar.css'));
    }
}
