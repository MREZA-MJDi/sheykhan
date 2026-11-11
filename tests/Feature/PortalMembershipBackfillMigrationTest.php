<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PortalMembershipBackfillMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_backfills_only_missing_memberships_for_active_assigned_portal_users(): void
    {
        $owner = User::factory()->create(['status' => 'active']);
        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-membership-backfill',
            'status' => 'active',
        ]);
        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست',
            'slug' => 'membership-backfill-course',
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
        ]);

        $teacherRole = Role::query()->create([
            'name' => 'مدرس',
            'slug' => 'teacher',
            'description' => 'Teacher',
        ]);
        $studentRole = Role::query()->create([
            'name' => 'دانش‌آموز',
            'slug' => 'student',
            'description' => 'Student',
        ]);

        $teacher = User::factory()->create(['status' => 'active']);
        $teacher->roles()->attach($teacherRole->id);
        DB::table('course_teacher')->insert([
            'course_id' => $course->id,
            'teacher_id' => $teacher->id,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $student = User::factory()->create(['status' => 'active']);
        $student->roles()->attach($studentRole->id);
        CourseEnrollment::query()->create([
            'course_id' => $course->id,
            'student_id' => $student->id,
            'status' => 'active',
        ]);

        $archivedTeacher = User::factory()->create(['status' => 'active']);
        $archivedTeacher->roles()->attach($teacherRole->id);
        DB::table('course_teacher')->insert([
            'course_id' => $course->id,
            'teacher_id' => $archivedTeacher->id,
            'is_primary' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('academy_user')->insert([
            'academy_id' => $academy->id,
            'user_id' => $archivedTeacher->id,
            'role' => 'teacher',
            'status' => 'archived',
            'joined_at' => now()->subMonth(),
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);

        $migration = require database_path('migrations/2026_11_11_000001_backfill_active_academy_memberships_for_assigned_users.php');
        $migration->up();

        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $teacher->id,
            'role' => 'teacher',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $student->id,
            'role' => 'student',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('academy_user', [
            'academy_id' => $academy->id,
            'user_id' => $archivedTeacher->id,
            'role' => 'teacher',
            'status' => 'archived',
        ]);
    }
}
