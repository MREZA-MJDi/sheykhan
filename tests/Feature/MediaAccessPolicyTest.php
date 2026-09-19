<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAccessPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_media_respects_free_and_paid_access_levels(): void
    {
        $this->seed();

        Storage::fake('local');

        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $previewStudent = User::where('email', 'student3@sheykhan.test')->firstOrFail();
        $paidStudent = User::where('email', 'student1@sheykhan.test')->firstOrFail();

        $previewPath = 'courses/' . $course->id . '/preview.pdf';
        $paidPath = 'courses/' . $course->id . '/paid.pdf';

        Storage::disk('local')->put($previewPath, '%PDF-preview');
        Storage::disk('local')->put($paidPath, '%PDF-paid');

        $preview = Media::create([
            'uploaded_by' => $course->created_by,
            'disk' => 'local',
            'path' => $previewPath,
            'original_name' => 'preview.pdf',
            'file_name' => 'preview.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 13,
            'checksum' => hash('sha256', '%PDF-preview'),
            'visibility' => 'private',
            'collection' => 'course-assets',
            'metadata' => ['access' => 'free', 'downloadable' => true],
            'status' => 'active',
        ]);

        $paid = Media::create([
            'uploaded_by' => $course->created_by,
            'disk' => 'local',
            'path' => $paidPath,
            'original_name' => 'paid.pdf',
            'file_name' => 'paid.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 10,
            'checksum' => hash('sha256', '%PDF-paid'),
            'visibility' => 'private',
            'collection' => 'course-assets',
            'metadata' => ['access' => 'paid', 'downloadable' => true],
            'status' => 'active',
        ]);

        $preview->attachments()->create([
            'mediable_type' => Course::class,
            'mediable_id' => $course->id,
            'collection' => 'course-assets',
            'sort_order' => 0,
        ]);

        $paid->attachments()->create([
            'mediable_type' => Course::class,
            'mediable_id' => $course->id,
            'collection' => 'course-assets',
            'sort_order' => 1,
        ]);

        $this->actingAs($previewStudent)
            ->get(route('media.download', $preview))
            ->assertOk();

        $this->actingAs($previewStudent)
            ->get(route('media.download', $paid))
            ->assertForbidden();

        $this->actingAs($paidStudent)
            ->get(route('media.download', $paid))
            ->assertOk();
    }
}
