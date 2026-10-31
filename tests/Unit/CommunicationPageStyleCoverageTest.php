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

    public function test_teacher_communication_presentation_is_owned_by_teacher_stylesheet(): void
    {
        $css = $this->source('resources/css/teacher.css');
        $this->assertStringContainsString('.communication-page__form', $css);
        $this->assertStringContainsString('.communication-page__response', $css);
        $this->assertStringContainsString('.communication-page__button', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
    }

    public function test_parent_communication_presentation_is_owned_by_parent_stylesheet_and_partial(): void
    {
        $css = $this->source('resources/css/parent.css');
        $page = $this->source('resources/views/parent/messages/index.blade.php');
        $inbox = $this->source('resources/views/parent/messages/partials/direct-message-inbox.blade.php');

        $this->assertStringContainsString('.communication-page__hero', $css);
        $this->assertStringContainsString('.communication-page__empty', $css);
        $this->assertStringContainsString('.communication-page__form--two-column', $css);
        $this->assertStringContainsString('parent.messages.partials.direct-message-inbox', $page);
        $this->assertStringContainsString('parent-panel', $inbox);
        $this->assertStringContainsString('communication-page__response', $inbox);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/resources/views/components/communication/teacher-direct-messages-inbox.blade.php');
    }

    public function test_owner_communication_presentation_is_owned_by_owner_stylesheet_and_partial(): void
    {
        $css = $this->source('resources/css/owner-dashboard-v2.css');
        $page = $this->source('resources/views/owner/communications/index.blade.php');
        $workspace = $this->source('resources/views/owner/communications/partials/messages-workspace.blade.php');

        $this->assertStringContainsString('.owner-admin-v2 .main-content .communication-page__hero', $css);
        $this->assertStringContainsString('.owner-admin-v2 .main-content .communication-page__empty', $css);
        $this->assertStringContainsString('owner.communications.partials.messages-workspace', $page);
        $this->assertStringContainsString('owner-communications-workspace', $workspace);
        $this->assertStringContainsString('dashboard-panel', $workspace);
        $this->assertFileDoesNotExist(dirname(__DIR__, 2) . '/resources/views/components/communication/academy-messages-workspace.blade.php');
    }
}
