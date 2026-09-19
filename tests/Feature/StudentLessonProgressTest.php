<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentLessonProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_report_video_progress(): void
    {
        $this->seed();

        $student = User::where('email', 'student1@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'seconds_watched' => 450,
            ])
            ->assertOk()
            ->assertJsonPath('progress_percent', 50);

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'seconds_watched' => 900,
                'completed' => true,
            ])
            ->assertOk()
            ->assertJsonPath('progress_percent', 100)
            ->assertJsonPath('completed', true);

        $this->assertDatabaseHas('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
            'progress_percent' => 100,
            'seconds_watched' => 900,
        ]);
    }

    public function test_student_without_course_access_cannot_report_progress(): void
    {
        $this->seed();

        $student = User::where('email', 'student3@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'web-programming-foundation')->firstOrFail();
        $lesson = $course->sections()->firstOrFail()->lessons()->firstOrFail();

        $this->actingAs($student)
            ->postJson(route('student.lessons.progress.store', $lesson), [
                'seconds_watched' => 100,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('lesson_progress', [
            'lesson_id' => $lesson->id,
            'user_id' => $student->id,
        ]);
    }
}
