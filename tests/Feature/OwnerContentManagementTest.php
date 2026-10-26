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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_specialist_can_create_and_publish_public_academy_content(): void
    {
        [$seo, $academy] = $this->seoSpecialistWithAcademy();

        $category = AcademyContentCategory::create([
            'academy_id' => $academy->id,
            'title' => 'مسیر والدین',
            'slug' => 'parents',
            'is_active' => true,
        ]);

        $response = $this->actingAs($seo)->post(route('owner.content.store'), [
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


    public function test_seo_specialist_content_cover_is_stored_as_public_media(): void
    {
        Storage::fake('local');

        [$seo, $academy] = $this->seoSpecialistWithAcademy();

        $category = AcademyContentCategory::create([
            'academy_id' => $academy->id,
            'title' => 'رسانه',
            'slug' => 'media',
            'is_active' => true,
        ]);

        $this->actingAs($seo)
            ->post(route('owner.content.store'), [
                'academy_id' => $academy->id,
                'category_id' => $category->id,
                'type' => 'article',
                'title' => 'محتوای تصویری',
                'body' => 'متن',
                'status' => 'published',
                'cover_image' => UploadedFile::fake()->image('cover.webp', 1200, 675),
            ])
            ->assertSessionHas('success');

        $content = AcademyContent::query()->where('academy_id', $academy->id)->firstOrFail();

        $this->assertDatabaseHas('media', [
            'uploaded_by' => $seo->id,
            'visibility' => 'public',
            'collection' => 'cover',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('media_attachments', [
            'mediable_type' => AcademyContent::class,
            'mediable_id' => $content->id,
            'collection' => 'cover',
            'is_featured' => 1,
        ]);
    }

    public function test_seo_specialist_can_edit_public_content_across_active_academies(): void
    {
        [$seoA, $academyA] = $this->seoSpecialistWithAcademy();
        [, $academyB] = $this->seoSpecialistWithAcademy();

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

        $this->actingAs($seoA)
            ->get(route('owner.content.edit', $content))
            ->assertOk();
    }

    public function test_academy_owner_cannot_create_public_content_even_with_legacy_permission(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'academy-owner'],
            ['name' => 'مدیر آموزشگاه', 'description' => 'Owner test']
        );
        $legacyPermission = Permission::firstOrCreate(
            ['name' => 'content.manage'],
            ['label' => 'مدیریت محتوا', 'group' => 'content']
        );
        $role->permissions()->syncWithoutDetaching([$legacyPermission->id]);

        $owner = User::factory()->create(['status' => 'active']);
        $owner->roles()->attach($role->id);
        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی مالک',
            'slug' => 'owner-' . Str::random(8),
            'status' => 'active',
        ]);
        $category = AcademyContentCategory::create([
            'academy_id' => $academy->id,
            'title' => 'عمومی',
            'slug' => 'public-' . Str::random(8),
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('owner.content.store'), [
                'academy_id' => $academy->id,
                'category_id' => $category->id,
                'type' => 'article',
                'title' => 'تلاش غیرمجاز',
                'body' => 'متن',
                'status' => 'draft',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('academy_contents', ['title' => 'تلاش غیرمجاز']);

        try {
            app(\\App\\Services\\OwnerContentService::class)->index($owner);
            $this->fail('Direct service access must not bypass the SEO-only role.');
        } catch (\\Symfony\\Component\\HttpKernel\\Exception\\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    private function seoSpecialistWithAcademy(): array
    {
        $role = Role::firstOrCreate(
            ['slug' => 'seo-specialist'],
            ['name' => 'متخصص SEO', 'description' => 'Public website content']
        );
        $permission = Permission::firstOrCreate(
            ['name' => 'seo.manage'],
            ['label' => 'مدیریت SEO', 'group' => 'seo']
        );
        $role->permissions()->syncWithoutDetaching([$permission->id]);

        $seo = User::factory()->create(['status' => 'active']);
        $seo->roles()->attach($role->id);

        $academy = Academy::create([
            'owner_id' => $seo->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::random(7),
            'status' => 'active',
        ]);

        return [$seo, $academy];
    }
}