<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelTailwindSourceCoverageTest extends TestCase
{
    public function test_student_views_are_scanned_by_the_shared_tailwind_entrypoint(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));
        $layout = file_get_contents(resource_path('views/layouts/student.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertStringContainsString("@import 'tailwindcss' source(none);", $css);
        $this->assertStringContainsString("@source '../../resources/views';", $css);
        $this->assertStringContainsString("@source '../../resources/views/components';", $css);
        $this->assertStringContainsString('resources/css/app.css', $layout);
        $this->assertStringContainsString('resources/css/student.css', $layout);
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

    public function test_role_dashboards_have_distinct_accents_and_semantic_answer_states(): void
    {
        $admin = file_get_contents(resource_path('css/admin.css'));
        $teacher = file_get_contents(resource_path('css/teacher.css'));
        $student = file_get_contents(resource_path('css/student.css'));
        $parent = file_get_contents(resource_path('css/parent.css'));
        $teacherAttempts = file_get_contents(resource_path('views/teacher/exams/attempts.blade.php'));

        foreach ([$admin, $teacher, $student, $parent, $teacherAttempts] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('.owner-shell{', $admin);
        $this->assertStringContainsString('.teacher-shell{', $teacher);
        $this->assertStringContainsString('.student-shell', $student);
        $this->assertStringContainsString('.parent-shell', $parent);
        $this->assertStringContainsString('.teacher-exam-answer-value.is-correct', $teacher);
        $this->assertStringContainsString('.teacher-exam-answer-value.is-wrong', $teacher);
        $this->assertStringContainsString('is-correct', $teacherAttempts);
        $this->assertStringContainsString('is-wrong', $teacherAttempts);
    }

    public function test_parent_hero_styles_the_real_h1_used_by_the_dashboard(): void
    {
        $css = file_get_contents(resource_path('css/parent.css'));
        $view = file_get_contents(resource_path('views/parent/dashboard.blade.php'));

        $this->assertIsString($css);
        $this->assertIsString($view);
        $this->assertStringContainsString('.parent-hero :is(h1, h2)', $css);
        $this->assertStringContainsString('<h1>', $view);
    }

    public function test_teacher_messaging_tabs_follow_their_individual_permissions(): void
    {
        $view = file_get_contents(resource_path('views/components/navigation/teacher-sidebar.blade.php'));

        $this->assertIsString($view);
        $this->assertMatchesRegularExpression(
            '/hasPermission\(\x27parent_messages\.view\x27\).*?teacher\.messages\.index/s',
            $view
        );
        $this->assertMatchesRegularExpression(
            '/hasPermission\(\x27student_messages\.view\x27\).*?teacher\.student-messages\.index/s',
            $view
        );
        $this->assertMatchesRegularExpression(
            '/hasPermission\(\x27parent_messages\.manage\x27\).*?teacher\.direct-messages\.index/s',
            $view
        );
    }

    public function test_public_catalog_styles_include_colorful_but_scoped_card_states(): void
    {
        $css = file_get_contents(resource_path('css/public-experience.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('.public-store-page .edu-card:nth-child(4n + 2)', $css);
        $this->assertStringContainsString('.public-blog-page .public-blog-card:nth-child(4n + 3)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }

}
