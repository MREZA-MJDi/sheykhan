<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourseMediaChunkUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_resume_and_finalize_a_private_course_file_upload(): void
    {
        Storage::fake('local');

        [$owner, $course] = $this->ownerWorkspace();
        $uploadId = (string) Str::uuid();
        $firstChunkBytes = 5 * 1024 * 1024;
        $fileSize = $firstChunkBytes + 1;
        $pdfHeader = "%PDF-1.4\n";
        $firstChunk = $pdfHeader . str_repeat(' ', $firstChunkBytes - strlen($pdfHeader));
        $lastChunk = 'x';

        $storeUrl = route('owner.courses.media.chunks.store', $course);
        $statusUrl = route('owner.courses.media.chunks.status', [
            'course' => $course,
            'uploadId' => $uploadId,
        ]);
        $completeUrl = route('owner.courses.media.chunks.complete', [
            'course' => $course,
            'uploadId' => $uploadId,
        ]);

        $this->actingAs($owner)
            ->postJson($storeUrl, [
                'upload_id' => $uploadId,
                'chunk_index' => 0,
                'total_chunks' => 2,
                'file_name' => 'course-handout.pdf',
                'file_size' => $fileSize,
                'mime_type' => 'application/pdf',
                'collection' => 'course-assets',
                'chunk' => UploadedFile::fake()->createWithContent('part.bin', $firstChunk),
            ])
            ->assertOk()
            ->assertJsonPath('received_chunks', [0])
            ->assertJsonPath('total_chunks', 2);

        // A browser reload can query the existing session and continue from the
        // first missing part instead of retransmitting the completed first part.
        $this->actingAs($owner)
            ->getJson($statusUrl)
            ->assertOk()
            ->assertJsonPath('received_chunks', [0])
            ->assertJsonPath('file_name', 'course-handout.pdf');

        $this->actingAs($owner)
            ->postJson($storeUrl, [
                'upload_id' => $uploadId,
                'chunk_index' => 1,
                'total_chunks' => 2,
                'file_name' => 'course-handout.pdf',
                'file_size' => $fileSize,
                'mime_type' => 'application/pdf',
                'collection' => 'course-assets',
                'chunk' => UploadedFile::fake()->createWithContent('part.bin', $lastChunk),
            ])
            ->assertOk()
            ->assertJsonPath('received_chunks', [0, 1]);

        $this->actingAs($owner)
            ->postJson($completeUrl)
            ->assertOk()
            ->assertJsonPath('message', 'فایل با موفقیت دریافت و ذخیره شد.')
            ->assertJsonStructure(['media_id', 'redirect_url']);

        $media = Media::query()
            ->where('original_name', 'course-handout.pdf')
            ->firstOrFail();

        $this->assertSame('private', $media->visibility);
        $this->assertSame('course-assets', $media->collection);
        $this->assertSame($fileSize, (int) $media->size);
        $this->assertDatabaseHas('media_attachments', [
            'media_id' => $media->id,
            'mediable_type' => Course::class,
            'mediable_id' => $course->id,
        ]);

        Storage::disk('local')->assertExists($media->path);
        $this->assertDatabaseMissing('media', [
            'id' => $media->id,
            'visibility' => 'public',
        ]);
    }

    public function test_teacher_cannot_use_chunked_upload_to_create_a_public_course_cover(): void
    {
        Storage::fake('local');
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->postJson(route('teacher.courses.media.chunks.store', $course), [
                'upload_id' => (string) Str::uuid(),
                'chunk_index' => 0,
                'total_chunks' => 1,
                'file_name' => 'cover.jpg',
                'file_size' => 1,
                'collection' => 'course-cover',
                'chunk' => UploadedFile::fake()->createWithContent('part.bin', 'x'),
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('media', [
            'original_name' => 'cover.jpg',
        ]);
    }

    /**
     * @return array{0:User,1:Course}
     */
    private function ownerWorkspace(): array
    {
        $ownerRole = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permissions = collect(['courses.manage', 'media.upload'])->map(fn (string $name) => Permission::create([
            'name' => $name,
            'label' => $name,
            'group' => 'courses',
        ]));

        $ownerRole->permissions()->attach($permissions->pluck('id')->all());

        $owner = User::factory()->create([
            'email' => 'chunk-owner-' . Str::random(8) . '@test.local',
        ]);
        $owner->roles()->attach($ownerRole->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی فایل‌ها',
            'slug' => 'chunk-academy-' . Str::random(8),
            'status' => 'active',
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره دارای فایل',
            'slug' => 'chunk-course-' . Str::random(8),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);

        return [$owner, $course];
    }
}
