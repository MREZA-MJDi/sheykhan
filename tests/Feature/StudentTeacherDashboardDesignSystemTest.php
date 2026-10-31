<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentTeacherDashboardDesignSystemTest extends TestCase
{
    public function test_student_and_teacher_layouts_load_separate_assets_and_theme_storage(): void
    {
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $themePartial = file_get_contents(resource_path('views/layouts/partials/panel-theme-init.blade.php'));
        $teacherScript = file_get_contents(resource_path('js/teacher.js'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));
        foreach ([$studentLayout,$teacherLayout,$themePartial,$teacherScript,$viteConfig] as $source) $this->assertIsString($source);

        $this->assertStringContainsString("@include('layouts.partials.panel-theme-init')", $studentLayout);
        $this->assertStringNotContainsString("@include('layouts.partials.panel-theme-init')", $teacherLayout);
        $this->assertStringContainsString("@vite(['resources/css/student.css', 'resources/js/student.js'])", $studentLayout);
        $this->assertStringContainsString("@vite(['resources/css/teacher.css', 'resources/js/teacher.js'])", $teacherLayout);
        $this->assertStringContainsString("'resources/css/teacher.css'", $viteConfig);
        $this->assertStringContainsString("'resources/js/teacher.js'", $viteConfig);
        $this->assertStringContainsString("localStorage.getItem('sheykhan.panel.theme')", $themePartial);
        $this->assertStringContainsString("sheykhan.teacher.theme", $teacherScript);
        $this->assertStringNotContainsString("localStorage.getItem('sheykhan.panel.theme')", $teacherScript);
        $this->assertStringNotContainsString("localStorage.getItem('theme')", $teacherScript);
        $this->assertStringNotContainsString('fonts.googleapis.com', $teacherLayout);
    }

    public function test_teacher_styles_and_components_are_an_independent_responsive_tailwind_entry(): void
    {
        $css = file_get_contents(resource_path('css/teacher.css'));
        $layout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $vite = file_get_contents(base_path('vite.config.js'));
        foreach ([$css,$layout,$vite] as $source) $this->assertIsString($source);

        $this->assertStringContainsString("@import 'tailwindcss' source(none);", $css);
        $this->assertStringContainsString("@source '../../resources/views/teacher';", $css);
        $this->assertStringContainsString("@source '../../resources/views/layouts/teacher.blade.php';", $css);
        $this->assertStringContainsString('<aside class="side"', $layout);
        $this->assertStringContainsString('<nav class="bt"', $layout);
        $this->assertStringContainsString('.teacher-panel', $css);
        $this->assertStringContainsString('.weekly-calendar', $css);
        $this->assertStringContainsString('@media(max-width:760px)', $css);
        $this->assertStringContainsString('focus-visible', $css);
        $this->assertStringNotContainsString("import './panel-runtime.js'", file_get_contents(resource_path('js/teacher.js')));
        $this->assertStringNotContainsString("import './student.css'", $css);
    }

    public function test_teacher_javascript_owns_alpine_upload_calendar_and_exam_builder_runtime(): void
    {
        $script = file_get_contents(resource_path('js/teacher.js'));
        $this->assertIsString($script);
        foreach ([
            "import { bootAlpineRuntime as bootAlpine } from './shared/alpine-runtime.js';",
            "import { bootSharedBehaviors } from './shared/behavior-runtime.js';",
            "import './teacher/assessment-builder.js';",
            "import './shared/persian-calendar.js';",
            "import './shared/persian-money.js';",
            "import { bootFileUploadProgress } from './shared/upload-progress.js';",
            "import './teacher/course-media-uploader.js';",
            'bootAlpine();',
            'bootSharedBehaviors();',
        ] as $expected) {
            $this->assertStringContainsString($expected, $script);
        }
        $this->assertStringNotContainsString("from './panel-runtime.js'", $script);
        $this->assertStringNotContainsString("Alpine.start();", $script);
        $this->assertStringContainsString("import './teacher/assessment-builder.js';", $script);
        $this->assertStringNotContainsString("from './teacher/persian-calendar.js'", $script);
        $this->assertStringNotContainsString("from './teacher/persian-money.js'", $script);
        $this->assertStringNotContainsString("from './teacher/upload-progress.js'", $script);
    }

    public function test_teacher_reuses_canonical_shared_modules_instead_of_identical_private_copies(): void
    {
        foreach ([
            'js/assessment-builder.js',
            'js/persian-calendar-core.mjs',
            'js/persian-calendar.js',
            'js/persian-money.js',
            'js/upload-progress.js',
            'js/teacher/persian-calendar-core.mjs',
            'js/teacher/persian-money.js',
            'js/teacher/upload-progress.js',
            'js/shared/assessment-builder.js',
        ] as $duplicate) {
            $this->assertFileDoesNotExist(resource_path($duplicate));
        }

        $calendar = file_get_contents(resource_path('js/shared/persian-calendar.js'));
        $money = file_get_contents(resource_path('js/shared/persian-money.js'));
        $upload = file_get_contents(resource_path('js/shared/upload-progress.js'));
        $teacherBuilder = file_get_contents(resource_path('js/teacher/assessment-builder.js'));
        $ownerBuilder = file_get_contents(resource_path('js/owner/exam-builder.js'));
        foreach ([$calendar,$money,$upload,$teacherBuilder,$ownerBuilder] as $source) $this->assertIsString($source);
        $this->assertFileDoesNotExist(resource_path('js/shared/assessment-builder.js'));
    }

    public function test_shared_behavior_runtime_is_theme_neutral_and_used_by_every_panel_entry(): void
    {
        $behavior = file_get_contents(resource_path('js/shared/behavior-runtime.js'));
        $panelRuntime = file_get_contents(resource_path('js/panel-runtime.js'));
        $teacher = file_get_contents(resource_path('js/teacher.js'));
        $ownerCommon = file_get_contents(resource_path('js/owner-admin-common.js'));
        foreach ([$behavior,$panelRuntime,$teacher,$ownerCommon] as $source) $this->assertIsString($source);

        $this->assertStringContainsString('export function initializeTabs', $behavior);
        $this->assertStringContainsString('export function guardFormOnce', $behavior);
        $this->assertStringContainsString('bootConfirmationBehavior', $behavior);
        $this->assertStringContainsString('bootDismissBehavior', $behavior);
        $this->assertStringNotContainsString('.css', $behavior);
        $this->assertStringNotContainsString('resources/css/', $behavior);
        $this->assertStringContainsString('bootSharedBehaviors();', $panelRuntime);
        $this->assertStringContainsString('bootSharedBehaviors();', $teacher);
        $this->assertStringContainsString('initializeTabs(root', $panelRuntime);
        $this->assertStringContainsString('guardFormOnce(form', $ownerCommon);
    }

    public function test_teacher_dashboard_and_mobile_navigation_are_teacher_owned(): void
    {
        $dashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));
        $layout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $css = file_get_contents(resource_path('css/teacher.css'));
        foreach ([$dashboard,$layout,$css] as $source) $this->assertIsString($source);

        $this->assertStringContainsString('id="teacher-dashboard"', $dashboard);
        $this->assertStringContainsString('class="teacher-stat-card"', $dashboard);
        $this->assertStringContainsString('class="teacher-quick-action"', $dashboard);
        $this->assertStringContainsString('teacher-stat-card', $css);
        $this->assertStringContainsString('teacher-quick-action', $css);
        $this->assertStringContainsString('data-teacher-menu-open', $layout);
        $this->assertStringContainsString('data-teacher-menu-close', $layout);
        $this->assertStringNotContainsString('<x-navigation.role-mobile-nav role="teacher"', $layout);
    }

    public function test_student_layout_and_runtime_remain_unchanged_and_role_specific(): void
    {
        $studentScript = file_get_contents(resource_path('js/student.js'));
        $studentStyles = file_get_contents(resource_path('css/student.css'));
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        foreach ([$studentScript,$studentStyles,$studentLayout] as $source) $this->assertIsString($source);
        $this->assertStringContainsString("from './panel-runtime.js'", $studentScript);
        $this->assertStringContainsString('.student-shell', $studentStyles);
        $this->assertStringContainsString('<x-navigation.role-mobile-nav role="student" />', $studentLayout);
    }

    public function test_role_entries_that_are_not_teacher_continue_using_the_shared_runtime(): void
    {
        foreach ([
            'resources/js/app.js',
            'resources/js/parent.js',
            'resources/js/student.js',
            'resources/js/owner-dashboard.js',
            'resources/js/seo-dashboard.js',
        ] as $entry) {
            $source = file_get_contents(resource_path(str_replace('resources/', '', $entry)));
            $this->assertIsString($source);
            $this->assertStringContainsString("from './panel-runtime.js'", $source, $entry);
        }
    }

    public function test_spotplayer_remains_the_protected_course_player(): void
    {
        $playerView = file_get_contents(resource_path('views/student/courses/show.blade.php'));
        $package = json_decode(file_get_contents(base_path('package.json')), true);
        $this->assertIsString($playerView);
        $this->assertIsArray($package);
        $this->assertArrayNotHasKey('shaka-player', $package['dependencies'] ?? []);
        $this->assertStringContainsString("config('services.spotplayer.web_player_script')", $playerView);
        $this->assertStringContainsString('new window.SpotPlayer(', $playerView);
    }

    public function test_owner_and_seo_keep_their_own_shared_interactions_and_styles(): void
    {
        $ownerScript = file_get_contents(resource_path('js/owner-dashboard.js'));
        $seoScript = file_get_contents(resource_path('js/seo-dashboard.js'));
        $sharedScript = file_get_contents(resource_path('js/owner-admin-common.js'));
        $ownerCss = file_get_contents(resource_path('css/owner-dashboard-v2.css'));
        $seoCss = file_get_contents(resource_path('css/seo-dashboard.css'));
        $ownerLayout = file_get_contents(resource_path('views/layouts/owner.blade.php'));
        $seoLayout = file_get_contents(resource_path('views/layouts/seo-specialist.blade.php'));
        foreach ([$ownerScript,$seoScript,$sharedScript,$ownerCss,$seoCss,$ownerLayout,$seoLayout] as $source) $this->assertIsString($source);
        $this->assertStringContainsString("import './owner-admin-common.js';", $ownerScript);
        $this->assertStringContainsString("import './owner-admin-common.js';", $seoScript);
        $this->assertStringContainsString("import './shared/file-preview.js';", $sharedScript);
        $this->assertStringNotContainsString('assessment-builder', $sharedScript);
        $this->assertStringContainsString("import './owner/exam-builder.js';", $ownerScript);
        $this->assertStringContainsString("import './owner/people-tabs.js';", $ownerScript);
        $this->assertStringContainsString("import './panels/owner/admin.css';", $ownerCss);
        $this->assertStringContainsString("import './panels/seo/admin.css';", $seoCss);
        $this->assertFileDoesNotExist(resource_path('css/owner-admin-shared.css'));
        foreach ([$ownerLayout,$seoLayout] as $layout) {
            $this->assertStringContainsString("@include('owner.partials.admin-topbar')", $layout);
            $this->assertStringContainsString("@include('owner.partials.admin-main')", $layout);
        }
    }
}
