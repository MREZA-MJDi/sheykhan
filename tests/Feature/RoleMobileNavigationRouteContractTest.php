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

            preg_match_all('/(?<!->)\\broute\\s*\\(\\s*[\'"]([^\'"]+)[\'"]/', $source, $matches);

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



    public function test_owner_and_teacher_mobile_links_include_the_destination_get_permission(): void
    {
        $routeSource = file_get_contents(base_path('routes/web.php'));
        $mobileSource = file_get_contents(resource_path('views/components/navigation/role-mobile-nav.blade.php'));

        $this->assertIsString($routeSource);
        $this->assertIsString($mobileSource);

        $routePermissions = [];
        $prefix = null;

        foreach (explode("\n", $routeSource) as $line) {
            if (str_contains($line, "->prefix('owner')->name('owner.')")) {
                $prefix = 'owner';
                continue;
            }
            if (str_contains($line, "->prefix('teacher')->name('teacher.')")) {
                $prefix = 'teacher';
                continue;
            }

            if ($prefix && str_contains($line, 'Route::get(')
                && preg_match("/->name\\('([^']+)'\\)/", $line, $nameMatch)
                && preg_match("/permission:([a-zA-Z0-9_.-]+)/", $line, $permissionMatch)) {
                $routePermissions[$prefix . '.' . $nameMatch[1]] = $permissionMatch[1];
            }

            if ($prefix && trim($line) === '});') {
                $prefix = null;
            }
        }

        $mismatches = [];
        $menuRole = null;

        foreach (explode("\n", $mobileSource) as $line) {
            if (str_contains($line, "'teacher' => [")) {
                $menuRole = 'teacher';
            } elseif (str_contains($line, "'owner' => [")) {
                $menuRole = 'owner';
            } elseif (str_contains($line, "'parent' => [") || str_contains($line, 'default => []')) {
                $menuRole = null;
            }

            if (! $menuRole || ! preg_match("/'route'\\s*=>\\s*'([^']+)'/", $line, $routeMatch)) {
                continue;
            }

            $routeName = $routeMatch[1];
            if (! isset($routePermissions[$routeName])) {
                continue;
            }

            $declaredPermissions = [];
            if (preg_match("/'permissions'\\s*=>\\s*\\[([^\\]]*)\\]/", $line, $permissionsMatch)) {
                preg_match_all("/['\"]([a-zA-Z0-9_.-]+)['\"]/", $permissionsMatch[1], $permissionNames);
                $declaredPermissions = $permissionNames[1] ?? [];
            } elseif (preg_match("/'permission'\\s*=>\\s*'([^']+)'/", $line, $permissionMatch)) {
                $declaredPermissions = [$permissionMatch[1]];
            }

            if (! in_array($routePermissions[$routeName], $declaredPermissions, true)) {
                $mismatches[] = $routeName . ' requires ' . $routePermissions[$routeName]
                    . ' but its menu item declares [' . implode(', ', $declaredPermissions) . ']';
            }
        }

        $this->assertSame(
            [],
            $mismatches,
            'Owner/teacher mobile navigation must include each destination GET permission: ' . implode('; ', $mismatches),
        );
    }

    public function test_owner_desktop_sidebar_exposes_scoped_content_commerce_and_legal_routes(): void
    {
        $sidebar = file_get_contents(resource_path('views/components/navigation/owner-sidebar.blade.php'));
        $this->assertIsString($sidebar);

        foreach ([
            'route(\'owner.resources.index\', $academy)',
            'route(\'owner.showcase.index\', $websiteAcademy)',
            'route(\'owner.products.index\', $academy)',
            'route(\'owner.orders.index\', $academy)',
            "route('owner.legal-documents.index')",
        ] as $destination) {
            $this->assertStringContainsString($destination, $sidebar, 'Desktop owner sidebar missing ' . $destination);
        }

        foreach (["content.manage", "products.view", "orders.view", "legal.manage"] as $permission) {
            $this->assertStringContainsString("hasPermission('" . $permission . "')", $sidebar);
        }

        $this->assertStringContainsString("$seoOnly && $user->hasPermission('seo.manage')", $sidebar);
        $this->assertStringContainsString("route('owner.blog.index')", $sidebar);
        $this->assertStringContainsString("route('owner.content.index')", $sidebar);
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

        foreach ([
            'owner.resources.index',
            'owner.showcase.index',
            'owner.products.index',
            'owner.orders.index',
            'owner.legal-documents.index',
            'owner.reports.index',
            'parent.meetings.index',
        ] as $ownerRoute) {
            $this->assertContains($ownerRoute, $routes, 'The owner mobile navigation must expose ' . $ownerRoute);
        }
    }
}
