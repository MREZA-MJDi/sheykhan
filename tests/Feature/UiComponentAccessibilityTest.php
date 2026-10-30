<?php

namespace Tests\\Feature;

use Illuminate\\Support\\Facades\\Blade;
use Tests\\TestCase;

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
}
