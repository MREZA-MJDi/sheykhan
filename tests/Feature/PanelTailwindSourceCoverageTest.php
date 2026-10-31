<?php

namespace Tests\Feature;

use Tests\TestCase;

class PanelTailwindSourceCoverageTest extends TestCase
{
    public function test_student_views_are_scanned_by_the_student_tailwind_entrypoint(): void
    {
        $css = file_get_contents(resource_path('css/student.css'));
        $layout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertStringContainsString("@import 'tailwindcss' source(none);", $css);
        $this->assertStringContainsString("@source '../../resources/views/student';", $css);
        $this->assertStringContainsString("@source '../../resources/views/layouts/student.blade.php';", $css);
        $this->assertStringContainsString("@source '../../resources/views/components';", $css);
        $this->assertStringContainsString('resources/css/student.css', $layout);
        $this->assertStringContainsString('resources/js/student.js', $layout);
    }

    public function test_parent_styles_scan_parent_views_layout_and_shared_components(): void
    {
        $css = file_get_contents(resource_path('css/parent.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString("@source '../../resources/views/parent';", $css);
        $this->assertStringContainsString("@source '../../resources/views/layouts/parent.blade.php';", $css);
        $this->assertStringContainsString("@source '../../resources/views/components';", $css);
    }

    public function test_teacher_views_scan_only_teacher_owned_views_layout_and_components(): void
    {
        $css = file_get_contents(resource_path('css/teacher.css'));
        $layout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $this->assertIsString($css);
        $this->assertIsString($layout);
        $this->assertStringContainsString("@source '../../resources/views/teacher';", $css);
        $this->assertStringContainsString("@source '../../resources/views/layouts/teacher.blade.php';", $css);
        $this->assertStringContainsString("@source '../../resources/views/components/teacher';", $css);
        $this->assertStringNotContainsString("@source '../../resources/views/components';", $css);
        $this->assertStringContainsString('<x-teacher.course-management-form', file_get_contents(resource_path('views/teacher/courses/form.blade.php')));
        $this->assertStringContainsString('<x-teacher.weekly-schedule-calendar', file_get_contents(resource_path('views/teacher/schedule/index.blade.php')));
        $this->assertStringContainsString('teacher.partials.academy-messages-workspace', file_get_contents(resource_path('views/teacher/communications/index.blade.php')));
    }

    public function test_shared_teacher_inbox_remains_neutral_for_other_consumers(): void
    {
        $view = file_get_contents(resource_path('views/components/communication/teacher-direct-messages-inbox.blade.php'));
        $this->assertIsString($view);
        $this->assertStringContainsString('rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7', $view);
        $this->assertStringNotContainsString('parent-panel', $view);
    }

    public function test_teacher_uses_its_own_design_for_exams_and_weekly_schedule(): void
    {
        $teacher = file_get_contents(resource_path('css/teacher.css'));
        $attempts = file_get_contents(resource_path('views/teacher/exams/attempts.blade.php'));
        foreach ([$teacher,$attempts] as $source) $this->assertIsString($source);
        $this->assertStringContainsString('.teacher-exam-answer-value.is-correct', $teacher);
        $this->assertStringContainsString('.teacher-exam-answer-value.is-wrong', $teacher);
        $this->assertStringContainsString('.weekly-calendar', $teacher);
        $this->assertStringContainsString('is-correct', $attempts);
        $this->assertStringContainsString('is-wrong', $attempts);
    }

    public function test_teacher_navigation_declares_the_actual_destination_permissions(): void
    {
        $view = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $this->assertIsString($view);
        $this->assertStringContainsString("'route'=>'teacher.messages.index','permission'=>'parent_messages.view'", $view);
        $this->assertStringContainsString("'route'=>'teacher.student-messages.index','permission'=>'student_messages.view'", $view);
        $this->assertStringContainsString("'route'=>'teacher.direct-messages.index','permission'=>'parent_messages.manage'", $view);
        $this->assertStringContainsString("'route'=>'teacher.communications.index','permission'=>'parent_messages.manage'", $view);
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

    public function test_public_catalog_styles_include_colorful_but_scoped_card_states(): void
    {
        $css = file_get_contents(resource_path('css/public-experience.css'));
        $this->assertIsString($css);
        $this->assertStringContainsString('.public-store-page .edu-card:nth-child(4n + 2)', $css);
        $this->assertStringContainsString('.public-blog-page .public-blog-card:nth-child(4n + 3)', $css);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $css);
    }
}
