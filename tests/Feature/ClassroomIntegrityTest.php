<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassroomIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_persian_classroom_datetime_is_not_silently_accepted_on_create(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $course = Course::query()->where('slug', 'math-foundation-7')->firstOrFail();

        $this->actingAs($teacher)
            ->post(route('teacher.classrooms.store'), [
                'course_id' => $course->id,
                'title' => 'کلاس با تاریخ نامعتبر',
                'starts_at' => '۱۴۰۳/۱۳/۴۰ ۲۹:۹۹',
                'ends_at' => '۱۴۰۳/۱۳/۴۱ ۳۰:۹۹',
            ])
            ->assertSessionHasErrors(['starts_at', 'ends_at']);

        $this->assertDatabaseMissing('classrooms', [
            'title' => 'کلاس با تاریخ نامعتبر',
        ]);
    }

    public function test_invalid_classroom_datetime_is_not_silently_accepted_on_update(): void
    {
        $this->seed();

        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();
        $classroom = Classroom::query()
            ->whereHas('teachers', fn ($query) => $query->whereKey($teacher->id))
            ->firstOrFail();

        $this->actingAs($teacher)
            ->put(route('teacher.classrooms.update', $classroom), [
                'course_id' => $classroom->course_id,
                'title' => $classroom->title,
                'capacity' => $classroom->capacity,
                'status' => $classroom->status,
                'starts_at' => '۱۴۰۳/۱۳/۴۰ ۲۹:۹۹',
                'ends_at' => '۱۴۰۳/۱۳/۴۱ ۳۰:۹۹',
            ])
            ->assertSessionHasErrors(['starts_at', 'ends_at']);
    }
}
