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
            preg_match('/<nav\s+class="role-mobile-bottom-nav".*?<\/nav>/s', $html, $matches),
            'The owner mobile navigation should be rendered exactly once.'
        );

        $this->assertStringContainsString(
            'href="' . route('owner.people.index', $secondAcademy) . '"',
            $matches[0],
            'Mobile owner links should keep the academy currently being viewed.'
        );
        $this->assertStringNotContainsString(
            'href="' . route('owner.people.index', $firstAcademy) . '"',
            $matches[0],
            'Mobile owner links must not silently switch to the first academy.'
        );
    }
}
