<?php

namespace Tests\Feature;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerBlogCrudAndCoverUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_seo_specialist_can_create_update_and_delete_blog_post_with_cover_image(): void
    {
        Storage::fake('local');
        $seoUser = $this->seoSpecialist();
        $category = BlogCategory::query()->create([
            'name' => 'راهنما',
            'slug' => 'guide-' . Str::random(6),
            'description' => 'مقالات راهنما',
        ]);

        $this->actingAs($seoUser)
            ->post(route('seo.blog.store'), [
                'category_id' => $category->id,
                'title' => 'راهنمای آزمایشی',
                'slug' => 'sample-guide-' . Str::random(6),
                'excerpt' => 'خلاصه مقاله',
                'content' => 'متن کامل مقاله',
                'status' => 'published',
                'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 750)->size(180),
            ])
            ->assertRedirect();

        $post = BlogPost::query()->firstOrFail();
        $this->assertSame($seoUser->id, $post->author_id);
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);

        $firstMedia = $post->media()->wherePivot('collection', 'cover')->firstOrFail();
        $firstPath = $firstMedia->path;
        Storage::disk('local')->assertExists($firstPath);
        $this->assertSame('public', $firstMedia->visibility);

        $post->seoMeta()->create(['title' => 'عنوان SEO آزمایشی']);

        $this->actingAs($seoUser)
            ->patch(route('seo.blog.update', $post), [
                'category_id' => $category->id,
                'title' => 'راهنمای به‌روزشده',
                'slug' => $post->slug,
                'excerpt' => 'خلاصه جدید',
                'content' => 'متن به‌روزشده',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->image('new-cover.webp', 1200, 750)->size(210),
            ])
            ->assertRedirect(route('seo.blog.edit', $post));

        $post->refresh();
        $this->assertSame('راهنمای به‌روزشده', $post->title);
        $this->assertSame('draft', $post->status);
        $this->assertNull($post->published_at);

        $replacementMedia = $post->media()->wherePivot('collection', 'cover')->firstOrFail();
        $this->assertNotSame($firstMedia->id, $replacementMedia->id);
        $this->assertDatabaseCount('media', 1);
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($replacementMedia->path);

        $this->actingAs($seoUser)
            ->delete(route('seo.blog.destroy', $post))
            ->assertRedirect(route('seo.blog.index'));

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
        $this->assertDatabaseMissing('seo_metas', [
            'seoable_type' => BlogPost::class,
            'seoable_id' => $post->id,
        ]);
        $this->assertDatabaseCount('media', 0);
        Storage::disk('local')->assertMissing($replacementMedia->path);
    }

    public function test_blog_cover_upload_rejects_non_image_files(): void
    {
        Storage::fake('local');
        $seoUser = $this->seoSpecialist();

        $this->actingAs($seoUser)
            ->post(route('seo.blog.store'), [
                'title' => 'مقاله بدون تصویر معتبر',
                'content' => 'متن مقاله',
                'status' => 'draft',
                'cover_image' => UploadedFile::fake()->create('cover.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('cover_image');

        $this->assertDatabaseCount('blog_posts', 0);
        $this->assertDatabaseCount('media', 0);
    }

    private function seoSpecialist(): User
    {
        $role = Role::query()->create([
            'name' => 'متخصص SEO',
            'slug' => 'seo-specialist',
            'description' => 'مدیریت محتوای عمومی سایت',
        ]);
        $permission = Permission::query()->create([
            'name' => 'seo.manage',
            'label' => 'مدیریت SEO',
            'group' => 'seo',
        ]);
        $role->permissions()->attach($permission->id);

        $user = User::factory()->create(['status' => 'active']);
        $user->roles()->attach($role->id);

        return $user;
    }
}
