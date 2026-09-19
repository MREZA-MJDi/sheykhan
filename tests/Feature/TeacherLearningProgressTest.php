<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherLearningProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_only_view_progress_for_assigned_courses(): void
    {
        $this->seed();

        $teacherOne = User::where('email', 'teacher1@sheykhan.test')->firstOrFail();
        $teacherTwo = User::where('email', 'teacher2@sheykhan.test')->firstOrFail();

        $teacherOneCourse = Course::whereHas('teachers', fn ($query) => $query->whereKey($teacherOne->id))
            ->firstOrFail();

        $teacherTwoCourse = Course::whereHas('teachers', fn ($query) => $query->whereKey($teacherTwo->id))
            ->whereDoesntHave('teachers', fn ($query) => $query->whereKey($teacherOne->id))
            ->firstOrFail();

        $this->actingAs($teacherOne)
            ->get(route('teacher.courses.progress', $teacherOneCourse))
            ->assertOk();

        $this->actingAs($teacherOne)
            ->get(route('teacher.courses.progress', $teacherTwoCourse))
            ->assertForbidden();
    }
}
