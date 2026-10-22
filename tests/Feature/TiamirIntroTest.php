<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TiamirIntroTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_homepage_does_not_render_or_load_the_demo_tiamir_intro(): void
    {
        $this->withoutVite();
        $this->seed();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-tiamir-intro', false)
            ->assertDontSee('TIAMIR CO.')
            ->assertDontSee('RMMAJIDI DEV')
            ->assertDontSee('tiamir.com/wp-content/uploads/2023/10/Logo-3.png');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertDontSee('data-tiamir-intro', false);

        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $styles = file_get_contents(resource_path('css/home.css'));
        $scripts = file_get_contents(resource_path('js/home.js'));

        $this->assertIsString($layout);
        $this->assertIsString($styles);
        $this->assertIsString($scripts);
        $this->assertStringNotContainsString('tiamir-intro', $layout . $styles . $scripts);

        $this->assertFileDoesNotExist(resource_path('views/components/branding/tiamir-intro.blade.php'));
        $this->assertFileDoesNotExist(resource_path('css/tiamir-intro.css'));
        $this->assertFileDoesNotExist(resource_path('js/tiamir-intro.js'));
    }
}
