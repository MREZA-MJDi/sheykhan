<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelTailwindSourceCoverageTest extends TestCase
{
    public function test_student_styles_scan_student_views_layout_and_shared_components(): void
    {
        $css = file_get_contents(resource_path('css/student.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString("@source '../../resources/views/student';", $css);
        $this->assertStringContainsString("@source '../../resources/views/layouts/student.blade.php';", $css);
        $this->assertStringContainsString("@source '../../resources/views/components';", $css);
    }

    public function test_parent_styles_scan_parent_views_layout_and_shared_components(): void
    {
        $css = file_get_contents(resource_path('css/parent.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString("@source '../../resources/views/parent';", $css);
        $this->assertStringContainsString("@source '../../resources/views/layouts/parent.blade.php';", $css);
        $this->assertStringContainsString("@source '../../resources/views/components';", $css);
    }

    public function test_shared_teacher_inbox_uses_role_neutral_panel_styles(): void
    {
        $view = file_get_contents(resource_path('views/components/communication/teacher-direct-messages-inbox.blade.php'));

        $this->assertIsString($view);
        $this->assertStringContainsString('rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7', $view);
        $this->assertStringNotContainsString('parent-panel', $view);
    }
}
