<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Media;
use App\Models\User;
use App\Services\StudentLearningResourceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentLearningResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_classroom_resource_is_isolated_from_other_students_in_same_course(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $studentInClass = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $studentOutsideClass = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();

        $studentOutsideClass->enrollments()->updateOrCreate(
            ['course_id' => $course->id],
            [
                'status' => 'active',
                'started_at' => now(),
            ]
        );

        $media = $this->makeMedia('class-notes.pdf', 'application/pdf');

        $resource = LearningResource::create([
            'academy_id' => $course->academy_id,
            'course_id' => null,
            'classroom_id' => $classroom->id,
            'lesson_id' => null,
            'media_id' => $media->id,
            'uploaded_by' => User::where('email', 'owner@sheykhan.test')->value('id'),
            'title' => 'جزوه اختصاصی کلاس',
            'resource_type' => 'document',
            'visibility' => 'enrolled_students',
            'release_at' => now()->subMinute(),
            'downloadable' => true,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $service = app(StudentLearningResourceService::class);

        $this->assertTrue($service->canAccess($studentInClass, $resource));
        $this->assertFalse($service->canAccess($studentOutsideClass, $resource));

        $this->assertTrue(
            $service->query($studentInClass)->whereKey($resource->id)->exists()
        );

        $this->assertFalse(
            $service->query($studentOutsideClass)->whereKey($resource->id)->exists()
        );

        $this->actingAs($studentInClass)
            ->get(route('student.resources.download', $resource))
            ->assertDownload('class-notes.pdf');

        $this->actingAs($studentOutsideClass)
            ->get(route('student.resources.download', $resource))
            ->assertNotFound();
    }

    public function test_view_only_resource_returns_friendly_forbidden_message_when_download_is_attempted(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $media = $this->makeMedia('view-only.pdf', 'application/pdf');

        $resource = LearningResource::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'classroom_id' => null,
            'lesson_id' => null,
            'media_id' => $media->id,
            'uploaded_by' => User::where('email', 'owner@sheykhan.test')->value('id'),
            'title' => 'محتوای فقط مشاهده',
            'resource_type' => 'document',
            'visibility' => 'enrolled_students',
            'release_at' => now()->subMinute(),
            'downloadable' => false,
            'status' => 'active',
        ]);

        $this->actingAs($student)
            ->get(route('student.resources.download', $resource))
            ->assertForbidden()
            ->assertSee('این محتوا فقط برای مشاهده ارائه شده و امکان دانلود آن فعال نیست.');
    }

    public function test_unreleased_resource_is_not_visible_or_downloadable(): void
    {
        $this->seed();

        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $media = $this->makeMedia('future.pdf', 'application/pdf');

        $resource = LearningResource::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'classroom_id' => null,
            'lesson_id' => null,
            'media_id' => $media->id,
            'uploaded_by' => User::where('email', 'owner@sheykhan.test')->value('id'),
            'title' => 'منبع آینده',
            'resource_type' => 'document',
            'visibility' => 'enrolled_students',
            'release_at' => now()->addHour(),
            'downloadable' => true,
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $service = app(StudentLearningResourceService::class);

        $this->assertFalse($service->canAccess($student, $resource));
        $this->assertFalse(
            $service->query($student)->whereKey($resource->id)->exists()
        );
    }

    public function test_student_resource_routes_require_the_dedicated_resource_permission(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $student->roles()
            ->firstOrFail()
            ->permissions()
            ->detach(
                \App\Models\Permission::where('name', 'resources.view')->value('id')
            );

        $this->actingAs($student)
            ->get(route('student.resources.index'))
            ->assertForbidden();
    }

    private function makeMedia(string $fileName, string $mimeType): Media
    {
        Storage::fake('local');

        $path = 'student-resources/' . str()->uuid() . '-' . $fileName;

        Storage::disk('local')->put($path, 'student-resource-fixture');

        return Media::create([
            'uploaded_by' => User::where('email', 'owner@sheykhan.test')->value('id'),
            'disk' => 'local',
            'path' => $path,
            'original_name' => $fileName,
            'file_name' => basename($path),
            'mime_type' => $mimeType,
            'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
            'size' => Storage::disk('local')->size($path),
            'checksum' => hash('sha256', Storage::disk('local')->get($path)),
            'visibility' => 'private',
            'collection' => 'learning-resources',
            'metadata' => ['test' => true],
            'status' => 'active',
        ]);
    }
}
