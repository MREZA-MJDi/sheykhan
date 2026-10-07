<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_render_the_portal_entry_pages_without_cross_role_redirects(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->with('sections.lessons')->firstOrFail();
        $lesson = $course->sections->flatMap(fn ($section) => $section->lessons)->firstOrFail();
        $assignment = Assignment::where('course_id', $course->id)->firstOrFail();
        $exam = Exam::where('course_id', $course->id)->firstOrFail();

        $routes = [
            ['student.dashboard', []],
            ['student.courses.index', []],
            ['student.courses.show', [$course]],
            ['student.lessons.show', [$lesson]],
            ['student.assignments.index', []],
            ['student.assignments.show', [$assignment]],
            ['student.exams.index', []],
            ['student.exams.show', [$exam]],
            ['student.live-classes.index', []],
            ['student.attendance.index', []],
            ['student.results.index', []],
            ['student.achievements.index', []],
            ['student.notes.index', []],
            ['student.profile.edit', []],
            ['student.resources.index', []],
        ];

        foreach ($routes as [$routeName, $parameters]) {
            $this->actingAs($student)
                ->get(route($routeName, $parameters))
                ->assertOk();
        }
    }
}
