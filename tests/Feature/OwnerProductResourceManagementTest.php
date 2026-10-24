<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductFile;
use App\Models\Role;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerProductResourceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_edit_product_and_replace_primary_file_without_losing_version_history(): void
    {
        Storage::fake('local');
        [$owner, $academy, $category] = $this->workspace();
        $product = Product::query()->create([
            'academy_id' => $academy->id, 'category_id' => $category->id, 'created_by' => $owner->id,
            'title' => 'جزوه اولیه', 'slug' => 'starter-' . Str::lower(Str::random(8)),
            'product_type' => 'booklet', 'delivery_type' => 'download', 'price' => 100000,
            'currency' => 'IRR', 'status' => 'draft',
        ]);
        $oldPdf = app(MediaService::class)->upload(UploadedFile::fake()->create('old.pdf', 20, 'application/pdf'), null, [
            'disk' => 'local', 'directory' => 'product-tests', 'collection' => 'product-source', 'visibility' => 'private', 'uploaded_by' => $owner->id,
        ]);
        ProductFile::query()->create([
            'product_id' => $product->id, 'media_id' => $oldPdf->id, 'version' => '1.0',
            'is_primary' => true, 'is_preview' => false, 'requires_watermark' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.products.edit', [$academy, $product]))
            ->assertOk()
            ->assertSee('ویرایش: جزوه اولیه');

        $this->actingAs($owner)
            ->patch(route('owner.products.update', [$academy, $product]), [
                'category_id' => $category->id, 'title' => 'جزوه ویرایش‌شده',
                'subtitle' => 'نسخه تازه', 'description' => 'محتوای به‌روزشده',
                'product_type' => 'booklet', 'price' => 150000, 'sale_price' => 125000,
                'version' => '1.1', 'pdf_file' => UploadedFile::fake()->create('new.pdf', 30, 'application/pdf'),
            ])
            ->assertRedirect(route('owner.products.edit', [$academy, $product]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id, 'title' => 'جزوه ویرایش‌شده', 'price' => 150000, 'sale_price' => 125000,
        ]);
        $this->assertDatabaseHas('product_files', [
            'product_id' => $product->id, 'media_id' => $oldPdf->id, 'version' => '1.0', 'is_primary' => false,
        ]);
        $newPrimary = ProductFile::query()->where('product_id', $product->id)->where('is_primary', true)->firstOrFail();
        $this->assertSame('1.1', $newPrimary->version);
        $this->assertSame('private', $newPrimary->media->visibility);
        $this->assertTrue(Storage::disk('local')->exists($newPrimary->media->path));
    }

    public function test_owner_cannot_edit_another_academys_product_or_resource(): void
    {
        [$owner, $academy] = $this->workspace();
        [$otherOwner, $otherAcademy, $category] = $this->workspace();
        $product = Product::query()->create([
            'academy_id' => $otherAcademy->id, 'category_id' => $category->id, 'created_by' => $otherOwner->id,
            'title' => 'محصول بیرونی', 'slug' => 'outside-' . Str::lower(Str::random(8)),
            'product_type' => 'book', 'delivery_type' => 'download', 'price' => 1000,
            'currency' => 'IRR', 'status' => 'draft',
        ]);
        $media = Media::query()->create([
            'uploaded_by' => $otherOwner->id, 'disk' => 'local', 'path' => 'outside/resource.pdf',
            'original_name' => 'resource.pdf', 'file_name' => 'resource.pdf', 'mime_type' => 'application/pdf',
            'extension' => 'pdf', 'size' => 100, 'checksum' => hash('sha256', 'outside'),
            'visibility' => 'private', 'collection' => 'learning-resources', 'metadata' => [], 'status' => 'active',
        ]);
        $resource = LearningResource::query()->create([
            'academy_id' => $otherAcademy->id, 'media_id' => $media->id, 'uploaded_by' => $otherOwner->id,
            'title' => 'منبع بیرونی', 'resource_type' => 'document', 'visibility' => 'enrolled_students',
            'downloadable' => false, 'status' => 'active', 'sort_order' => 0,
        ]);

        $this->actingAs($owner)->get(route('owner.products.edit', [$academy, $product]))->assertNotFound();
        $this->actingAs($owner)->get(route('owner.resources.edit', [$academy, $resource]))->assertNotFound();
    }

    public function test_owner_can_update_private_resource_and_archive_then_restore_it(): void
    {
        Storage::fake('local');
        [$owner, $academy, $category, $course] = $this->workspace(true);
        $oldMedia = app(MediaService::class)->upload(UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'), null, [
            'disk' => 'local', 'directory' => 'resource-tests', 'collection' => 'learning-resources', 'visibility' => 'private', 'uploaded_by' => $owner->id,
        ]);
        $resource = LearningResource::query()->create([
            'academy_id' => $academy->id, 'course_id' => $course->id, 'media_id' => $oldMedia->id, 'uploaded_by' => $owner->id,
            'title' => 'جزوه قدیمی', 'description' => 'توضیح اولیه', 'resource_type' => 'document',
            'visibility' => 'enrolled_students', 'downloadable' => false, 'status' => 'active', 'sort_order' => 0,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.resources.edit', [$academy, $resource]))
            ->assertOk()
            ->assertSee('notes.pdf');

        $this->actingAs($owner)
            ->patch(route('owner.resources.update', [$academy, $resource]), [
                'title' => 'جزوه به‌روزشده', 'description' => 'شرح تازه',
                'course_id' => $course->id, 'classroom_id' => '', 'lesson_id' => '',
                'release_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'file' => UploadedFile::fake()->create('notes-v2.pdf', 12, 'application/pdf'),
            ])
            ->assertRedirect(route('owner.resources.edit', [$academy, $resource]))
            ->assertSessionHas('success');

        $resource->refresh();
        $this->assertSame('جزوه به‌روزشده', $resource->title);
        $this->assertSame('private', $resource->media->visibility);
        $this->assertNotSame($oldMedia->id, $resource->media_id);
        $this->assertDatabaseMissing('media', ['id' => $oldMedia->id]);

        $this->actingAs($owner)
            ->patch(route('owner.resources.archive', [$academy, $resource]))
            ->assertRedirect(route('owner.resources.index', $academy))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('learning_resources', ['id' => $resource->id, 'status' => 'inactive']);

        $this->actingAs($owner)
            ->patch(route('owner.resources.restore', [$academy, $resource]))
            ->assertRedirect(route('owner.resources.index', $academy))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('learning_resources', ['id' => $resource->id, 'status' => 'active']);
    }

    private function workspace(bool $withCourse = false): array
    {
        $owner = User::factory()->create(['status' => 'active']);
        $role = Role::query()->firstOrCreate(
            ['slug' => 'academy-owner'],
            ['name' => 'مدیر آموزشگاه', 'description' => 'Owner']
        );
        $permissions = collect(['products.view', 'products.manage', 'content.manage', 'academy.view', 'academy.manage', 'dashboard.view'])
            ->map(fn ($name) => Permission::query()->firstOrCreate(['name' => $name], ['label' => $name, 'group' => 'owner']));
        $role->permissions()->syncWithoutDetaching($permissions->pluck('id'));
        $owner->roles()->attach($role->id);
        $academy = Academy::query()->create([
            'owner_id' => $owner->id, 'name' => 'آکادمی مدیریت محتوا',
            'slug' => 'content-' . Str::lower(Str::random(8)), 'status' => 'active',
        ]);
        $category = ProductCategory::query()->create([
            'name' => 'جزوه', 'slug' => 'booklets-' . Str::lower(Str::random(8)),
            'description' => 'منابع', 'sort_order' => 1, 'is_active' => true,
        ]);
        $result = [$owner, $academy, $category];
        if ($withCourse) {
            $course = Course::query()->create([
                'academy_id' => $academy->id, 'created_by' => $owner->id,
                'title' => 'دوره تست', 'slug' => 'course-' . Str::lower(Str::random(8)),
                'status' => 'published', 'access_type' => 'free', 'price' => 0, 'published_at' => now(),
            ]);
            $result[] = $course;
        }
        return $result;
    }
}
