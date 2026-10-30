<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelDesignTokenContractTest extends TestCase
{
    public function test_every_role_stylesheet_imports_the_neutral_panel_token_contract(): void
    {
        $stylesheets = [
            'parent.css',
            'student.css',
            'teacher.css',
            'owner-dashboard-v2.css',
            'seo-dashboard.css',
        ];

        foreach ($stylesheets as $stylesheet) {
            $source = file_get_contents(resource_path('css/' . $stylesheet));

            $this->assertIsString($source);
            $this->assertStringContainsString("@import './panel-design-tokens.css';", $source, $stylesheet);
            $this->assertStringContainsString("@import 'tailwindcss' source(none);", $source);
        }

        $tokens = file_get_contents(resource_path('css/panel-design-tokens.css'));
        $this->assertIsString($tokens);

        foreach ([
            '--panel-font-family',
            '--panel-font-size-meta',
            '--panel-font-size-body',
            '--panel-font-size-heading',
            '--panel-control-height',
            '--panel-control-radius',
            '--panel-card-radius',
            '--panel-section-gap',
            '--panel-focus-color',
            '--panel-motion-fast',
            '--panel-motion-normal',
        ] as $token) {
            $this->assertStringContainsString($token, $tokens);
        }

        $this->assertStringNotContainsString('body {', $tokens);
        $this->assertStringNotContainsString('body{', $tokens);
        $this->assertStringNotContainsString('.role-sidebar', $tokens);
        $this->assertStringNotContainsString('background:', $tokens);
    }

    public function test_shared_ui_components_opt_into_the_same_panel_primitives(): void
    {
        $input = file_get_contents(resource_path('views/components/ui/input.blade.php'));
        $button = file_get_contents(resource_path('views/components/ui/button.blade.php'));
        $card = file_get_contents(resource_path('views/components/ui/card.blade.php'));
        $badge = file_get_contents(resource_path('views/components/ui/badge.blade.php'));

        foreach ([$input, $button, $card, $badge] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('ui-control panel-control', $input);
        $this->assertStringContainsString('panel-action', $button);
        $this->assertStringContainsString('panel-surface', $card);
        $this->assertStringContainsString('panel-badge', $badge);
    }

    public function test_role_brands_remain_owned_by_their_independent_stylesheets(): void
    {
        $parent = file_get_contents(resource_path('css/parent.css'));
        $student = file_get_contents(resource_path('css/student.css'));
        $teacher = file_get_contents(resource_path('css/teacher.css'));
        $owner = file_get_contents(resource_path('css/owner-dashboard-v2.css'));
        $seo = file_get_contents(resource_path('css/seo-dashboard.css'));

        foreach ([$parent, $student, $teacher, $owner, $seo] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('--panel-primary:', $parent);
        $this->assertStringContainsString('--panel-primary:', $student);
        $this->assertStringContainsString('--panel-primary:', $teacher);
        $this->assertStringContainsString('--acc:#22d3ee', $owner);
        $this->assertStringContainsString('--acc:#22d3ee', $seo);
        $this->assertStringContainsString('--panel-focus-color', $owner);
        $this->assertStringContainsString('--panel-focus-color', $teacher);
    }
}
