<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMobileNavigationRouteContractTest extends TestCase
{

    public function test_every_literal_route_reference_in_blade_views_resolves(): void
    {
        $directory = new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($directory);
        $missing = [];

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            if (! is_string($source)) {
                $this->fail('Unable to read Blade view: ' . $file->getPathname());
            }

            preg_match_all('/\\broute\\s*\\(\\s*[\'"]([^\'"]+)[\'"]/', $source, $matches);

            foreach (array_unique($matches[1] ?? []) as $name) {
                if (! Route::has($name)) {
                    $missing[] = str_replace(resource_path('views') . DIRECTORY_SEPARATOR, '', $file->getPathname())
                        . ': ' . $name;
                }
            }
        }

        $this->assertSame(
            [],
            $missing,
            'Every Blade page must reference a registered named route: ' . implode(', ', $missing),
        );
    }

    public function test_every_declared_mobile_navigation_item_points_to_a_registered_route(): void
    {
        $path = resource_path('views/components/navigation/role-mobile-nav.blade.php');
        $source = file_get_contents($path);

        $this->assertIsString($source, 'The shared role mobile navigation component must be readable.');
        preg_match_all("/'route'\\s*=>\\s*'([^']+)'/", $source, $matches);

        $routes = array_values(array_unique($matches[1] ?? []));
        $this->assertNotEmpty($routes, 'The shared mobile navigation should declare destinations.');

        $missing = array_values(array_filter($routes, fn (string $name): bool => ! Route::has($name)));

        $this->assertSame(
            [],
            $missing,
            'Mobile navigation routes must exist instead of silently disappearing from the menu: ' . implode(', ', $missing),
        );
    }
}
