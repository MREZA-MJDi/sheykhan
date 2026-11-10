<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\Media;
use App\Models\MediaAttachment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoBlogCoverUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_seo_specialist_can_upload_and_replace_a_public_blog_cover(): void
    {
        $specialist = $this->makeSeoSpecialist();

        $this->actingAs($specialist)
            ->post(route('seo.blog.store'), [
                'title' => 'راهنمای انتخاب کلاس',
                'slug' => 'class-selection-guide',
                'excerpt' => 'راهنمای کوتاه انتخاب کلاس مناسب',
                'content' => 'متن کامل مقاله برای آزمایش بارگذاری تصویر شاخص.',
                'status' => 'published',
                'published_at' => now()->toDateTimeString(),
                'cover_image' => $this->validPng('first-cover.png'),
            ])
            ->assertRedirect();

        $post = BlogPost::query()->where('slug', 'class-selection-guide')->firstOrFail();
        $firstMedia = $post->media()->wherePivot('collection', 'cover')->firstOrFail();

        $this->assertSame('public', $firstMedia->visibility);
        $this->assertDatabaseHas('media_attachments', [
            'media_id' => $firstMedia->id,
            'mediable_type' => $post->getMorphClass(),
            'mediable_id' => $post->id,
            'collection' => 'cover',
            'is_featured' => true,
        ]);
        Storage::disk('local')->assertExists($firstMedia->path);

        $this->get(route('media.public', $firstMedia))->assertOk();

        $this->actingAs($specialist)
            ->patch(route('seo.blog.update', $post), [
                'title' => 'راهنمای به‌روز انتخاب کلاس',
                'slug' => 'class-selection-guide',
                'excerpt' => 'خلاصه به‌روزشده',
                'content' => 'متن به‌روزشده مقاله.',
                'status' => 'published',
                'published_at' => now()->toDateTimeString(),
                'cover_image' => $this->validPng('replacement-cover.png'),
            ])
            ->assertRedirect();

        $post->refresh();
        $coverMedia = $post->media()->wherePivot('collection', 'cover')->firstOrFail();

        $this->assertNotSame($firstMedia->id, $coverMedia->id);
        $this->assertSame(1, $post->media()->wherePivot('collection', 'cover')->count());
        $this->assertDatabaseMissing('media', ['id' => $firstMedia->id]);
        $this->assertDatabaseMissing('media_attachments', ['media_id' => $firstMedia->id]);
        Storage::disk('local')->assertMissing($firstMedia->path);
        Storage::disk('local')->assertExists($coverMedia->path);

        $this->get(route('media.public', $coverMedia))->assertOk();
    }

    private function makeSeoSpecialist(): User
    {
        $role = Role::query()->create([
            'name' => 'متخصص SEO',
            'slug' => 'seo-specialist',
            'description' => 'Dedicated SEO specialist',
        ]);

        $permission = Permission::query()->create([
            'name' => 'seo.manage',
            'label' => 'Manage SEO',
            'group' => 'seo',
        ]);

        $role->permissions()->attach($permission->id);

        $user = User::factory()->create([
            'email' => 'seo-cover-upload@example.test',
            'status' => 'active',
        ]);

        $user->roles()->attach($role->id);

        return $user;
    }

    private function validPng(string $name): UploadedFile
    {
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=',
            true,
        );

        return UploadedFile::fake()->createWithContent($name, $contents ?: '');
    }
}
