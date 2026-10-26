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

    public function test_communication_styles_live_in_the_consolidated_role_bundles(): void
    {
        foreach ([
            'resources/css/teacher.css',
            'resources/css/parent.css',
            'resources/css/owner-dashboard-v2.css',
        ] as $stylesheet) {
            $source = $this->source($stylesheet);
            $this->assertStringContainsString('communication-page__hero', $source, $stylesheet);
            $this->assertStringContainsString('communication-page__message', $source, $stylesheet);
            $this->assertStringContainsString('communication-page__empty', $source, $stylesheet);
            $this->assertStringNotContainsString("@import './communication-pages.css';", $source, $stylesheet);
        }
    }

    public function test_all_message_screens_have_explicit_style_hooks(): void
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

    public function test_consolidated_bundles_cover_message_and_form_states(): void
    {
        $source = implode("\n", [
            $this->source('resources/css/teacher.css'),
            $this->source('resources/css/parent.css'),
            $this->source('resources/css/owner-dashboard-v2.css'),
        ]);

        foreach ([
            '.communication-page__hero',
            '.communication-page__stats',
            '.communication-page__message',
            '.communication-page__body',
            '.communication-page__response',
            '.communication-page__form--two-column',
            '.communication-page__empty',
        ] as $selector) {
            $this->assertStringContainsString($selector, $source, $selector);
        }

        $this->assertMatchesRegularExpression('/@media\s*\(max-width:\s*760px\)/', $source);
    }
}
