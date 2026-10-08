<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyContent;
use App\Models\AcademyContentCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_publish_owned_academy_content(): void
    {
        [$owner, $academy] = $this->ownerWithAcademy('content.manage');

        $category = AcademyContentCategory::create([
            'academy_id' => $academy->id,
            'title' => 'مسیر والدین',
            'slug' => 'parents',
            'is_active' => true,
        ]);

        $response = $this->actingAs($owner)->post(route('owner.content.store'), [
            'academy_id' => $academy->id,
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'راهنمای والدین',
            'slug' => 'parents-guide',
            'excerpt' => 'یک راهنمای کاربردی',
            'body' => 'متن راهنمای والدین',
            'status' => 'published',
        ]);

        $content = AcademyContent::query()->where('academy_id', $academy->id)->firstOrFail();

        $response->assertRedirect(route('owner.content.edit', $content));

        $this->assertDatabaseHas('academy_contents', [
            'id' => $content->id,
            'academy_id' => $academy->id,
            'slug' => 'parents-guide',
            'status' => 'published',
        ]);
    }

    public function test_owner_cannot_edit_content_belonging_to_another_academy(): void
    {
        [$ownerA, $academyA] = $this->ownerWithAcademy('content.manage');
        [, $academyB] = $this->ownerWithAcademy();

        $category = AcademyContentCategory::create([
            'academy_id' => $academyB->id,
            'title' => 'گروه B',
            'slug' => 'group-b',
            'is_active' => true,
        ]);

        $content = AcademyContent::create([
            'academy_id' => $academyB->id,
            'category_id' => $category->id,
            'type' => 'article',
            'title' => 'محتوای B',
            'slug' => 'content-b-' . Str::random(5),
            'status' => 'published',
        ]);

        $this->actingAs($ownerA)
            ->get(route('owner.content.edit', $content))
            ->assertForbidden();
    }

    private function ownerWithAcademy(?string $permission = null): array
    {
        $role = Role::firstOrCreate(
            ['slug' => 'academy-owner'],
            ['name' => 'مدیر آموزشگاه', 'description' => 'Owner test']
        );

        if ($permission) {
            $perm = Permission::firstOrCreate(
                ['name' => $permission],
                ['label' => $permission, 'group' => 'owner']
            );
            $role->permissions()->attach($perm->id);
        }

        $owner = User::factory()->create();
        $owner->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::random(7),
            'status' => 'active',
        ]);

        return [$owner, $academy];
    }
}
