<?php

namespace Tests\Feature;

use Tests\TestCase;

class OwnerZipDesignCoverageTest extends TestCase
{
    public function test_all_26_zip_sections_use_the_shared_owner_skin(): void
    {
        $views = [
            'dashboard.blade.php',
            'reports/index.blade.php',
            'people/index.blade.php',
            'classrooms/index.blade.php',
            'attendance/index.blade.php',
            'assignments/index.blade.php',
            'schedules/index.blade.php',
            'meetings/index.blade.php',
            'messages/index.blade.php',
            'communications/index.blade.php',
            'counseling/index.blade.php',
            'exams/index.blade.php',
            'recordings/index.blade.php',
            'teacher-payments/index.blade.php',
            'resources/index.blade.php',
            'showcase/index.blade.php',
            'products/index.blade.php',
            'orders/index.blade.php',
            'courses/index.blade.php',
            'website/index.blade.php',
            'content/index.blade.php',
            'blog/index.blade.php',
            'academy/banners.blade.php',
            'legal-documents/index.blade.php',
            'finance/index.blade.php',
            'academy/form.blade.php',
        ];

        $layout = file_get_contents(resource_path('views/layouts/owner.blade.php'));
        $css = file_get_contents(resource_path('css/owner-panel.css'));
        $vite = file_get_contents(base_path('vite.config.js'));

        $this->assertCount(26, $views);
        $this->assertIsString($layout);
        $this->assertIsString($css);
        $this->assertIsString($vite);
        $this->assertStringContainsString("@vite(['resources/css/owner-panel.css'", $layout);
        $this->assertStringContainsString('body.owner-shell .app{', $css);
        $this->assertStringContainsString('body.owner-shell .main-content .owner-stat-card', $css);
        $this->assertStringContainsString('body.owner-shell .main-content .dashboard-panel', $css);
        $this->assertStringContainsString('resources/css/owner-panel.css', $vite);
        $this->assertStringNotContainsString('owner-dashboard-v2.css', $layout . $vite);
        $this->assertStringNotContainsString('seo-dashboard.css', $layout . $vite);

        foreach ($views as $view) {
            $path = resource_path('views/owner/' . $view);
            $this->assertFileExists($path, 'ZIP section Blade view is missing: ' . $view);

            $source = file_get_contents($path);
            $this->assertIsString($source);
            $this->assertStringContainsString("@extends('layouts.owner')", $source, 'ZIP section does not use the shared shell: ' . $view);
        }
    }
}
