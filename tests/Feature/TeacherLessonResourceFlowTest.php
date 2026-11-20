<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\User;
use App\Notifications\TeacherLessonSubmittedForReview;
use Database\Seeders\Support\SeedMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherLessonResourceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_upload_creates_private_pending_resource_and_notifies_course_owner(): void
    {
        Storage::fake('local');
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $response = $this->actingAs($teacher)->post(
            route('teacher.lessons.media.store', $lesson),
            [
                'media' => UploadedFile::fake()->create('lesson-handout.pdf', 120, 'application/pdf'),
                'downloadable' => '0',
            ]
        );

        $response->assertRedirect();
        $resource = LearningResource::where('lesson_id', $lesson->id)->latest('id')->firstOrFail();

        $this->assertSame($course->id, $resource->course_id);
        $this->assertFalse($resource->downloadable);
        $this->assertSame('draft', $resource->status);
        $this->assertNull($resource->release_at);
        $this->assertSame('draft', $lesson->fresh()->status);

        $owner = $course->academy->owner;
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
            'type' => TeacherLessonSubmittedForReview::class,
        ]);

        $this->actingAs($student)
            ->get(route('student.resources.index'))
            ->assertOk()
            ->assertDontSee('lesson-handout.pdf');

        $this->actingAs($student)
            ->get(route('student.resources.download', $resource))
            ->assertForbidden()
            ->assertSee('فایل‌های آموزشی محافظت‌شده');

        $this->assertDatabaseHas('media', [
            'id' => $resource->media_id,
            'visibility' => 'private',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('media_attachments', [
            'media_id' => $resource->media_id,
            'mediable_type' => Lesson::class,
            'mediable_id' => $lesson->id,
        ]);
    }

    public function test_teacher_cannot_publish_lesson_until_owner_approves_and_resources_release(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $media = SeedMedia::make('teacher-existing-resource', $teacher->id, 'application/pdf', 'pdf', 'learning-resources', 'private');

        $resource = LearningResource::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'media_id' => $media->id,
            'uploaded_by' => $teacher->id,
            'title' => 'منبع انتظار انتشار',
            'resource_type' => 'document',
            'visibility' => 'enrolled_students',
            'release_at' => null,
            'downloadable' => true,
            'status' => 'draft',
        ]);

        $lesson->update(['status' => 'draft', 'published_at' => null]);

        $this->actingAs($teacher)->patch(
            route('teacher.lessons.update', $lesson),
            [
                'title' => $lesson->title,
                'slug' => $lesson->slug,
                'type' => $lesson->type,
                'summary' => $lesson->summary,
                'content' => $lesson->content,
                'is_free' => $lesson->is_free ? '1' : '0',
                'status' => 'published',
            ]
        )->assertRedirect();

        $resource->refresh();

        $this->assertSame('draft', $resource->status);
        $this->assertNull($resource->release_at);
        $this->assertSame('draft', $lesson->fresh()->status);

        $owner = $course->academy->owner;

        $this->actingAs($owner)
            ->patch(route('owner.courses.lessons.publish', [$course, $lesson]))
            ->assertRedirect();

        $resource->refresh();
        $this->assertSame('active', $resource->status);
        $this->assertNotNull($resource->release_at);
        $this->assertTrue($resource->release_at->isPast());
        $this->assertSame('published', $lesson->fresh()->status);

        $notification = $owner->notifications()
            ->where('type', TeacherLessonSubmittedForReview::class)
            ->firstOrFail();

        $this->assertNotNull($notification->read_at);
    }
}
