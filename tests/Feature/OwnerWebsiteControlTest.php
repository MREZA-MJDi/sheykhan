<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\HomeBanner;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerWebsiteControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_academy_owner_can_open_website_control_center(): void
    {
        [$owner, $academy] = $this->workspace();

        $this->actingAs($owner)
            ->get(route('owner.website.index'))
            ->assertOk()
            ->assertViewIs('owner.website.index')
            ->assertViewHas('academies', fn ($academies) => $academies->contains($academy));
    }

    public function test_academy_owner_can_update_non_destructive_banner_crop_position(): void
    {
        [$owner, $academy] = $this->workspace();

        $media = Media::create([
            'uploaded_by' => $owner->id,
            'disk' => 'local',
            'path' => 'academies/' . $academy->id . '/home-banners/test.webp',
            'original_name' => 'test.webp',
            'file_name' => 'test.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 1000,
            'checksum' => hash('sha256', 'test'),
            'visibility' => 'public',
            'collection' => 'home-banners',
            'metadata' => [],
            'status' => 'active',
        ]);

        $academy->media()->attach($media->id, [
            'collection' => 'home-banners',
            'sort_order' => 0,
            'is_featured' => true,
        ]);

        $banner = HomeBanner::create([
            'academy_id' => $academy->id,
            'media_id' => $media->id,
            'slot' => 1,
            'title' => 'بنر تست',
            'sort_order' => 1,
            'crop_x' => 50,
            'crop_y' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.academy.banners.update', $academy), [
                'banners' => [
                    1 => [
                        'media_id' => $media->id,
                        'title' => 'بنر تست',
                        'crop_x' => 18,
                        'crop_y' => 72,
                        'is_active' => true,
                    ],
                ],
            ])
            ->assertRedirect();

        $banner->refresh();

        $this->assertSame(18, $banner->crop_x);
        $this->assertSame(72, $banner->crop_y);
        $this->assertSame($media->id, $banner->media_id);
    }

    public function test_failed_banner_update_removes_uploaded_files_after_database_rollback(): void
    {
        [$owner, $academy] = $this->workspace();
        $otherUser = User::factory()->create();

        $unattachedMedia = Media::create([
            'uploaded_by' => $otherUser->id,
            'disk' => 'local',
            'path' => 'other/banner.webp',
            'original_name' => 'banner.webp',
            'file_name' => 'banner.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 1000,
            'checksum' => hash('sha256', 'foreign-banner'),
            'visibility' => 'public',
            'collection' => 'home-banners',
            'metadata' => [],
            'status' => 'active',
        ]);

        Storage::fake('local');

        $this->actingAs($owner)
            ->patch(route('owner.academy.banners.update', $academy), [
                'banners' => [
                    1 => [
                        'image' => UploadedFile::fake()->image('new-banner.jpg', 800, 400),
                        'title' => 'بنر جدید',
                        'is_active' => true,
                    ],
                    2 => [
                        'media_id' => $unattachedMedia->id,
                        'title' => 'رسانه متعلق به آموزشگاه دیگر',
                        'is_active' => true,
                    ],
                ],
            ])
            ->assertStatus(422);

        $this->assertSame(
            [],
            Storage::disk('local')->allFiles('academies/' . $academy->id . '/home-banners')
        );
        $this->assertDatabaseMissing('home_banners', [
            'academy_id' => $academy->id,
            'slot' => 1,
        ]);
        $this->assertDatabaseCount('media', 1);
        $this->assertDatabaseCount('media_attachments', 0);
    }

    private function workspace(): array
    {
        $owner = User::factory()->create([
            'email' => 'owner-' . Str::random(8) . '@test.local',
        ]);

        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permissions = collect([
            'academy.view',
            'academy.manage',
        ])->map(fn ($name) => Permission::create([
            'name' => $name,
            'label' => $name,
            'group' => 'owner',
        ]));

        $role->permissions()->sync($permissions->pluck('id'));
        $owner->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::random(8),
            'status' => 'active',
        ]);

        return [$owner, $academy];
    }
}
