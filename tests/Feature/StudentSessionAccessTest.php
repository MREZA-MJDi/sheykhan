<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LiveClass;
use App\Models\Media;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentSessionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_lamp_state_based_on_recording_release(): void
    {
        $this->seed();

        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $classroom = $course->classrooms()->firstOrFail();
        $student = User::where('email', 'student1@sheykhan.test')->firstOrFail();

        $future = $this->makeSession($course, $classroom, 'جلسه آینده', now()->addDay(), 'scheduled', now()->addDays(2));
        $unreleased = $this->makeSession($course, $classroom, 'جلسه برگزارشده بدون انتشار', now()->subDay(), 'completed', now()->addHour());
        $released = $this->makeSession($course, $classroom, 'جلسه ضبط‌شده منتشرشده', now()->subDays(2), 'completed', now()->subDay());

        $sessions = app(StudentDashboardService::class)->build($student)['sessions'];

        $byId = collect($sessions)->keyBy('id');

        $this->assertFalse($byId[$future->id]['available']);
        $this->assertSame('خاموش', $byId[$future->id]['lamp']);

        $this->assertFalse($byId[$unreleased->id]['available']);
        $this->assertSame('خاموش', $byId[$unreleased->id]['lamp']);

        $this->assertTrue($byId[$released->id]['available']);
        $this->assertSame('روشن', $byId[$released->id]['lamp']);
        $this->assertSame(route('media.download', $released->recording), $byId[$released->id]['href']);
    }

    public function test_recording_download_requires_release_and_student_scope(): void
    {
        Storage::fake('local');
        $this->seed();

        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $classroom = $course->classrooms()->firstOrFail();

        $inside = User::where('email', 'student1@sheykhan.test')->firstOrFail();
        $outside = User::where('email', 'student3@sheykhan.test')->firstOrFail();

        // Same course, different classroom: recording access must still be isolated.
        $outside->enrollments()->updateOrCreate(
            ['course_id' => $course->id],
            ['status' => 'active', 'started_at' => now()]
        );

        $released = $this->makeSession($course, $classroom, 'ضبط منتشرشده', now()->subDay(), 'completed', now()->subHour());
        $unreleased = $this->makeSession($course, $classroom, 'ضبط منتشرنشده', now()->subDay(), 'completed', now()->addHour());

        $this->actingAs($inside)
            ->get(route('media.download', $released->recording))
            ->assertOk();

        $this->actingAs($inside)
            ->get(route('media.download', $unreleased->recording))
            ->assertForbidden();

        $this->actingAs($outside)
            ->get(route('media.download', $released->recording))
            ->assertForbidden()
            ->assertSee('این عملیات برای حساب شما مجاز نیست.');
    }

    private function makeSession(
        Course $course,
        $classroom,
        string $title,
        \DateTimeInterface $scheduledAt,
        string $status,
        \DateTimeInterface $releasedAt,
    ): LiveClass {
        $path = 'recordings/' . str()->uuid() . '.mp4';
        Storage::disk('local')->put($path, 'demo-recording');

        $media = Media::create([
            'uploaded_by' => User::where('email', 'owner@sheykhan.test')->value('id'),
            'disk' => 'local',
            'path' => $path,
            'original_name' => $title . '.mp4',
            'file_name' => basename($path),
            'mime_type' => 'video/mp4',
            'extension' => 'mp4',
            'size' => Storage::disk('local')->size($path),
            'checksum' => hash('sha256', Storage::disk('local')->get($path)),
            'visibility' => 'private',
            'collection' => 'recording',
            'metadata' => ['demo' => true],
            'status' => 'active',
        ]);

        $session = LiveClass::create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => User::where('email', 'teacher1@sheykhan.test')->value('id'),
            'recording_media_id' => $media->id,
            'title' => $title,
            'provider' => 'internal',
            'scheduled_at' => $scheduledAt,
            'recording_released_at' => $releasedAt,
            'duration_minutes' => 90,
            'status' => $status,
        ]);

        $media->attachments()->create([
            'mediable_type' => LiveClass::class,
            'mediable_id' => $session->id,
            'collection' => 'recording',
            'sort_order' => 0,
            'is_featured' => false,
        ]);

        return $session;
    }
}
