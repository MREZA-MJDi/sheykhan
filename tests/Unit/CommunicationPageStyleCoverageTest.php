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

    public function test_every_role_bundle_imports_the_shared_communication_stylesheet(): void
    {
        foreach ([
            'resources/css/teacher.css',
            'resources/css/parent.css',
            'resources/css/owner.css',
        ] as $stylesheet) {
            $this->assertStringContainsString("@import './communication-pages.css';", $this->source($stylesheet), $stylesheet);
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

    public function test_shared_sheet_defines_the_core_message_and_form_styles(): void
    {
        $source = $this->source('resources/css/communication-pages.css');

        foreach ([
            '.communication-page__hero',
            '.communication-page__stats',
            '.communication-page__message',
            '.communication-page__body',
            '.communication-page__response',
            '.communication-page__form--two-column',
            '.communication-page__empty',
            '@media (max-width: 760px)',
        ] as $selector) {
            $this->assertStringContainsString($selector, $source, $selector);
        }
    }
}
