<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerSecretaryTeachingToolsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_class_assignment_with_private_pdf_handout(): void
    {
        Storage::fake('local');
        [$owner, $academy, $course, $classroom, $teacher] = $this->workspace();

        $this->actingAs($owner)
            ->post(route('owner.assignments.store', $academy), [
                'classroom_id' => $classroom->id,
                'title' => 'تمرین فصل پنجم',
                'instructions' => 'تمرین‌های ۱ تا ۵ را حل کنید.',
                'due_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
                'max_score' => 20,
                'status' => 'published',
                'handout' => UploadedFile::fake()->create('homework.pdf', 40, 'application/pdf'),
            ])
            ->assertRedirect();

        $assignment = Assignment::query()
            ->where('course_id', $course->id)
            ->where('classroom_id', $classroom->id)
            ->where('title', 'تمرین فصل پنجم')
            ->firstOrFail();

        $this->assertSame((int) $teacher->id, (int) $assignment->teacher_id);
        $this->assertSame('published', $assignment->status);

        $handout = Media::query()->where('original_name', 'homework.pdf')->firstOrFail();
        $this->assertSame('private', $handout->visibility);
        $this->assertSame('local', $handout->disk);
        $this->assertDatabaseHas('media_attachments', [
            'media_id' => $handout->id,
            'mediable_type' => Assignment::class,
            'mediable_id' => $assignment->id,
            'collection' => 'assignment-handout',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.assignments.show', [$academy, $assignment]))
            ->assertOk()
            ->assertSee('تمرین فصل پنجم')
            ->assertSee('homework.pdf');
    }

    public function test_assignment_creation_rejects_a_class_without_one_active_teacher(): void
    {
        [$owner, $academy, $course] = $this->workspace();
        $unassignedClass = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس بدون مدرس',
            'code' => 'NO-TEACHER-' . Str::upper(Str::random(5)),
            'capacity' => 20,
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->post(route('owner.assignments.store', $academy), [
                'classroom_id' => $unassignedClass->id,
                'title' => 'تکلیف نباید ساخته شود',
                'instructions' => 'یک دستورالعمل',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('classroom_id');

        $this->assertDatabaseMissing('assignments', [
            'course_id' => $course->id,
            'classroom_id' => $unassignedClass->id,
            'title' => 'تکلیف نباید ساخته شود',
        ]);
    }

    public function test_owner_can_grade_a_submission_through_the_shared_assessment_service(): void
    {
        [$owner, $academy, $course, $classroom, $teacher] = $this->workspace();
        $student = User::factory()->create([
            'name' => 'دانش‌آموز تحویل‌دهنده',
            'email' => 'student-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);
        $studentRole = Role::query()->create([
            'name' => 'دانش‌آموز',
            'slug' => 'student',
            'description' => 'Student',
        ]);
        $student->roles()->attach($studentRole->id);
        $academy->users()->attach($student->id, [
            'role' => 'student',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'teacher_id' => $teacher->id,
            'title' => 'تکلیف قابل تصحیح',
            'instructions' => 'پاسخ را بنویس.',
            'max_score' => 20,
            'status' => 'published',
        ]);
        $submission = AssignmentSubmission::query()->create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'content' => 'پاسخ دانش‌آموز',
            'submitted_at' => now(),
        ]);

        $this->actingAs($owner)
            ->patch(route('owner.assignments.grade', [$academy, $assignment, $submission]), [
                'score' => 18,
                'feedback' => 'راه‌حل خوب است؛ یک مرحله را کامل‌تر توضیح بده.',
            ])
            ->assertRedirect(route('owner.assignments.show', [$academy, $assignment]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assignment_submissions', [
            'id' => $submission->id,
            'score' => 18,
            'graded_by' => $owner->id,
        ]);
        $this->assertNotNull($submission->fresh()->graded_at);
    }

    public function test_owner_can_create_real_jitsi_schedule_and_overlapping_slot_is_rejected(): void
    {
        [$owner, $academy, , $classroom] = $this->workspace();

        $this->actingAs($owner)
            ->post(route('owner.schedules.store', [$academy, $classroom]), [
                'weekday' => 6,
                'start_time' => '10:00',
                'end_time' => '11:00',
                'room' => 'کلاس آنلاین',
                'provider' => 'Jitsi',
                'meeting_url' => '',
            ])
            ->assertRedirect(route('owner.schedules.index', $academy))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('class_schedules', 1);
        $schedule = $classroom->schedules()->firstOrFail();
        $this->assertSame('Jitsi', $schedule->provider);
        $this->assertStringStartsWith('https://meet.jit.si/Sheykhan-', $schedule->meeting_url);

        $this->actingAs($owner)
            ->post(route('owner.schedules.store', [$academy, $classroom]), [
                'weekday' => 6,
                'start_time' => '10:30',
                'end_time' => '11:30',
                'provider' => 'Jitsi',
                'meeting_url' => '',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('class_schedules', 1);

        $this->actingAs($owner)
            ->get(route('owner.schedules.index', $academy))
            ->assertOk()
            ->assertSee($classroom->title)
            ->assertSee('meet.jit.si/Sheykhan-', false);
    }

    private function workspace(): array
    {
        $owner = User::factory()->create([
            'name' => 'منشی آموزشگاه',
            'email' => 'owner-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);
        $teacher = User::factory()->create([
            'name' => 'مدرس فعال',
            'email' => 'teacher-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);

        $ownerRole = Role::query()->create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permissionIds = [];
        foreach (['courses.manage', 'courses.view', 'live_classes.view', 'media.view'] as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $name, 'group' => explode('.', $name)[0]],
            );
            $permissionIds[] = $permission->id;
        }
        $ownerRole->permissions()->sync($permissionIds);
        $owner->roles()->attach($ownerRole->id);

        $teacherRole = Role::query()->create([
            'name' => 'مدرس',
            'slug' => 'teacher',
            'description' => 'Teacher',
        ]);
        $teacher->roles()->attach($teacherRole->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی گردش کار',
            'slug' => 'academy-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
        $academy->users()->attach($teacher->id, [
            'role' => 'teacher',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره گردش کار',
            'slug' => 'course-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now(),
        ]);
        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس گردش کار',
            'code' => 'CLASS-' . Str::upper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $classroom->teachers()->attach($teacher->id);

        return [$owner, $academy, $course, $classroom, $teacher];
    }
}
