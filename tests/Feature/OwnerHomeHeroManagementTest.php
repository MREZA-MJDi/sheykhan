<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\HomeHeroSetting;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerHomeHeroManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_upload_desktop_and_mobile_hero_images_and_public_home_uses_them(): void
    {
        Storage::fake('local');
        Cache::flush();
        [$owner] = $this->workspace();

        $this->actingAs($owner)
            ->get(route('owner.website.hero.edit'))
            ->assertOk()
            ->assertViewIs('owner.website.hero')
            ->assertSee('data-home-hero-editor', false)
            ->assertSee('data-home-hero-preview-desktop', false)
            ->assertSee('data-home-hero-preview-mobile', false);

        $this->actingAs($owner)
            ->patch(route('owner.website.hero.update'), [
                'image' => UploadedFile::fake()->image('desktop-hero.jpg', 1920, 1080),
                'mobile_image' => UploadedFile::fake()->image('mobile-hero.jpg', 900, 1200),
                'crop_x' => 24,
                'crop_y' => 68,
                'mobile_crop_x' => 72,
                'mobile_crop_y' => 35,
            ])
            ->assertRedirect(route('owner.website.hero.edit'));

        $hero = HomeHeroSetting::query()->with(['media', 'mobileMedia'])->findOrFail(1);

        $this->assertSame(24, $hero->crop_x);
        $this->assertSame(68, $hero->crop_y);
        $this->assertSame(72, $hero->mobile_crop_x);
        $this->assertSame(35, $hero->mobile_crop_y);
        $this->assertSame('home-hero', $hero->media->collection);
        $this->assertSame('home-hero', $hero->mobileMedia->collection);
        Storage::disk('local')->assertExists($hero->media->path);
        Storage::disk('local')->assertExists($hero->mobileMedia->path);

        Cache::flush();
        $response = $this->get(route('home'))->assertOk();
        $response->assertSee(route('media.public', $hero->media), false)
            ->assertSee(route('media.public', $hero->mobileMedia), false)
            ->assertSee('data-home-hero-mobile-source', false)
            ->assertSee('--hero-crop-x: 24%', false)
            ->assertSee('--hero-mobile-crop-y: 35%', false);
    }

    public function test_replacing_or_restoring_default_cleans_up_only_old_hero_uploads(): void
    {
        Storage::fake('local');
        [$owner] = $this->workspace();

        $this->actingAs($owner)->patch(route('owner.website.hero.update'), [
            'image' => UploadedFile::fake()->image('first-hero.jpg', 1600, 900),
            'mobile_image' => UploadedFile::fake()->image('first-mobile.jpg', 800, 1000),
            'crop_x' => 50,
            'crop_y' => 46,
            'mobile_crop_x' => 54,
            'mobile_crop_y' => 50,
        ])->assertRedirect();

        $oldHero = HomeHeroSetting::query()->with(['media', 'mobileMedia'])->findOrFail(1);
        $oldDesktopPath = $oldHero->media->path;
        $oldMobilePath = $oldHero->mobileMedia->path;

        $this->actingAs($owner)->patch(route('owner.website.hero.update'), [
            'image' => UploadedFile::fake()->image('replacement.jpg', 1600, 900),
            'remove_mobile_image' => true,
            'crop_x' => 50,
            'crop_y' => 46,
            'mobile_crop_x' => 54,
            'mobile_crop_y' => 50,
        ])->assertRedirect();

        $hero = HomeHeroSetting::query()->with(['media', 'mobileMedia'])->findOrFail(1);
        $this->assertNotSame($oldHero->media_id, $hero->media_id);
        $this->assertNull($hero->mobile_media_id);
        Storage::disk('local')->assertMissing($oldDesktopPath);
        Storage::disk('local')->assertMissing($oldMobilePath);
        $this->assertDatabaseMissing('media', ['path' => $oldDesktopPath]);
        $this->assertDatabaseMissing('media', ['path' => $oldMobilePath]);

        $replacementPath = $hero->media->path;
        $this->actingAs($owner)->patch(route('owner.website.hero.update'), [
            'restore_default' => true,
            'crop_x' => 50,
            'crop_y' => 46,
            'mobile_crop_x' => 54,
            'mobile_crop_y' => 50,
        ])->assertRedirect();

        $hero->refresh();
        $this->assertNull($hero->media_id);
        $this->assertNull($hero->mobile_media_id);
        Storage::disk('local')->assertMissing($replacementPath);
        $this->assertDatabaseMissing('media', ['path' => $replacementPath]);
    }

    public function test_non_owner_cannot_manage_public_home_hero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('owner.website.hero.edit'))
            ->assertForbidden();
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
