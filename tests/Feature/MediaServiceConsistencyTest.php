<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Role;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

final class MediaServiceConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_media_attachment_cleans_up_both_file_and_database_row(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create(['status' => 'active']);
        $this->actingAs($uploader);

        // Role is an Eloquent model but does not use HasMedia. This forces the
        // attach step to fail after the file and its media row have been created.
        try {
            app(MediaService::class)->upload(
                UploadedFile::fake()->create('will-not-attach.pdf', 24, 'application/pdf'),
                new Role(),
                [
                    'disk' => 'local',
                    'directory' => 'media-consistency-test',
                    'collection' => 'test',
                    'visibility' => 'private',
                ],
            );

            $this->fail('The upload should fail when the target model has no media relation.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('HasMedia trait', $exception->getMessage());
        }

        $this->assertDatabaseCount('media', 0);
        $this->assertSame([], Storage::disk('local')->allFiles('media-consistency-test'));
    }
}
