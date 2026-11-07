<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyMessage;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StudentMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentTeacherMessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_open_a_learning_profile_only_for_a_student_in_their_assigned_class(): void
    {
        $workspace = $this->workspace();

        $this->actingAs($workspace['teacher'])
            ->get(route('teacher.students.show', $workspace['student']))
            ->assertOk()
            ->assertSee('پرونده آموزشی')
            ->assertSee($workspace['student']->name)
            ->assertSee($workspace['classroom']->title)
            ->assertSee('آخرین حضور و غیاب');

        $this->actingAs($workspace['otherTeacher'])
            ->get(route('teacher.students.show', $workspace['student']))
            ->assertNotFound();
    }

    public function test_student_can_message_an_assigned_teacher_and_read_the_reply(): void
    {
        $workspace = $this->workspace();
        $student = $workspace['student'];
        $teacher = $workspace['teacher'];
        $course = $workspace['course'];

        $this->actingAs($student)
            ->get(route('student.messages.index'))
            ->assertOk()
            ->assertSee('پیام‌های من')
            ->assertSee('پیام جدید به مدرس');

        $this->actingAs($student)
            ->post(route('student.messages.store'), [
                'target' => 'course:' . $course->id . ':' . $teacher->id,
                'category' => 'question',
                'subject' => 'سؤال درباره تکلیف هفته',
                'body' => 'لطفاً روش حل پرسش دوم را توضیح دهید.',
            ])
            ->assertRedirect(route('student.messages.index'))
            ->assertSessionHas('success');

        $message = StudentMessage::query()->sole();
        $this->assertSame($student->id, $message->student_id);
        $this->assertSame($teacher->id, $message->teacher_id);
        $this->assertSame($workspace['academy']->id, $message->academy_id);
        $this->assertSame($course->id, $message->course_id);
        $this->assertDatabaseHas('student_messages', [
            'id' => $message->id,
            'subject' => 'سؤال درباره تکلیف هفته',
            'status' => 'open',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.student-messages.index'))
            ->assertOk()
            ->assertSee('سؤال درباره تکلیف هفته')
            ->assertSee($student->name);

        $this->actingAs($teacher)
            ->patch(route('teacher.student-messages.reply', $message), [
                'response' => 'ابتدا صورت سؤال را به دو بخش تقسیم کن و هر بخش را جدا حل کن.',
            ])
            ->assertRedirect(route('teacher.student-messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_messages', [
            'id' => $message->id,
            'status' => 'responded',
            'responded_by' => $teacher->id,
            'response' => 'ابتدا صورت سؤال را به دو بخش تقسیم کن و هر بخش را جدا حل کن.',
        ]);

        $this->actingAs($teacher)
            ->patch(route('teacher.student-messages.close', $message))
            ->assertRedirect(route('teacher.student-messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_messages', [
            'id' => $message->id,
            'status' => 'closed',
        ]);

        $this->actingAs($student)
            ->get(route('student.messages.index'))
            ->assertOk()
            ->assertSee('پیام‌های من')
            ->assertSee('ابتدا صورت سؤال را به دو بخش تقسیم کن و هر بخش را جدا حل کن.');
    }

    public function test_student_cannot_message_a_teacher_of_another_classroom_group_in_the_same_course(): void
    {
        $workspace = $this->workspace();

        // The second teacher is attached to the same course but not to this
        // student's classroom cohort. Course-level association alone is not
        // enough to cross the teacher/class group boundary.
        $workspace['course']->teachers()->attach($workspace['otherTeacher']->id, ['is_primary' => false]);

        $this->actingAs($workspace['student'])
            ->post(route('student.messages.store'), [
                'target' => 'course:' . $workspace['course']->id . ':' . $workspace['otherTeacher']->id,
                'category' => 'question',
                'subject' => 'پیام خارج از محدوده',
                'body' => 'این پیام نباید به مدرس نامرتبط ارسال شود.',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('student_messages', 0);
    }

    public function test_teacher_cannot_open_or_reply_to_another_teachers_student_message(): void
    {
        $workspace = $this->workspace();
        $message = StudentMessage::query()->create([
            'academy_id' => $workspace['academy']->id,
            'student_id' => $workspace['student']->id,
            'teacher_id' => $workspace['teacher']->id,
            'course_id' => $workspace['course']->id,
            'category' => 'question',
            'subject' => 'پیام خصوصی دانش‌آموز',
            'body' => 'محتوای این پیام فقط برای مدرس تخصیص‌یافته است.',
            'status' => 'open',
        ]);

        $this->actingAs($workspace['otherTeacher'])
            ->get(route('teacher.student-messages.index'))
            ->assertOk()
            ->assertDontSee('پیام خصوصی دانش‌آموز');

        $this->actingAs($workspace['otherTeacher'])
            ->patch(route('teacher.student-messages.reply', $message), [
                'response' => 'پاسخ غیرمجاز',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('student_messages', [
            'id' => $message->id,
            'status' => 'open',
            'response' => null,
        ]);
    }

    public function test_teacher_loses_classroom_message_access_after_the_classroom_assignment_is_removed(): void
    {
        $workspace = $this->workspace();
        $message = StudentMessage::query()->create([
            'academy_id' => $workspace['academy']->id,
            'student_id' => $workspace['student']->id,
            'teacher_id' => $workspace['teacher']->id,
            'course_id' => $workspace['course']->id,
            'classroom_id' => $workspace['classroom']->id,
            'category' => 'question',
            'subject' => 'پیام کلاس واگذار شده',
            'body' => 'پس از لغو تخصیص کلاس نباید قابل مشاهده یا پاسخ باشد.',
            'status' => 'open',
        ]);

        $workspace['classroom']->teachers()->detach($workspace['teacher']->id);

        $this->actingAs($workspace['teacher'])
            ->get(route('teacher.student-messages.index'))
            ->assertOk()
            ->assertDontSee('پیام کلاس واگذار شده');

        $this->actingAs($workspace['teacher'])
            ->patch(route('teacher.student-messages.reply', $message), [
                'response' => 'این پاسخ نباید ثبت شود.',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('student_messages', [
            'id' => $message->id,
            'status' => 'open',
            'response' => null,
        ]);
    }

    public function test_teacher_can_broadcast_to_families_in_their_own_active_classes(): void
    {
        $workspace = $this->workspace();
        $parent = User::factory()->create(['status' => 'active']);
        $parentRole = $this->role('parent', ['dashboard.view']);
        $parent->roles()->attach($parentRole->id);
        $workspace['academy']->users()->attach($parent->id, [
            'role' => 'parent',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        DB::table('parent_student')->insert([
            'parent_id' => $parent->id,
            'student_id' => $workspace['student']->id,
            'relation' => 'parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($workspace['teacher'])
            ->get(route('teacher.communications.index'))
            ->assertOk()
            ->assertSee('همه خانواده‌های کلاس‌های من');

        $this->actingAs($workspace['teacher'])
            ->post(route('teacher.communications.store'), [
                'target' => 'all-class-parents:' . $workspace['academy']->id,
                'category' => 'announcement',
                'subject' => 'اطلاعیه ویژه خانواده‌های کلاس',
                'body' => 'این پیام برای خانواده‌های کلاس‌هایی است که به این مدرس اختصاص داده شده‌اند.',
            ])
            ->assertRedirect(route('teacher.communications.index'))
            ->assertSessionHasNoErrors();

        $message = AcademyMessage::query()->sole();
        $this->assertSame('teacher_class_parents', $message->audience);
        $this->assertSame($workspace['academy']->id, $message->academy_id);
        $this->assertSame($workspace['teacher']->id, $message->sender_id);
        $this->assertNull($message->classroom_id);

        $this->actingAs($parent)
            ->get(route('parent.messages.index'))
            ->assertOk()
            ->assertSee('اطلاعیه ویژه خانواده‌های کلاس')
            ->assertSee('این پیام برای خانواده‌های کلاس‌هایی است');
    }


    public function test_teacher_recipient_search_is_scoped_and_returns_only_twenty_rows_per_page(): void
    {
        $workspace = $this->workspace();
        $studentRole = $workspace['student']->roles()->firstOrFail();

        for ($index = 1; $index <= 23; $index++) {
            $student = User::factory()->create([
                'name' => 'مخاطب جست‌وجو ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);
            $student->roles()->attach($studentRole->id);
            $workspace['academy']->users()->attach($student->id, [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ]);
            $workspace['classroom']->students()->attach($student->id, [
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
            $student->enrollments()->create([
                'course_id' => $workspace['course']->id,
                'classroom_id' => $workspace['classroom']->id,
                'status' => 'active',
                'paid_amount' => 0,
                'started_at' => now(),
            ]);
        }

        $firstPage = $this->actingAs($workspace['teacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'direct',
                'type' => 'students',
                'q' => 'مخاطب جست‌وجو',
            ]));

        $firstPage->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('has_more', true);

        $secondPage = $this->actingAs($workspace['teacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'direct',
                'type' => 'students',
                'q' => 'مخاطب جست‌وجو',
                'page' => 2,
            ]));

        $secondPage->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('has_more', false);

        $this->actingAs($workspace['otherTeacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'direct',
                'type' => 'students',
                'q' => 'مخاطب جست‌وجو',
            ]))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_teacher_recipient_search_finds_only_linked_parents_and_assigned_classrooms(): void
    {
        $workspace = $this->workspace();
        $parent = User::factory()->create(['name' => 'والد مجاز تست', 'status' => 'active']);
        $parentRole = $this->role('parent', ['dashboard.view']);
        $parent->roles()->attach($parentRole->id);
        $workspace['academy']->users()->attach($parent->id, [
            'role' => 'parent',
            'status' => 'active',
            'joined_at' => now(),
        ]);
        DB::table('parent_student')->insert([
            'parent_id' => $parent->id,
            'student_id' => $workspace['student']->id,
            'relation' => 'parent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($workspace['teacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'direct',
                'type' => 'parents',
                'q' => 'والد مجاز',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.value', 'parent:' . $workspace['classroom']->id . ':' . $workspace['student']->id . ':' . $parent->id);

        $this->actingAs($workspace['teacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'announcement',
                'type' => 'classrooms',
                'q' => 'کلاس تست مکاتبات',
            ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.value', 'classroom:' . $workspace['classroom']->id . ':parents');

        $this->actingAs($workspace['otherTeacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'announcement',
                'type' => 'classrooms',
                'q' => 'کلاس تست مکاتبات',
            ]))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    private function workspace(): array
    {
        $owner = User::factory()->create(['status' => 'active']);
        $teacher = User::factory()->create(['status' => 'active']);
        $otherTeacher = User::factory()->create(['status' => 'active']);
        $student = User::factory()->create(['status' => 'active']);

        $ownerRole = $this->role('academy-owner', ['dashboard.view']);
        $teacherRole = $this->role('teacher', ['dashboard.view', 'student_messages.view', 'student_messages.manage', 'parent_messages.manage', 'students.view', 'attendance.view', 'assignments.view', 'exams.view']);
        $studentRole = $this->role('student', ['dashboard.view', 'student_messages.view', 'student_messages.manage']);

        $owner->roles()->attach($ownerRole->id);
        $teacher->roles()->attach($teacherRole->id);
        $otherTeacher->roles()->attach($teacherRole->id);
        $student->roles()->attach($studentRole->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست مکاتبات',
            'slug' => 'messaging-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        foreach ([[$teacher, 'teacher'], [$otherTeacher, 'teacher'], [$student, 'student']] as [$user, $role]) {
            $academy->users()->attach($user->id, [
                'role' => $role,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست مکاتبات',
            'slug' => 'messaging-course-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);
        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس تست مکاتبات',
            'code' => 'MSG-' . strtoupper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $classroom->teachers()->attach($teacher->id);
        $classroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
        $student->enrollments()->create([
            'course_id' => $course->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        return compact('owner', 'teacher', 'otherTeacher', 'student', 'academy', 'course', 'classroom');
    }

    private function role(string $slug, array $permissionNames): Role
    {
        $role = Role::query()->create([
            'name' => $slug,
            'slug' => $slug,
            'description' => $slug,
        ]);

        $permissionIds = [];
        foreach ($permissionNames as $permissionName) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $permissionName],
                ['label' => $permissionName, 'group' => explode('.', $permissionName)[0]],
            );
            $permissionIds[] = $permission->id;
        }

        $role->permissions()->sync($permissionIds);

        return $role;
    }
}
