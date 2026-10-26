<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerMobileNavigationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_navigation_keeps_links_scoped_to_the_current_academy(): void
    {
        $this->seed();

        $owner = User::query()
            ->where('email', 'owner@sheykhan.test')
            ->firstOrFail();

        $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('id')
            ->firstOrFail()
            ->update(['name' => 'AAA Seeded Academy']);

        $firstAcademy = $owner->ownedAcademies()
            ->where('status', 'active')
            ->orderBy('name')
            ->firstOrFail();

        $secondAcademy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'ZZZ Mobile Navigation Academy',
            'slug' => 'mobile-nav-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('owner.people.index', $secondAcademy))
            ->assertOk();

        $this->assertNotSame($firstAcademy->id, $secondAcademy->id);

        $html = $response->getContent();
        $this->assertIsString($html);
        $this->assertSame(
            1,
            preg_match('/<aside\\s+class="side card"\\s+id="side".*?<\\/aside>/s', $html, $matches),
            'The independent owner sidebar should be rendered exactly once.'
        );

        $sidebar = $matches[0];
        $secondAcademyHref = 'href="' . route('owner.people.index', $secondAcademy) . '"';
        $firstAcademyHref = 'href="' . route('owner.people.index', $firstAcademy) . '"';

        $this->assertStringContainsString($secondAcademyHref, $sidebar);
        $this->assertMatchesRegularExpression(
            '/<a\\b[^>]*' . preg_quote($secondAcademyHref, '/') . '[^>]*aria-current="page"[^>]*>/',
            $sidebar,
            'The academy currently being viewed must be the active navigation destination.'
        );
        $this->assertStringContainsString($firstAcademyHref, $sidebar);
        $this->assertDoesNotMatchRegularExpression(
            '/<a\\b[^>]*' . preg_quote($firstAcademyHref, '/') . '[^>]*aria-current="page"[^>]*>/',
            $sidebar,
            'Other academy links must not be presented as the current page.'
        );
    }
}
