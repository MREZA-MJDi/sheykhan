<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelDesignTokenContractTest extends TestCase
{
    public function test_student_parent_owner_and_seo_keep_the_neutral_token_contract(): void
    {
        foreach (['parent.css', 'student.css', 'owner-dashboard-v2.css', 'seo-dashboard.css'] as $stylesheet) {
            $source = file_get_contents(resource_path('css/' . $stylesheet));
            $this->assertIsString($source);
            $this->assertStringContainsString("@import './panel-design-tokens.css';", $source, $stylesheet);
            $this->assertStringContainsString("@import 'tailwindcss' source(none);", $source);
        }

        $tokens = file_get_contents(resource_path('css/panel-design-tokens.css'));
        $this->assertIsString($tokens);
        foreach (['--panel-font-family','--panel-font-size-meta','--panel-font-size-body','--panel-font-size-heading','--panel-control-height','--panel-control-radius','--panel-card-radius','--panel-section-gap','--panel-focus-color','--panel-motion-fast','--panel-motion-normal'] as $token) {
            $this->assertStringContainsString($token, $tokens);
        }
        $this->assertStringNotContainsString('body {', $tokens);
        $this->assertStringNotContainsString('body{', $tokens);
        $this->assertStringNotContainsString('.role-sidebar', $tokens);
        $this->assertStringNotContainsString('background:', $tokens);
    }

    public function test_shared_ui_components_remain_role_neutral(): void
    {
        $input = file_get_contents(resource_path('views/components/ui/input.blade.php'));
        $button = file_get_contents(resource_path('views/components/ui/button.blade.php'));
        $card = file_get_contents(resource_path('views/components/ui/card.blade.php'));
        $badge = file_get_contents(resource_path('views/components/ui/badge.blade.php'));
        foreach ([$input,$button,$card,$badge] as $source) $this->assertIsString($source);
        $this->assertStringContainsString('ui-control panel-control', $input);
        $this->assertStringContainsString('panel-action', $button);
        $this->assertStringContainsString('panel-surface', $card);
        $this->assertStringContainsString('panel-badge', $badge);
    }

    public function test_teacher_brand_is_fully_owned_by_teacher_stylesheet(): void
    {
        $teacher = file_get_contents(resource_path('css/teacher.css'));
        $this->assertIsString($teacher);
        $this->assertStringContainsString("--bg:#e6e9f7", $teacher);
        $this->assertStringContainsString("--pri:#4a5fe0", $teacher);
        $this->assertStringContainsString('.teacher-panel', $teacher);
        $this->assertStringContainsString('.weekly-calendar', $teacher);
        $this->assertStringContainsString('.teacher-stat-card', $teacher);
        $this->assertStringNotContainsString("@import './panel-design-tokens.css';", $teacher);
        $this->assertStringNotContainsString('resources/css/student.css', $teacher);
        $this->assertStringNotContainsString('resources/css/owner-dashboard-v2.css', $teacher);
    }

    public function test_other_role_brands_remain_owned_by_their_independent_stylesheets(): void
    {
        $parent = file_get_contents(resource_path('css/parent.css'));
        $student = file_get_contents(resource_path('css/student.css'));
        $owner = file_get_contents(resource_path('css/owner-dashboard-v2.css'));
        $seo = file_get_contents(resource_path('css/seo-dashboard.css'));
        foreach ([$parent,$student,$owner,$seo] as $source) $this->assertIsString($source);
        $this->assertStringContainsString('--panel-primary:', $parent);
        $this->assertStringContainsString('--panel-primary:', $student);
        $this->assertStringContainsString('--acc:#22d3ee', $owner);
        $this->assertStringContainsString('--acc:#22d3ee', $seo);
    }
}
