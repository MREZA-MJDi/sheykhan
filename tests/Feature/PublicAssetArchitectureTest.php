<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicAssetArchitectureTest extends TestCase
{
    public function test_homepage_animation_and_styles_are_route_scoped(): void
    {
        $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $authLayout = file_get_contents(resource_path('views/layouts/auth.blade.php'));
        $homeStyles = file_get_contents(resource_path('css/home.css'));
        $publicStyles = file_get_contents(resource_path('css/public.css'));
        $homeScript = file_get_contents(resource_path('js/home.js'));
        $viteConfig = file_get_contents(base_path('vite.config.js'));

        $this->assertIsString($appLayout);
        $this->assertIsString($authLayout);
        $this->assertIsString($homeStyles);
        $this->assertIsString($publicStyles);
        $this->assertIsString($homeScript);
        $this->assertIsString($viteConfig);

        $this->assertStringContainsString("resources/css/public.css", $appLayout);
        $this->assertStringContainsString("resources/js/public.js", $appLayout);
        $this->assertStringContainsString("request()->routeIs('home')", $appLayout);
        $this->assertStringContainsString("resources/css/tiamir-intro.css", $appLayout);
        $this->assertStringContainsString("resources/js/tiamir-intro.js", $appLayout);
        $this->assertStringNotContainsString("resources/css/home.css", $authLayout);
        $this->assertStringNotContainsString("resources/js/home.js", $authLayout);
        $this->assertStringNotContainsString("tiamir-intro.js", $homeScript);
        $this->assertStringNotContainsString("public-experience.css", $homeStyles);
        $this->assertStringContainsString("resources/js/public.js", $viteConfig);
    }

    public function test_variable_vazirmatn_font_is_shared_instead_of_six_static_files(): void
    {
        $sharedStyles = file_get_contents(resource_path('css/components.css'));
        $panelBase = file_get_contents(resource_path('css/panel-base.css'));
        $homeStyles = file_get_contents(resource_path('css/home.css'));

        $this->assertIsString($sharedStyles);
        $this->assertIsString($panelBase);
        $this->assertIsString($homeStyles);

        $this->assertSame(1, substr_count($sharedStyles, '@font-face'));
        $this->assertStringContainsString('Vazirmatn[wght].woff2', $sharedStyles);
        $this->assertStringContainsString('font-weight:100 900', str_replace(' ', '', $sharedStyles));
        $this->assertStringNotContainsString('@font-face', $panelBase);
        $this->assertStringNotContainsString('@font-face', $homeStyles);
    }

    public function test_each_role_bundle_scans_only_its_role_views_and_shared_components(): void
    {
        $panelBase = file_get_contents(resource_path('css/panel-base.css'));
        $owner = file_get_contents(resource_path('css/owner.css'));
        $teacher = file_get_contents(resource_path('css/teacher.css'));
        $student = file_get_contents(resource_path('css/student.css'));
        $parent = file_get_contents(resource_path('css/parent.css'));

        $this->assertIsString($panelBase);
        $this->assertStringNotContainsString("@source '../../resources/views'", $panelBase);
        $this->assertStringContainsString("@source '../views/owner'", $owner);
        $this->assertStringContainsString("@source '../views/teacher'", $teacher);
        $this->assertStringContainsString("@source '../views/student'", $student);
        $this->assertStringContainsString("@source '../views/parent'", $parent);
    }
}
