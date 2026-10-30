<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentTeacherDashboardDesignSystemTest extends TestCase
{
    public function test_student_and_teacher_layouts_boot_theme_and_load_their_own_assets(): void
    {
        $studentLayout = file_get_contents(resource_path('views/layouts/student.blade.php'));
        $teacherLayout = file_get_contents(resource_path('views/layouts/teacher.blade.php'));
        $themePartial = file_get_contents(resource_path('views/layouts/partials/panel-theme-init.blade.php'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        foreach ([$studentLayout, $teacherLayout, $themePartial, $viteConfig] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString("@include('layouts.partials.panel-theme-init')", $studentLayout);
        $this->assertStringContainsString("@include('layouts.partials.panel-theme-init')", $teacherLayout);
        $this->assertStringContainsString("@vite(['resources/css/student.css', 'resources/js/student.js'])", $studentLayout);
        $this->assertStringContainsString("@vite(['resources/css/teacher.css', 'resources/js/teacher.js'])", $teacherLayout);
        $this->assertStringContainsString("'resources/css/student.css'", $viteConfig);
        $this->assertStringContainsString("'resources/js/student.js'", $viteConfig);
        $this->assertStringContainsString("'resources/css/teacher.css'", $viteConfig);
        $this->assertStringContainsString("'resources/js/teacher.js'", $viteConfig);

        $this->assertStringContainsString("localStorage.getItem('sheykhan.panel.theme')", $themePartial);
        $this->assertStringContainsString("localStorage.getItem('sheykhan.student.theme')", $themePartial);
        $this->assertStringContainsString("localStorage.getItem('theme')", $themePartial);
        $this->assertStringNotContainsString('fonts.googleapis.com', $teacherLayout);
    }

    public function test_role_stylesheets_are_independent_tailwind_entries_with_responsive_tokens(): void
    {
        $studentStyles = file_get_contents(resource_path('css/student.css'));
        $teacherStyles = file_get_contents(resource_path('css/teacher.css'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        foreach ([$studentStyles, $teacherStyles, $viteConfig] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString("@import 'tailwindcss' source(none);", $studentStyles);
        $this->assertStringContainsString("@source '../../resources/views/student';", $studentStyles);
        $this->assertStringContainsString("@source '../../resources/views/layouts/student.blade.php';", $studentStyles);
        $this->assertStringContainsString("@source '../../resources/views/components';", $studentStyles);
        $this->assertStringContainsString("@import 'tailwindcss' source(none);", $teacherStyles);
        $this->assertStringContainsString("@source '../../resources/views/teacher';", $teacherStyles);
        $this->assertStringContainsString("@source '../../resources/views/layouts/teacher.blade.php';", $teacherStyles);
        $this->assertStringContainsString("@source '../../resources/views/components';", $teacherStyles);

        $this->assertStringContainsString('.student-shell', $studentStyles);
        $this->assertStringContainsString('.teacher-shell', $teacherStyles);
        $this->assertStringContainsString('.student-shell .role-mobile-bottom-nav', $studentStyles);
        $this->assertStringContainsString('.teacher-shell .role-mobile-bottom-nav', $teacherStyles);
        $this->assertStringContainsString('@media (max-width: 380px)', $studentStyles);
        $this->assertStringContainsString('@media (max-width: 760px)', $teacherStyles);
        $this->assertStringContainsString('focus-visible', $studentStyles . $teacherStyles);

        $this->assertStringNotContainsString("import './teacher.css'", $studentStyles);
        $this->assertStringNotContainsString("import './student.css'", $teacherStyles);
        $this->assertStringNotContainsString('resources/css/role-dashboard-design-system.css', $viteConfig);
    }

    public function test_avatar_fallback_behavior_is_shared_and_scoped_to_student_and_teacher(): void
    {
        $sharedScript = file_get_contents(resource_path('js/panel-base.js'));
        $studentScript = file_get_contents(resource_path('js/student.js'));
        $teacherScript = file_get_contents(resource_path('js/teacher.js'));

        $this->assertIsString($sharedScript);
        $this->assertIsString($studentScript);
        $this->assertIsString($teacherScript);

        $this->assertStringContainsString("document.body.matches('.student-shell, .teacher-shell')", $sharedScript);
        $this->assertStringContainsString("document.querySelectorAll('img[data-avatar-image]')", $sharedScript);
        $this->assertStringContainsString("image.addEventListener('error', showFallback, { once: true })", $sharedScript);
        $this->assertStringContainsString('bootRoleAvatarFallback();', $sharedScript);
    }

    public function test_dashboard_markup_and_layout_rules_remain_role_specific(): void
    {
        $studentDashboard = file_get_contents(resource_path('views/student/dashboard.blade.php'));
        $teacherDashboard = file_get_contents(resource_path('views/teacher/dashboard.blade.php'));
        $studentStyles = file_get_contents(resource_path('css/student.css'));
        $teacherStyles = file_get_contents(resource_path('css/teacher.css'));

        foreach ([$studentDashboard, $teacherDashboard, $studentStyles, $teacherStyles] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString('id="dashboard"', $studentDashboard);
        $this->assertStringContainsString('#dashboard .hero', $studentStyles);
        $this->assertStringContainsString('id="teacher-dashboard"', $teacherDashboard);
        $this->assertStringContainsString('#teacher-dashboard', $teacherStyles);
        $this->assertStringNotContainsString('style="margin-top:18px"', $studentDashboard);
    }
    public function test_role_entries_use_one_shared_panel_runtime_and_player_module(): void
    {
        $entries = [
            'resources/js/app.js',
            'resources/js/parent.js',
            'resources/js/student.js',
            'resources/js/teacher.js',
            'resources/js/owner-dashboard.js',
            'resources/js/seo-dashboard.js',
        ];
        $runtime = file_get_contents(resource_path('js/panel-runtime.js'));
        $player = file_get_contents(resource_path('js/shaka-player.js'));

        $this->assertIsString($runtime);
        $this->assertIsString($player);
        $this->assertStringContainsString("import { bootPanel, closePanelMenu } from './panel-base.js';", $runtime);
        $this->assertStringContainsString("import('./shaka-player.js')", $runtime);
        $this->assertStringContainsString('bootOwnerPeopleTabs();', $runtime);
        $this->assertStringContainsString('bootRoleMobileNavigation();', $runtime);
        $this->assertStringContainsString('export async function bootShakaPlayers()', $player);

        foreach ($entries as $entry) {
            $source = file_get_contents(resource_path(str_replace('resources/', '', $entry)));
            $this->assertIsString($source);
            $this->assertStringContainsString("from './panel-runtime.js'", $source, $entry);
            $this->assertStringNotContainsString('const initializedPlayers = new WeakMap()', $source, $entry);
            $this->assertStringNotContainsString('new shaka.Player()', $source, $entry);
        }
    }

    public function test_owner_and_seo_share_common_interactions_styles_and_layout_partials(): void
    {
        $ownerScript = file_get_contents(resource_path('js/owner-dashboard.js'));
        $seoScript = file_get_contents(resource_path('js/seo-dashboard.js'));
        $sharedScript = file_get_contents(resource_path('js/owner-admin-common.js'));
        $ownerCss = file_get_contents(resource_path('css/owner-dashboard-v2.css'));
        $seoCss = file_get_contents(resource_path('css/seo-dashboard.css'));
        $sharedCss = file_get_contents(resource_path('css/owner-admin-shared.css'));
        $ownerLayout = file_get_contents(resource_path('views/layouts/owner.blade.php'));
        $seoLayout = file_get_contents(resource_path('views/layouts/seo-specialist.blade.php'));

        foreach ([$ownerScript, $seoScript, $sharedScript, $ownerCss, $seoCss, $sharedCss, $ownerLayout, $seoLayout] as $source) {
            $this->assertIsString($source);
        }

        $this->assertStringContainsString("import './owner-admin-common.js';", $ownerScript);
        $this->assertStringContainsString("import './owner-admin-common.js';", $seoScript);
        $this->assertStringContainsString('data-media-uploader', $sharedScript);
        $this->assertStringContainsString('const initializeExamBuilder', $sharedScript);
        $this->assertStringContainsString("@import './owner-admin-shared.css';", $ownerCss);
        $this->assertStringContainsString("@import './owner-admin-shared.css';", $seoCss);

        foreach ([$ownerLayout, $seoLayout] as $layout) {
            $this->assertStringContainsString("@include('owner.partials.admin-topbar')", $layout);
            $this->assertStringContainsString("@include('owner.partials.admin-main')", $layout);
            $this->assertStringContainsString("@include('owner.partials.admin-command-palette')", $layout);
        }
    }

}
