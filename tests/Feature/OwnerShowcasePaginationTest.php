<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Academy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerShowcasePaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_seo_showcase_lists_are_paginated_without_hiding_older_records(): void
    {
        [$owner, $academy] = $this->workspace();

        foreach (range(1, 31) as $number) {
            Achievement::query()->create([
                'academy_id' => $academy->id,
                'display_name' => 'افتخارآفرین ' . $number,
                'achievement_type' => 'gifted_school',
                'school_name' => 'مدرسه ' . $number,
                'title' => 'عنوان ' . $number,
                'status' => 'draft',
            ]);

            Testimonial::query()->create([
                'academy_id' => $academy->id,
                'display_name' => 'والد ' . $number,
                'role' => 'parent',
                'content_text' => 'تجربه شماره ' . $number,
                'status' => 'pending',
            ]);
        }

        $this->actingAs($owner)
            ->get(route('owner.showcase.index', $academy))
            ->assertOk()
            ->assertViewHas('achievements', fn ($items) => $items instanceof LengthAwarePaginator
                && $items->total() === 31
                && $items->count() === 15
                && $items->currentPage() === 1)
            ->assertViewHas('testimonials', fn ($items) => $items instanceof LengthAwarePaginator
                && $items->total() === 31
                && $items->count() === 15
                && $items->currentPage() === 1);

        $this->actingAs($owner)
            ->get(route('owner.showcase.index', $academy) . '?achievements_page=3')
            ->assertOk()
            ->assertViewHas('achievements', fn ($items) => $items instanceof LengthAwarePaginator
                && $items->total() === 31
                && $items->count() === 1
                && $items->currentPage() === 3)
            ->assertViewHas('testimonials', fn ($items) => $items instanceof LengthAwarePaginator
                && $items->total() === 31
                && $items->count() === 15
                && $items->currentPage() === 1);
    }

    private function workspace(): array
    {
        $owner = User::factory()->create([
            'email' => 'showcase-owner-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);

        $role = Role::query()->create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'seo-specialist',
            'description' => 'Owner showcase test',
        ]);

        $permission = Permission::query()->create([
            'name' => 'seo.manage',
            'label' => 'مدیریت SEO',
            'group' => 'seo',
        ]);

        $role->permissions()->sync([$permission->id]);
        $owner->roles()->attach($role->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'showcase-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        return [$owner, $academy];
    }
}
