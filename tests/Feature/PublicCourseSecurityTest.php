<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Services\CourseCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCourseSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_course_catalog_exposes_only_published_lesson_metadata_and_no_protected_media(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $course->update([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $course->academy()->update(['status' => 'active']);

        $course = app(CourseCatalogService::class)->findPublished($course);

        $lesson = $course->sections
            ->flatMap(fn ($section) => $section->lessons)
            ->first();

        $this->assertNotNull($lesson);
        $this->assertFalse(array_key_exists('content', $lesson->getAttributes()));
        $this->assertFalse($lesson->relationLoaded('media'));
        $this->assertTrue($lesson->status === 'published');
    }

    public function test_unauthorized_public_course_request_does_not_hydrate_protected_media(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $course->update([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $course->academy()->update(['status' => 'active']);

        $response = $this->get(route('courses.show', ['course' => $course]));

        $response->assertOk();
        $response->assertDontSee('دانلود فایل');
    }
}
