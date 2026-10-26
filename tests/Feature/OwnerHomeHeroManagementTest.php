<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\HomeBanner;
use App\Models\HomeHeroSlide;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerHomeHeroManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['filesystems.default' => 'local']);
    }

    public function test_seo_specialist_can_manage_multiple_independent_hero_slides_and_public_home_uses_them(): void
    {
        Storage::fake('local');
        Cache::flush();
        [$owner, $academy, $seo] = $this->workspace();

        $this->actingAs($seo)
            ->get(route('owner.website.hero.edit'))
            ->assertOk()
            ->assertViewIs('owner.website.hero')
            ->assertSee('data-home-hero-editor', false)
            ->assertSee('name="slot" value="1"', false)
            ->assertSee('name="slot" value="5"', false);

        $this->actingAs($seo)
            ->patch(route('owner.website.hero.update'), [
                'slot' => 1,
                'image' => UploadedFile::fake()->image('desktop-hero.jpg', 1920, 1080),
                'mobile_image' => UploadedFile::fake()->image('mobile-hero.jpg', 900, 1200),
                'title' => 'Hero slide first',
                'description' => 'Description for the first hero slide',
                'cta_label' => 'Explore courses',
                'cta_url' => '/courses',
                'is_active' => true,
                'crop_x' => 24,
                'crop_y' => 68,
                'mobile_crop_x' => 72,
                'mobile_crop_y' => 35,
            ])
            ->assertRedirect(route('owner.website.hero.edit'));

        $this->actingAs($seo)
            ->patch(route('owner.website.hero.update'), [
                'slot' => 2,
                'image' => UploadedFile::fake()->image('second-hero.jpg', 1600, 900),
                'title' => 'Hero slide second',
                'is_active' => true,
                'crop_x' => 50,
                'crop_y' => 46,
                'mobile_crop_x' => 54,
                'mobile_crop_y' => 50,
            ])
            ->assertRedirect(route('owner.website.hero.edit'));

        $slides = HomeHeroSlide::query()->with(['media', 'mobileMedia'])->orderBy('slot')->get();
        $this->assertCount(2, $slides);
        $this->assertSame('Hero slide first', $slides[0]->title);
        $this->assertSame('Hero slide second', $slides[1]->title);
        $this->assertTrue($slides[0]->is_active);
        $this->assertTrue($slides[1]->is_active);
        $this->assertSame(24, $slides[0]->crop_x);
        $this->assertSame(68, $slides[0]->crop_y);
        $this->assertSame(72, $slides[0]->mobile_crop_x);
        $this->assertSame(35, $slides[0]->mobile_crop_y);
        $this->assertSame('home-hero', $slides[0]->media->collection);
        $this->assertSame('home-hero', $slides[0]->mobileMedia->collection);
        Storage::disk('local')->assertExists($slides[0]->media->path);
        Storage::disk('local')->assertExists($slides[0]->mobileMedia->path);
        Storage::disk('local')->assertExists($slides[1]->media->path);

        Cache::flush();
        $response = $this->get(route('home'))->assertOk();
        $response->assertSee(route('media.public', $slides[0]->media), false)
            ->assertSee(route('media.public', $slides[0]->mobileMedia), false)
            ->assertSee(route('media.public', $slides[1]->media), false)
            ->assertSee('data-home-hero-mobile-source', false)
            ->assertSee('--hero-crop-x: 24%', false)
            ->assertSee('--hero-mobile-crop-y: 35%', false)
            ->assertSee('Hero slide first')
            ->assertSee('Hero slide second');
    }

    public function test_replacing_or_removing_a_slide_cleans_up_only_unused_hero_uploads(): void
    {
        Storage::fake('local');
        [$owner, $academy, $seo] = $this->workspace();

        $this->actingAs($seo)->patch(route('owner.website.hero.update'), [
            'slot' => 1,
            'image' => UploadedFile::fake()->image('first-hero.jpg', 1600, 900),
            'mobile_image' => UploadedFile::fake()->image('first-mobile.jpg', 800, 1000),
            'is_active' => true,
            'crop_x' => 50,
            'crop_y' => 46,
            'mobile_crop_x' => 54,
            'mobile_crop_y' => 50,
        ])->assertRedirect();

        $oldSlide = HomeHeroSlide::query()->with(['media', 'mobileMedia'])->where('slot', 1)->firstOrFail();
        $oldDesktopPath = $oldSlide->media->path;
        $oldMobilePath = $oldSlide->mobileMedia->path;

        $this->actingAs($seo)->patch(route('owner.website.hero.update'), [
            'slot' => 1,
            'image' => UploadedFile::fake()->image('replacement.jpg', 1600, 900),
            'remove_mobile_image' => true,
            'is_active' => true,
            'crop_x' => 50,
            'crop_y' => 46,
            'mobile_crop_x' => 54,
            'mobile_crop_y' => 50,
        ])->assertRedirect();

        $slide = HomeHeroSlide::query()->with(['media', 'mobileMedia'])->where('slot', 1)->firstOrFail();
        $this->assertNotSame($oldSlide->media_id, $slide->media_id);
        $this->assertNull($slide->mobile_media_id);
        Storage::disk('local')->assertMissing($oldDesktopPath);
        Storage::disk('local')->assertMissing($oldMobilePath);
        $this->assertDatabaseMissing('media', ['path' => $oldDesktopPath]);
        $this->assertDatabaseMissing('media', ['path' => $oldMobilePath]);

        $replacementPath = $slide->media->path;
        $this->actingAs($seo)->patch(route('owner.website.hero.update'), [
            'slot' => 1,
            'remove_slide' => true,
        ])->assertRedirect();

        $this->assertDatabaseMissing('home_hero_slides', ['slot' => 1]);
        Storage::disk('local')->assertMissing($replacementPath);
        $this->assertDatabaseMissing('media', ['path' => $replacementPath]);
    }

    public function test_homepage_renders_hero_slides_and_academy_banners_as_separate_sections(): void
    {
        Storage::fake('local');
        Cache::flush();
        [$owner, $academy] = $this->workspace();

        $heroMedia = app(MediaService::class)->upload(
            UploadedFile::fake()->image('hero-independent.jpg', 1600, 900),
            null,
            [
                'disk' => 'local',
                'directory' => 'website/home-hero/slides',
                'collection' => 'home-hero',
                'visibility' => 'public',
                'uploaded_by' => $owner->id,
            ],
        );

        HomeHeroSlide::query()->create([
            'slot' => 1,
            'media_id' => $heroMedia->id,
            'title' => 'Independent Hero Title',
            'is_active' => true,
            'sort_order' => 1,
            'updated_by' => $owner->id,
        ]);

        $bannerMedia = app(MediaService::class)->upload(
            UploadedFile::fake()->image('banner-independent.jpg', 1600, 900),
            null,
            [
                'disk' => 'local',
                'directory' => 'academies/' . $academy->id . '/home-banners',
                'collection' => 'home-banners',
                'visibility' => 'public',
                'uploaded_by' => $owner->id,
            ],
        );

        HomeBanner::query()->create([
            'academy_id' => $academy->id,
            'media_id' => $bannerMedia->id,
            'slot' => 1,
            'title' => 'بنر مستقل شیخان',
            'description' => 'این بنر نباید به اسلاید Hero تبدیل شود.',
            'cta_label' => null,
            'cta_url' => null,
            'sort_order' => 1,
            'crop_x' => 50,
            'crop_y' => 50,
            'is_active' => true,
        ]);

        Cache::flush();
        $response = $this->actingAs($owner)->get(route('home'))->assertOk();
        $html = $response->getContent();
        $bannerGridPosition = strpos($html, 'class="home-banner-grid"');

        $this->assertNotFalse($bannerGridPosition, 'The independent Banner section should render.');
        $heroMarkup = substr($html, 0, $bannerGridPosition);
        $bannerMarkup = substr($html, $bannerGridPosition);

        $this->assertStringContainsString('data-home-hero-slide', $heroMarkup);
        $this->assertStringContainsString(route('media.public', $heroMedia), $heroMarkup);
        $this->assertStringNotContainsString('بنر مستقل شیخان', $heroMarkup);
        $this->assertStringContainsString('بنر مستقل شیخان', $bannerMarkup);
        $this->assertStringContainsString(route('media.public', $bannerMedia), $bannerMarkup);
    }

    public function test_only_seo_specialists_can_manage_public_home_hero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('owner.website.hero.edit'))
            ->assertForbidden();

        [$owner] = $this->workspace();

        $this->actingAs($owner)
            ->get(route('owner.website.hero.edit'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('owner.website.hero.update'), ['slot' => 1])
            ->assertForbidden();
    }

    public function test_seo_specialist_can_manage_hero_without_a_slug_or_branch_assumption(): void
    {
        [$owner, $academy, $seo] = $this->workspace('sheykhan-main');

        $this->actingAs($seo)
            ->get(route('owner.website.hero.edit'))
            ->assertOk();
    }

    private function workspace(string $academySlug = 'sheykhan-academy'): array
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
            'slug' => $academySlug,
            'status' => 'active',
        ]);

        $seoRole = Role::firstOrCreate(
            ['slug' => 'seo-specialist'],
            ['name' => 'متخصص SEO', 'description' => 'Public website management']
        );
        $seoPermissions = collect(['dashboard.view', 'seo.manage'])->map(fn ($name) => Permission::firstOrCreate(
            ['name' => $name],
            ['label' => $name, 'group' => 'seo']
        ));
        $seoRole->permissions()->sync($seoPermissions->pluck('id'));

        $seo = User::factory()->create([
            'email' => 'seo-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);
        $seo->roles()->attach($seoRole->id);

        return [$owner, $academy, $seo];
    }
}
