<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherLessonResourceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_upload_creates_student_resource_for_the_same_lesson_scope(): void
    {
        Storage::fake('local');
        $this->seed();

        $teacher = User::where('email', 'teacher1@sheykhan.test')->firstOrFail();
        $student = User::where('email', 'student1@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
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
        $this->assertSame('active', $resource->status);

        $this->actingAs($student)
            ->get(route('student.resources.index'))
            ->assertOk()
            ->assertSee('lesson-handout.pdf');

        $this->actingAs($student)
            ->get(route('student.resources.download', $resource))
            ->assertForbidden()
            ->assertSee('فقط برای مشاهده ارائه شده');

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

    public function test_publishing_a_lesson_releases_its_existing_resources(): void
    {
        $this->seed();

        $teacher = User::where('email', 'teacher1@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $media = $course->media()->firstOrFail();

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

        $this->assertSame('active', $resource->status);
        $this->assertNotNull($resource->release_at);
        $this->assertTrue($resource->release_at->isPast());
    }
}
