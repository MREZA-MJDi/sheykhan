<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherCourseBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_manage_content_only_inside_an_assigned_course(): void
    {
        $this->seed();

        $teacherOne = User::where('email', 'teacher1@sheykhan.test')->firstOrFail();
        $teacherTwoCourse = Course::whereHas('teachers', fn ($query) => $query
            ->where('users.id', User::where('email', 'teacher2@sheykhan.test')->value('id')))
            ->firstOrFail();

        $teacherOneCourse = Course::whereHas('teachers', fn ($query) => $query
            ->where('users.id', $teacherOne->id))
            ->firstOrFail();

        $this->actingAs($teacherOne)
            ->post(route('teacher.courses.sections.store', $teacherOneCourse), [
                'title' => 'سرفصل تست',
                'description' => 'ساخت سرفصل توسط مدرس مجاز.',
            ])
            ->assertRedirect();

        $this->actingAs($teacherOne)
            ->post(route('teacher.courses.sections.store', $teacherTwoCourse), [
                'title' => 'نباید ساخته شود',
            ])
            ->assertForbidden();
    }
}
