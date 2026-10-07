<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningResource;
use App\Models\User;
use App\Services\StudentLearningResourceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLearningResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_classroom_resource_is_isolated_from_other_students_in_same_course(): void
    {
        $this->seed();

        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $classroom = $course->classrooms()->firstOrFail();
        $media = $course->media()->firstOrFail();

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

        $studentInClass = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $studentOutsideClass = User::where('email', 'student3@sheykhan.test')->firstOrFail();

        // Keep the second student enrolled in the same course but outside
        // the target classroom so the test exercises classroom isolation.
        $studentOutsideClass->enrollments()->updateOrCreate(
            ['course_id' => $course->id],
            ['status' => 'active', 'started_at' => now()]
        );

        $service = app(StudentLearningResourceService::class);

        $this->assertTrue($service->canAccess($studentInClass, $resource));
        $this->assertFalse($service->canAccess($studentOutsideClass, $resource));
        $this->assertTrue($service->query($studentInClass)->whereKey($resource->id)->exists());
        $this->assertFalse($service->query($studentOutsideClass)->whereKey($resource->id)->exists());

        $this->actingAs($studentOutsideClass)
            ->get(route('student.resources.download', $resource))
            ->assertNotFound();
    }

    public function test_view_only_resource_returns_friendly_forbidden_message_when_download_is_attempted(): void
    {
        $this->seed();

        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $media = $course->media()->firstOrFail();

        $resource = LearningResource::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
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

        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $media = $course->media()->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

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
        $this->assertFalse($service->query($student)->whereKey($resource->id)->exists());
    }
}
