<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\FinancialTransaction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\TestingRefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_idempotency_key_does_not_create_duplicate_enrollment_or_payment(): void
    {
        [$owner, $academy, $course, $student] = $this->makeWorkspace();
        $key = (string) Str::uuid();

        $payload = [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'paid_amount' => 100000,
            'idempotency_key' => $key,
        ];

        $this->actingAs($owner)
            ->post(route('owner.people.enroll-student', $academy), $payload)
            ->assertSessionHas('success');

        $this->actingAs($owner)
            ->post(route('owner.people.enroll-student', $academy), $payload)
            ->assertSessionHas('success');

        $this->assertDatabaseCount('course_enrollments', 1);
        $this->assertDatabaseCount('financial_transactions', 1);
        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
            'payment_status' => 'partial',
            'paid_amount' => 100000,
        ]);
    }

    public function test_duplicate_enrollment_with_same_final_state_is_safe_even_with_a_new_key(): void
    {
        [$owner, $academy, $course, $student] = $this->makeWorkspace();

        foreach ([(string) Str::uuid(), (string) Str::uuid()] as $key) {
            $this->actingAs($owner)
                ->post(route('owner.people.enroll-student', $academy), [
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'paid_amount' => 100000,
                    'idempotency_key' => $key,
                ])
                ->assertSessionHas('success');
        }

        $this->assertDatabaseCount('course_enrollments', 1);
        $this->assertDatabaseCount('financial_transactions', 1);
    }

    public function test_owner_cannot_overfill_a_classroom(): void
    {
        [$owner, $academy, $course, $student] = $this->makeWorkspace();

        $classroom = Classroom::create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس محدود',
            'code' => 'LIMIT-1',
            'capacity' => 1,
            'status' => 'active',
        ]);

        $secondStudent = User::factory()->create(['name' => 'دانش‌آموز دوم', 'email' => 'student2@test.local']);
        $studentRole = Role::query()->where('slug', 'student')->firstOrFail();
        $secondStudent->roles()->attach($studentRole->id);
        $academy->users()->attach($secondStudent->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->actingAs($owner)->post(route('owner.people.enroll-student', $academy), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'paid_amount' => 100000,
            'idempotency_key' => (string) Str::uuid(),
        ])->assertSessionHas('success');

        $this->actingAs($owner)->post(route('owner.people.enroll-student', $academy), [
            'student_id' => $secondStudent->id,
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'paid_amount' => 100000,
            'idempotency_key' => (string) Str::uuid(),
        ])->assertSessionHasErrors('classroom_id');

        $this->assertDatabaseCount('classroom_student', 1);
    }

    public function test_owner_cannot_enroll_a_student_from_another_academy(): void
    {
        [$owner, $academy, $course] = $this->makeWorkspace();
        $foreignStudent = User::factory()->create(['email' => 'foreign@test.local']);

        $this->actingAs($owner)->post(route('owner.people.enroll-student', $academy), [
            'student_id' => $foreignStudent->id,
            'course_id' => $course->id,
            'paid_amount' => 100000,
            'idempotency_key' => (string) Str::uuid(),
        ])->assertSessionHasErrors('student_id');

        $this->assertDatabaseCount('course_enrollments', 0);
    }

    private function makeWorkspace(): array
    {
        $owner = User::factory()->create(['email' => 'owner@test.local']);
        $student = User::factory()->create(['email' => 'student@test.local']);

        $ownerRole = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $ownerPermissions = Permission::insertGetId([
            'name' => 'enrollments.manage',
            'label' => 'Manage enrollments',
            'group' => 'enrollments',
        ]);

        $coursesPermission = Permission::insertGetId([
            'name' => 'courses.manage',
            'label' => 'Manage courses',
            'group' => 'courses',
        ]);

        $ownerRole->permissions()->attach([$ownerPermissions, $coursesPermission]);
        $owner->roles()->attach($ownerRole->id);

        $studentRole = Role::create([
            'name' => 'دانش‌آموز',
            'slug' => 'student-' . Str::lower(Str::random(6)),
            'description' => 'Student',
        ]);
        $student->roles()->attach($studentRole->id);

        $academy = Academy::create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'test-academy-' . Str::lower(Str::random(6)),
            'status' => 'active',
        ]);

        $academy->users()->attach($owner->id, [
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $academy->users()->attach($student->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $course = Course::create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست',
            'slug' => 'test-course-' . Str::lower(Str::random(6)),
            'status' => 'published',
            'access_type' => 'paid',
            'price' => 200000,
            'published_at' => now(),
        ]);

        return [$owner, $academy, $course, $student];
    }
}