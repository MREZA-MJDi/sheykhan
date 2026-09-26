<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_and_view_a_classroom_with_an_assigned_teacher(): void
    {
        [$owner, $academy, $course, $teacher] = $this->workspace();

        $payload = [
            'course_id' => $course->id,
            'teacher_ids' => [$teacher->id],
            'title' => 'کلاس وب مقدماتی',
            'code' => 'WEB-101',
            'capacity' => 20,
            'status' => 'active',
        ];

        $response = $this->actingAs($owner)
            ->post(route('owner.classrooms.store', $academy), $payload);

        $classroom = Classroom::query()->firstOrFail();

        $response
            ->assertRedirect(route('owner.classrooms.show', [$academy, $classroom]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('classrooms', [
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس وب مقدماتی',
            'code' => 'WEB-101',
            'capacity' => 20,
        ]);

        $this->assertDatabaseHas('classroom_teacher', [
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($owner)
            ->get(route('owner.classrooms.show', [$academy, $classroom]))
            ->assertOk()
            ->assertViewHas('classroom', fn (Classroom $item) => $item->is($classroom))
            ->assertViewHas('activities');
    }

    public function test_owner_cannot_create_classroom_with_teacher_not_assigned_to_the_course(): void
    {
        [$owner, $academy, $course] = $this->workspace();

        $otherTeacher = User::factory()->create(['email' => 'other-teacher@test.local']);
        $teacherRole = Role::query()->where('slug', 'teacher')->firstOrFail();
        $otherTeacher->roles()->attach($teacherRole->id);
        $academy->users()->attach($otherTeacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('owner.classrooms.store', $academy), [
                'course_id' => $course->id,
                'teacher_ids' => [$otherTeacher->id],
                'title' => 'کلاس نامعتبر',
                'code' => 'INVALID-1',
                'capacity' => 20,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('teacher_ids');

        $this->assertDatabaseCount('classrooms', 0);
    }

    public function test_owner_cannot_reduce_capacity_below_active_students(): void
    {
        [$owner, $academy, $course, $teacher] = $this->workspace();

        $classroom = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس ظرفیت',
            'code' => 'CAP-101',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classroom->teachers()->attach($teacher->id);

        for ($i = 1; $i <= 3; $i++) {
            $student = User::factory()->create(['email' => 'student-' . $i . '@test.local']);
            $studentRole = Role::query()->where('slug', 'student')->firstOrFail();
            $student->roles()->attach($studentRole->id);
            $academy->users()->attach($student->id, [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            $classroom->students()->attach($student->id, [
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        }

        $this->actingAs($owner)
            ->patch(route('owner.classrooms.update', [$academy, $classroom]), [
                'course_id' => $course->id,
                'teacher_ids' => [$teacher->id],
                'title' => $classroom->title,
                'code' => $classroom->code,
                'capacity' => 2,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('capacity');

        $this->assertDatabaseHas('classrooms', [
            'id' => $classroom->id,
            'capacity' => 20,
        ]);
    }

    public function test_owner_cannot_access_a_classroom_belonging_to_another_academy(): void
    {
        [$owner, $academy] = $this->workspace();

        $foreignOwner = User::factory()->create(['email' => 'foreign-owner@test.local']);
        $foreignAcademy = Academy::create([
            'owner_id' => $foreignOwner->id,
            'name' => 'آکادمی دیگر',
            'slug' => 'foreign-' . Str::random(8),
            'status' => 'active',
        ]);

        $foreignCourse = Course::create([
            'academy_id' => $foreignAcademy->id,
            'created_by' => $foreignOwner->id,
            'title' => 'دوره دیگر',
            'slug' => 'foreign-course-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now(),
        ]);

        $classroom = Classroom::create([
            'academy_id' => $foreignAcademy->id,
            'course_id' => $foreignCourse->id,
            'title' => 'کلاس دیگر',
            'code' => 'FOREIGN-1',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.classrooms.show', [$academy, $classroom]))
            ->assertNotFound();
    }

    public function test_owner_classroom_detail_builds_activity_from_real_classroom_events(): void
    {
        [$owner, $academy, $course, $teacher, $student] = $this->workspace();

        $classroom = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس فعالیت',
            'code' => 'ACT-101',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classroom->teachers()->attach($teacher->id);
        $classroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        CourseEnrollment::create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
            'payment_status' => 'paid',
            'price_amount' => 0,
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        Assignment::create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تمرین HTML',
            'status' => 'published',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('owner.classrooms.show', [$academy, $classroom]))
            ->assertOk();

        $activities = $response->viewData('activities');

        $this->assertTrue($activities->contains(fn (array $activity) => $activity['type'] === 'assignment'));
        $this->assertTrue($activities->contains(fn (array $activity) => $activity['type'] === 'enrollment'));
    }

    private function workspace(): array
    {
        $owner = User::factory()->create(['email' => 'owner-' . Str::random(8) . '@test.local']);
        $teacher = User::factory()->create(['email' => 'teacher-' . Str::random(8) . '@test.local']);
        $student = User::factory()->create(['email' => 'student-' . Str::random(8) . '@test.local']);

        $ownerRole = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permission = Permission::create([
            'name' => 'classrooms.manage',
            'label' => 'Manage classrooms',
            'group' => 'classrooms',
        ]);

        $viewPermission = Permission::create([
            'name' => 'classrooms.view',
            'label' => 'View classrooms',
            'group' => 'classrooms',
        ]);

        $ownerRole->permissions()->attach([$permission->id, $viewPermission->id]);
        $owner->roles()->attach($ownerRole->id);

        $teacherRole = Role::create([
            'name' => 'مدرس',
            'slug' => 'teacher',
            'description' => 'Teacher',
        ]);

        $teacher->roles()->attach($teacherRole->id);

        $studentRole = Role::create([
            'name' => 'دانش‌آموز',
            'slug' => 'student',
            'description' => 'Student',
        ]);

        $student->roles()->attach($studentRole->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::random(8),
            'status' => 'active',
        ]);

        $academy->users()->attach([
            $teacher->id => [
                'role' => 'teacher',
                'status' => 'active',
                'joined_at' => now(),
            ],
            $student->id => [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ],
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست',
            'slug' => 'course-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now(),
        ]);

        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        return [$owner, $academy, $course, $teacher, $student];
    }
}
