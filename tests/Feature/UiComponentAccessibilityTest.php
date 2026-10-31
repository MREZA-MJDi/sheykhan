<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiComponentAccessibilityTest extends TestCase
{
    public function test_shared_input_component_associates_its_label_with_the_generated_input_id(): void
    {
        $html = Blade::render('<x-ui.input label="ایمیل" name="email" hint="نشانی ایمیل خود را وارد کنید." />');

        $this->assertMatchesRegularExpression('/<label[^>]*for="([^"]+)"/', $html);
        preg_match('/<label[^>]*for="([^"]+)"/', $html, $matches);

        $inputId = $matches[1] ?? '';

        $this->assertNotSame('', $inputId);
        $this->assertStringContainsString('id="' . $inputId . '"', $html);
        $this->assertStringContainsString('aria-describedby="' . $inputId . '-hint"', $html);
        $this->assertStringContainsString('id="' . $inputId . '-hint"', $html);
    }

    public function test_shared_input_component_exposes_validation_error_to_assistive_technology(): void
    {
        $html = Blade::render('<x-ui.input label="ایمیل" name="email" error="نشانی ایمیل معتبر نیست." />');

        preg_match('/<label[^>]*for="([^"]+)"/', $html, $matches);
        $inputId = $matches[1] ?? '';

        $this->assertNotSame('', $inputId);
        $this->assertStringContainsString('id="' . $inputId . '"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('aria-describedby="' . $inputId . '-error"', $html);
        $this->assertStringContainsString('id="' . $inputId . '-error"', $html);
    }

    public function test_public_and_parent_layouts_own_their_flash_markup_but_share_dismiss_behavior(): void
    {
        $publicLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $parentLayout = file_get_contents(resource_path('views/layouts/parent.blade.php'));
        $publicStyles = file_get_contents(resource_path('css/app.css'));
        $parentStyles = file_get_contents(resource_path('css/parent.css'));
        $behavior = file_get_contents(resource_path('js/shared/behavior-runtime.js'));
        foreach ([$publicLayout,$parentLayout,$publicStyles,$parentStyles,$behavior] as $source) $this->assertIsString($source);

        foreach ([$publicLayout,$parentLayout] as $layout) {
            $this->assertStringContainsString('data-ui-dismissible', $layout);
            $this->assertStringContainsString('data-ui-dismiss', $layout);
            $this->assertStringNotContainsString('<x-ui.flash-messages', $layout);
        }
        $this->assertStringContainsString('bootDismissBehavior', $behavior);
        $this->assertStringContainsString('.ui-flash-stack', $publicStyles);
        $this->assertStringContainsString('.ui-flash-stack', $parentStyles);
        $this->assertFileDoesNotExist(resource_path('views/components/ui/flash-messages.blade.php'));
    }

}
