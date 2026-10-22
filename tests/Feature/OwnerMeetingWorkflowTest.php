<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OwnerMeetingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_schedule_a_meeting_for_a_valid_student_parent_pair(): void
    {
        [$owner, $academy, $course, $classroom, $student, $parent] = $this->workspace();

        $start = now()->addDays(2)->setTime(12, 0);
        $payload = [
            'meeting_target' => $classroom->id . ':' . $student->id . ':' . $parent->id,
            'title' => 'جلسه هماهنگی با آقای سبحانی',
            'message' => 'لطفاً کارنامه و پرسش‌های خود را همراه داشته باشید.',
            'scheduled_at' => $start->format('Y-m-d\TH:i'),
            'scheduled_end_at' => $start->copy()->addHour()->format('Y-m-d\TH:i'),
        ];

        $this->actingAs($owner)
            ->post(route('owner.meetings.store', $academy), $payload)
            ->assertRedirect(route('owner.meetings.index', $academy))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('academy_meetings', [
            'academy_id' => $academy->id,
            'classroom_id' => $classroom->id,
            'course_id' => $course->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'title' => $payload['title'],
            'status' => 'scheduled',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.meetings.index', $academy))
            ->assertOk()
            ->assertSee($payload['title'])
            ->assertSee($payload['message'])
            ->assertSee($student->name);
    }

    public function test_owner_cannot_schedule_a_meeting_for_a_parent_not_linked_to_the_student(): void
    {
        [$owner, $academy, , $classroom, $student] = $this->workspace();
        $unlinkedParent = User::factory()->create(['status' => 'active']);

        $this->actingAs($owner)
            ->post(route('owner.meetings.store', $academy), [
                'meeting_target' => $classroom->id . ':' . $student->id . ':' . $unlinkedParent->id,
                'title' => 'جلسه نامعتبر',
                'scheduled_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('meeting_target');

        $this->assertDatabaseCount('academy_meetings', 0);
    }

    public function test_owner_attendance_view_is_scoped_to_the_owned_academy_and_has_pagination(): void
    {
        [$owner, $academy, , $classroom, $student] = $this->workspace();

        Attendance::query()->create([
            'classroom_id' => $classroom->id,
            'student_id' => $student->id,
            'marked_by' => $owner->id,
            'attendance_date' => today()->toDateString(),
            'status' => 'present',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.attendance.index', $academy) . '?status=present')
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('سوابق ثبت‌شده')
            ->assertViewHas('attendances', fn ($items) => $items->total() === 1 && $items->count() === 1);
    }

    public function test_seo_routes_are_not_available_to_an_academy_owner_even_if_old_permission_remains(): void
    {
        [$owner, $academy] = $this->workspace();

        $this->actingAs($owner)
            ->get(route('owner.seo.index'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('owner.dashboard'))
            ->assertOk()
            ->assertDontSee('SEO و دیده‌شدن');
    }

    public function test_meeting_form_does_not_prepopulate_a_personal_demo_title(): void
    {
        [$owner, $academy] = $this->workspace();

        $this->actingAs($owner)
            ->get(route('owner.meetings.index', $academy))
            ->assertOk()
            ->assertDontSee('value="هماهنگی با آقای سبحانی"', false)
            ->assertSee('placeholder="مثلاً: بررسی روند تحصیلی و هماهنگی با خانواده"', false);
    }

    private function workspace(): array
    {
        $owner = User::factory()->create([
            'name' => 'مدیر تست',
            'email' => 'owner-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);
        $student = User::factory()->create([
            'name' => 'دانش‌آموز تست',
            'email' => 'student-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);
        $parent = User::factory()->create([
            'name' => 'ولی تست',
            'email' => 'parent-' . Str::random(8) . '@test.local',
            'status' => 'active',
        ]);

        $ownerRole = $this->role('academy-owner', 'مدیر آموزشگاه', [
            'dashboard.view',
            'attendance.view',
            'live_classes.view',
            'live_classes.manage',
            'seo.manage',
        ]);
        $studentRole = $this->role('student', 'دانش‌آموز', ['dashboard.view']);
        $parentRole = $this->role('parent', 'والد', ['dashboard.view']);

        $owner->roles()->attach($ownerRole->id);
        $student->roles()->attach($studentRole->id);
        $parent->roles()->attach($parentRole->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تست',
            'slug' => 'academy-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        foreach ([[$student, 'student'], [$parent, 'parent']] as [$user, $role]) {
            $academy->users()->attach($user->id, [
                'role' => $role,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره تست',
            'slug' => 'course-' . Str::lower(Str::random(8)),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس تست',
            'code' => 'CLS-' . strtoupper(Str::random(6)),
            'capacity' => 20,
            'status' => 'active',
        ]);

        $classroom->students()->attach($student->id, [
            'status' => 'active',
            'enrolled_at' => now(),
        ]);
        $parent->children()->attach($student->id, ['relation' => 'parent']);

        return [$owner, $academy, $course, $classroom, $student, $parent];
    }

    private function role(string $slug, string $name, array $permissionNames): Role
    {
        $role = Role::query()->create([
            'name' => $name,
            'slug' => $slug,
            'description' => $name,
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
