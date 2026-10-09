<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\TeacherAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeacherAccessIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_cannot_use_a_course_from_an_academy_where_membership_is_archived(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $this->assertFalse(
            app(TeacherAccessService::class)->canTeachCourse($teacher, $course)
        );
    }

    public function test_archived_academy_membership_blocks_viewing_exam_attempts_even_when_teacher_is_active_elsewhere(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $exam = Exam::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'آزمون آموزشگاه قبلی',
            'duration_minutes' => 30,
            'attempts_allowed' => 1,
            'status' => 'published',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.attempts', $exam))
            ->assertForbidden();
    }

    public function test_archived_academy_membership_blocks_grading_old_assignment_even_when_teacher_is_active_elsewhere(): void
    {
        [$teacher, $course] = $this->teacherWithArchivedCourseMembership();

        $assignment = Assignment::create([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف قدیمی',
            'max_score' => 20,
            'status' => 'published',
        ]);

        $student = User::factory()->create();
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => 'پاسخ',
            'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->patch(route('teacher.assignments.submissions.update', [$assignment, $submission]), [
                'score' => 18,
                'feedback' => 'خوب بود',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('assignment_submissions', [
            'id' => $submission->id,
            'score' => 18,
        ]);
    }

    private function teacherWithArchivedCourseMembership(): array
    {
        $role = Role::create([
            'name' => 'مدرس',
            'slug' => 'teacher',
            'description' => 'Teacher',
        ]);

        $permission = Permission::create([
            'name' => 'assignments.manage',
            'label' => 'Manage assignments',
            'group' => 'assignments',
        ]);
        $examPermission = Permission::create([
            'name' => 'exams.view',
            'label' => 'View exams',
            'group' => 'exams',
        ]);

        $role->permissions()->attach([$permission->id, $examPermission->id]);

        $teacher = User::factory()->create([
            'email' => 'teacher-' . Str::random(8) . '@test.local',
        ]);
        $teacher->roles()->attach($role->id);

        $activeAcademy = Academy::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'آکادمی فعال',
            'slug' => 'active-' . Str::random(8),
            'status' => 'active',
        ]);

        $teacher->academies()->attach($activeAcademy->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $archivedAcademy = Academy::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'آکادمی قبلی',
            'slug' => 'archived-' . Str::random(8),
            'status' => 'active',
        ]);

        $teacher->academies()->attach($archivedAcademy->id, [
            'role' => 'teacher',
            'status' => 'archived',
            'joined_at' => now()->subMonths(2),
        ]);

        $course = Course::create([
            'academy_id' => $archivedAcademy->id,
            'created_by' => $teacher->id,
            'title' => 'دوره قبلی',
            'slug' => 'old-course-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        $course->teachers()->attach($teacher->id);

        return [$teacher, $course];
    }
}
