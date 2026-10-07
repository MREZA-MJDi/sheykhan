<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\StudentDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentDashboardBackendContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_dashboard_contract_contains_scoped_courses_resources_and_recent_results(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();

        $payload = app(StudentDashboardService::class)->build($student);

        $this->assertArrayHasKey('courses', $payload);
        $this->assertArrayHasKey('resources', $payload);
        $this->assertArrayHasKey('recentResults', $payload);
        $this->assertArrayHasKey('pendingAssignments', $payload);
        $this->assertArrayHasKey('nextLesson', $payload);
        $this->assertArrayHasKey('nextLiveClass', $payload);
        $this->assertArrayHasKey('completedLessonsCount', $payload);
        $this->assertArrayHasKey('totalLessonsCount', $payload);
        $this->assertArrayHasKey('studyMinutesLast7Days', $payload);
        $this->assertArrayHasKey('studyStreak', $payload);
        $this->assertArrayHasKey('studyWeek', $payload);
        $this->assertArrayHasKey('achievements', $payload);
        $this->assertArrayHasKey('achievementCount', $payload);
        $this->assertCount(7, $payload['studyWeek']);

        $course = $payload['courses']->firstWhere('slug', 'math-foundation-7');

        $this->assertNotNull($course);
        $this->assertTrue($course->relationLoaded('academy'));
        $this->assertSame('آکادمی شیخان', $course->academy->name);

        foreach ($payload['recentResults'] as $result) {
            $this->assertNotNull($result->occurred_at);
            $this->assertNotSame('', $result->status_label ?? '');
        }
    }

    public function test_pending_assignment_kpi_is_not_limited_to_the_dashboard_list(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $classroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        for ($index = 1; $index <= 8; $index++) {
            Assignment::create([
                'course_id' => $course->id,
                'classroom_id' => $classroom->id,
                'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
                'title' => 'تکلیف پیگیری ' . $index,
                'instructions' => 'تکلیف تستی',
                'due_at' => now()->addDays($index),
                'max_score' => 100,
                'status' => 'published',
            ]);
        }

        $payload = app(StudentDashboardService::class)->build($student);

        $this->assertSame(8, $payload['pendingAssignments']);
        $this->assertCount(6, $payload['assignments']);
    }

    public function test_classroom_specific_assignment_is_not_visible_to_a_course_peer_outside_the_classroom(): void
    {
        $this->seed();

        $student = User::where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $otherStudent = User::where('email', 'student.parsa@sheykhan.test')->firstOrFail();
        $course = Course::where('slug', 'math-foundation-7')->firstOrFail();
        $targetClassroom = $course->classrooms()->where('code', 'MATH7-01')->firstOrFail();

        $otherClassroom = Classroom::create([
            'academy_id' => $course->academy_id,
            'course_id' => $course->id,
            'grade_id' => $targetClassroom->grade_id,
            'academic_year_id' => $targetClassroom->academic_year_id,
            'title' => 'کلاس ریاضی هفتم - گروه ۲',
            'code' => 'MATH7-02',
            'description' => 'کلاس تستی مستقل',
            'capacity' => 20,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonths(2),
        ]);

        $otherClassroom->students()->syncWithoutDetaching([
            $otherStudent->id => [
                'status' => 'active',
                'enrolled_at' => now(),
            ],
        ]);

        CourseEnrollment::updateOrCreate(
            [
                'course_id' => $course->id,
                'student_id' => $otherStudent->id,
            ],
            [
                'status' => 'active',
                'started_at' => now(),
            ]
        );

        Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => $otherClassroom->id,
            'teacher_id' => User::where('email', 'teacher.math@sheykhan.test')->value('id'),
            'title' => 'تکلیف فقط گروه دو',
            'instructions' => 'نباید برای دانش‌آموز گروه یک دیده شود.',
            'due_at' => now()->addDay(),
            'max_score' => 100,
            'status' => 'published',
        ]);

        $payload = app(StudentDashboardService::class)->build($student);

        $this->assertFalse(
            $payload['assignments']->contains('title', 'تکلیف فقط گروه دو')
        );
    }
}
