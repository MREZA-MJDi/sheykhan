<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\AcademyMessage;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AcademyCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_broadcast_is_visible_to_active_families_in_that_academy(): void
    {
        $w = $this->workspace();

        $this->actingAs($w['owner'])
            ->get(route('owner.communications.index', $w['academy']))
            ->assertRedirect(route('owner.communication-workspace', [$w['academy'], 'tab' => 'announcements']));

        $this->actingAs($w['owner'])
            ->get(route('owner.communication-workspace', [$w['academy'], 'tab' => 'announcements']))
            ->assertOk()
            ->assertSee('ارسال اطلاعیه تازه')
            ->assertSee('همه خانواده‌های فعال آموزشگاه')
            ->assertDontSee('پیام مستقیم به دانش‌آموز ' . $w['student']->name);

        $this->actingAs($w['owner'])->post(route('owner.communications.store', $w['academy']), [
            'target' => 'all_parents',
            'category' => 'announcement',
            'subject' => 'اطلاعیه شروع ترم',
            'body' => 'جلسه نخست ترم روز شنبه برگزار می‌شود.',
        ])->assertRedirect(route('owner.communication-workspace', [$w['academy'], 'tab' => 'announcements']));

        $this->assertDatabaseHas('academy_messages', [
            'academy_id' => $w['academy']->id,
            'sender_id' => $w['owner']->id,
            'audience' => 'all_parents',
            'recipient_id' => null,
            'subject' => 'اطلاعیه شروع ترم',
        ]);

        $this->actingAs($w['parent'])->get(route('parent.messages.index'))
            ->assertOk()->assertSee('اطلاعیه شروع ترم');

        $this->actingAs($w['student'])->get(route('student.messages.index'))
            ->assertOk()->assertSee('پیام‌های من');
    }

    public function test_owner_private_message_is_visible_only_to_the_selected_parent(): void
    {
        $w = $this->workspace();

        $target = 'parent:' . $w['student']->id . ':' . $w['parent']->id;
        $this->actingAs($w['owner'])->post(route('owner.communications.store', $w['academy']), [
            'target' => $target,
            'category' => 'academic',
            'subject' => 'پیگیری اختصاصی خانواده',
            'body' => 'لطفاً گزارش آموزشی فرزندتان را بررسی کنید.',
        ])->assertRedirect(route('owner.communication-workspace', [$w['academy'], 'tab' => 'announcements']));

        $this->actingAs($w['parent'])->get(route('parent.messages.index'))
            ->assertOk()->assertSee('پیگیری اختصاصی خانواده');

        $this->actingAs($w['otherParent'])->get(route('parent.messages.index'))
            ->assertOk()->assertDontSee('پیگیری اختصاصی خانواده');
    }

    public function test_teacher_can_message_one_class_family_group_and_one_student_privately(): void
    {
        $w = $this->workspace();

        $this->actingAs($w['teacher'])
            ->get(route('teacher.communications.index'))
            ->assertOk()
            ->assertSee('ارسال پیام و اطلاعیه')
            ->assertSee('جست‌وجوی مخاطب'); 

        $this->actingAs($w['teacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'announcement',
                'type' => 'classrooms',
                'q' => 'کلاس یک',
            ]))
            ->assertOk()
            ->assertJsonPath('data.0.value', 'classroom:' . $w['classroom']->id . ':parents');

        $this->actingAs($w['teacher'])
            ->getJson(route('teacher.recipient-options', [
                'context' => 'announcement',
                'type' => 'students',
                'q' => $w['student']->name,
            ]))
            ->assertOk()
            ->assertJsonPath('data.0.value', 'student:' . $w['classroom']->id . ':' . $w['student']->id);

        $this->actingAs($w['teacher'])->post(route('teacher.communications.store'), [
            'target' => 'classroom:' . $w['classroom']->id . ':parents',
            'category' => 'schedule',
            'subject' => 'تغییر ساعت کلاس',
            'body' => 'این هفته کلاس نیم ساعت زودتر برگزار می‌شود.',
        ])->assertRedirect(route('teacher.communications.index'));

        $this->actingAs($w['parent'])->get(route('parent.messages.index'))
            ->assertOk()->assertSee('تغییر ساعت کلاس');
        $this->actingAs($w['otherParent'])->get(route('parent.messages.index'))
            ->assertOk()->assertDontSee('تغییر ساعت کلاس');

        $this->actingAs($w['teacher'])->post(route('teacher.communications.store'), [
            'target' => 'student:' . $w['classroom']->id . ':' . $w['student']->id,
            'category' => 'important',
            'subject' => 'پیام خصوصی دانش‌آموز',
            'body' => 'این پیام فقط برای حساب همین دانش‌آموز است.',
        ])->assertRedirect(route('teacher.communications.index'));

        $this->actingAs($w['student'])->get(route('student.messages.index'))
            ->assertOk()->assertSee('پیام خصوصی دانش‌آموز');
        $this->actingAs($w['otherStudent'])->get(route('student.messages.index'))
            ->assertOk()->assertDontSee('پیام خصوصی دانش‌آموز');
    }

    public function test_teacher_cannot_send_to_a_classroom_they_do_not_teach(): void
    {
        $w = $this->workspace();

        $this->actingAs($w['teacher'])->post(route('teacher.communications.store'), [
            'target' => 'classroom:' . $w['otherClassroom']->id . ':parents',
            'category' => 'announcement',
            'subject' => 'پیام خارج از کلاس',
            'body' => 'این پیام نباید برای کلاس دیگری ارسال شود.',
        ])->assertNotFound();

        $this->assertDatabaseCount('academy_messages', 0);
    }

    public function test_owner_recipient_search_is_scoped_and_returns_at_most_twenty_results(): void
    {
        $w = $this->workspace();

        for ($index = 1; $index <= 21; $index++) {
            $student = User::factory()->create([
                'name' => 'Searchable Student ' . $index,
                'status' => 'active',
            ]);
            $w['academy']->users()->attach($student->id, [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $url = route('owner.communications.recipients', $w['academy'])
            . '?type=students&q=' . urlencode('Searchable');

        $this->actingAs($w['owner'])
            ->getJson($url)
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('has_more', true);

        $this->actingAs($w['parent'])
            ->getJson($url)
            ->assertForbidden();
    }

    private function workspace(): array
    {
        $owner = User::factory()->create(['status' => 'active']);
        $teacher = User::factory()->create(['status' => 'active']);
        $otherTeacher = User::factory()->create(['status' => 'active']);
        $student = User::factory()->create(['status' => 'active']);
        $otherStudent = User::factory()->create(['status' => 'active']);
        $parent = User::factory()->create(['status' => 'active']);
        $otherParent = User::factory()->create(['status' => 'active']);

        $ownerRole = $this->makeRole('academy-owner', ['dashboard.view', 'parents.view', 'parents.manage']);
        $teacherRole = $this->makeRole('teacher', ['dashboard.view', 'parent_messages.view', 'parent_messages.manage']);
        $studentRole = $this->makeRole('student', ['dashboard.view', 'student_messages.view', 'student_messages.manage']);
        $parentRole = $this->makeRole('parent', ['dashboard.view', 'parent_messages.view']);

        $owner->roles()->attach($ownerRole);
        foreach ([$teacher, $otherTeacher] as $user) $user->roles()->attach($teacherRole);
        foreach ([$student, $otherStudent] as $user) $user->roles()->attach($studentRole);
        foreach ([$parent, $otherParent] as $user) $user->roles()->attach($parentRole);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست پیام',
            'slug' => 'academy-messages-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        foreach ([[$teacher, 'teacher'], [$otherTeacher, 'teacher'], [$student, 'student'], [$otherStudent, 'student'], [$parent, 'parent'], [$otherParent, 'parent']] as [$user, $role]) {
            $academy->users()->attach($user->id, ['role' => $role, 'status' => 'active', 'joined_at' => now()]);
        }

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست پیام',
            'slug' => 'course-messages-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس یک',
            'code' => 'ONE-' . strtoupper(Str::random(5)),
            'capacity' => 20,
            'status' => 'active',
        ]);
        $otherClassroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس دو',
            'code' => 'TWO-' . strtoupper(Str::random(5)),
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classroom->teachers()->attach($teacher->id);
        $otherClassroom->teachers()->attach($otherTeacher->id);
        $classroom->students()->attach($student->id, ['status' => 'active', 'enrolled_at' => now()]);
        $otherClassroom->students()->attach($otherStudent->id, ['status' => 'active', 'enrolled_at' => now()]);
        $student->parents()->attach($parent->id, ['relation' => 'guardian']);
        $otherStudent->parents()->attach($otherParent->id, ['relation' => 'guardian']);

        return compact('owner', 'teacher', 'otherTeacher', 'student', 'otherStudent', 'parent', 'otherParent', 'academy', 'course', 'classroom', 'otherClassroom');
    }

    private function makeRole(string $slug, array $permissionNames): Role
    {
        $role = Role::query()->create(['name' => $slug, 'slug' => $slug, 'description' => $slug]);
        $ids = [];
        foreach ($permissionNames as $name) {
            $permission = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $name, 'group' => explode('.', $name)[0]]
            );
            $ids[] = $permission->id;
        }
        $role->permissions()->sync($ids);
        return $role;
    }
}
