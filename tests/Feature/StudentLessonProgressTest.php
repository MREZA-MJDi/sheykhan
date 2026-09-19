<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentLessonProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_tracks_unique_watched_video_segments(): void
    {
        $this->seed();

        $student = User::where('email', 'student1@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'from_seconds' => 0,
                'to_seconds' => 450,
            ])
            ->assertOk()
            ->assertJsonPath('progress_percent', 50);

        // Replaying an already watched range must not increase unique coverage.
        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'from_seconds' => 0,
                'to_seconds' => 450,
            ])
            ->assertOk()
            ->assertJsonPath('seconds_watched', 450);

        // A very large jump is treated as a seek; it only accepts a small heartbeat tail.
        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'from_seconds' => 450,
                'to_seconds' => 900,
            ])
            ->assertOk()
            ->assertJsonPath('progress_percent', 50.56)
            ->assertJsonPath('seconds_watched', 455)
            ->assertJsonPath('completed', false);

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'from_seconds' => 895,
                'to_seconds' => 900,
                'completed' => true,
            ])
            ->assertOk()
            ->assertJsonPath('seconds_watched', 455)
            ->assertJsonPath('completed', false);

        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'seconds_watched' => 455,
            'last_position_seconds' => 900,
        ]);
    }

    public function test_student_without_enrollment_can_preview_free_lesson_but_not_paid_lesson(): void
    {
        $this->seed();

        $student = User::where('email', 'student3@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $lessons = $course->sections()->firstOrFail()->lessons()->orderBy('sort_order')->get();

        $freeLesson = $lessons->first();
        $paidLesson = $lessons->get(1);

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $freeLesson), [
                'from_seconds' => 0,
                'to_seconds' => 100,
            ])
            ->assertOk();

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $paidLesson), [
                'from_seconds' => 0,
                'to_seconds' => 100,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $freeLesson->id,
            'user_id' => $student->id,
        ]);

        $this->assertDatabaseMissing('lesson_progress', [
            'lesson_id' => $paidLesson->id,
            'user_id' => $student->id,
        ]);
    }
}
