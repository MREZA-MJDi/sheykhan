<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class SeoPanelVisualContractTest extends TestCase
{
    public function test_admin_svg_icons_have_explicit_size_and_stroke_styles(): void
    {
        $styles = file_get_contents(dirname(__DIR__, 2) . '/resources/css/admin-white-system.css');

        self::assertIsString($styles);
        self::assertStringContainsString('.owner-admin-v2 svg.ic', $styles);
        self::assertStringContainsString('width:18px', $styles);
        self::assertStringContainsString('height:18px', $styles);
        self::assertStringContainsString('stroke:currentColor', $styles);
        self::assertStringContainsString('fill:none', $styles);
    }
}
