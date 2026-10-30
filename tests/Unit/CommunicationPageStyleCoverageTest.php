<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CommunicationPageStyleCoverageTest extends TestCase
{
    private function source(string $relativePath): string
    {
        $path = dirname(__DIR__, 2) . '/' . $relativePath;
        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }

    public function test_each_role_stylesheet_defines_communication_workspace_hooks(): void
    {
        $styleSheets = [
            'resources/css/teacher.css',
            'resources/css/parent.css',
            'resources/css/owner-dashboard-v2.css',
        ];

        foreach ($styleSheets as $styleSheet) {
            $source = $this->source($styleSheet);
            $this->assertStringContainsString('.communication-page__hero', $source, $styleSheet);
            $this->assertStringContainsString('.communication-page__message', $source, $styleSheet);
            $this->assertStringContainsString('.communication-page__empty', $source, $styleSheet);
            $this->assertStringContainsString('.communication-page__form--two-column', $source, $styleSheet);
        }
    }

    public function test_all_message_screens_have_explicit_shared_style_hooks(): void
    {
        foreach ([
            'resources/views/teacher/student-messages/index.blade.php',
            'resources/views/teacher/direct-messages/index.blade.php',
            'resources/views/teacher/messages/index.blade.php',
            'resources/views/parent/messages/index.blade.php',
            'resources/views/owner/messages/index.blade.php',
        ] as $view) {
            $source = $this->source($view);
            $this->assertStringContainsString('communication-page', $source, $view);
            $this->assertStringContainsString('communication-page__hero', $source, $view);
        }

        $inbox = $this->source('resources/views/components/communication/teacher-direct-messages-inbox.blade.php');
        $this->assertStringContainsString('communication-page__message', $inbox);
        $this->assertStringContainsString('communication-page__response', $inbox);
    }

    public function test_message_styles_cover_forms_empty_states_and_mobile_layouts(): void
    {
        foreach ([
            'resources/css/teacher.css',
            'resources/css/parent.css',
            'resources/css/owner-dashboard-v2.css',
        ] as $styleSheet) {
            $source = $this->source($styleSheet);
            $this->assertStringContainsString('.communication-page__hero', $source, $styleSheet);
            $this->assertStringContainsString('.communication-page__response', $source, $styleSheet);
            $this->assertStringContainsString('@media', $source, $styleSheet);
            $this->assertStringContainsString('focus-visible', $source, $styleSheet);
        }
    }
}
