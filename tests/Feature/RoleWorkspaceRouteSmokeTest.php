<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Navigation-level GET smoke tests. These cover the entry pages visible in
 * each role's sidebar/bottom bar, in addition to the detailed workflow tests.
 * Deep CRUD routes are covered by their domain-specific feature suites.
 */
class RoleWorkspaceRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_and_admin_workspace_navigation_pages_render(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = $owner->ownedAcademies()->where('status', 'active')->orderBy('id')->firstOrFail();

        $routes = [
            ['owner.dashboard', []],
            ['owner.people.index', [$academy]],
            ['owner.classrooms.index', [$academy]],
            ['owner.attendance.index', [$academy]],
            ['owner.assignments.index', [$academy]],
            ['owner.meetings.index', [$academy]],
            ['owner.schedules.index', [$academy]],
            ['owner.exams.index', [$academy]],
            ['owner.recordings.index', [$academy]],
            ['owner.teacher-payments.index', [$academy]],
            ['owner.messages.index', [$academy]],
            ['owner.communications.index', [$academy]],
            ['owner.counseling.index', [$academy]],
            ['owner.resources.index', [$academy]],
            ['owner.showcase.index', [$academy]],
            ['owner.products.index', [$academy]],
            ['owner.orders.index', [$academy]],
            ['owner.academy.edit', [$academy]],
            ['owner.academy.banners.edit', [$academy]],
            ['owner.courses.index', []],
            ['owner.website.index', []],
            ['owner.website.hero.edit', []],
            ['owner.content.index', []],
            ['owner.blog.index', []],
            ['owner.legal-documents.index', []],
            ['owner.finance.index', []],
            ['owner.reports.index', []],
        ];

        foreach ($routes as [$routeName, $parameters]) {
            $this->actingAs($owner)
                ->get(route($routeName, $parameters))
                ->assertOk($routeName . ' must render successfully from owner/admin navigation.');
        }
    }

    public function test_teacher_navigation_pages_render(): void
    {
        $this->seed();
        $teacher = User::query()->where('email', 'teacher.math@sheykhan.test')->firstOrFail();

        foreach ([
            'teacher.dashboard',
            'teacher.profile.edit',
            'teacher.courses.index',
            'teacher.classrooms.index',
            'teacher.schedule.index',
            'teacher.attendance.index',
            'teacher.students.index',
            'teacher.assignments.index',
            'teacher.exams.index',
            'teacher.messages.index',
            'teacher.direct-messages.index',
            'teacher.communications.index',
            'teacher.student-messages.index',
            'teacher.meetings.index',
            'teacher.counseling-requests.index',
            'teacher.payments.index',
            'teacher.live-classes.index',
        ] as $routeName) {
            $this->actingAs($teacher)
                ->get(route($routeName))
                ->assertOk($routeName . ' must render successfully from teacher navigation.');
        }
    }

    public function test_student_navigation_pages_render(): void
    {
        $this->seed();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();

        foreach ([
            'student.dashboard',
            'student.messages.index',
            'student.courses.index',
            'student.assignments.index',
            'student.exams.index',
            'student.live-classes.index',
            'student.attendance.index',
            'student.results.index',
            'student.achievements.index',
            'student.notes.index',
            'student.profile.edit',
            'student.resources.index',
        ] as $routeName) {
            $this->actingAs($student)
                ->get(route($routeName))
                ->assertOk($routeName . ' must render successfully from student navigation.');
        }
    }

    public function test_parent_navigation_pages_render(): void
    {
        $this->seed();
        $parent = User::query()->where('email', 'parent.armin@sheykhan.test')->firstOrFail();

        foreach ([
            'parent.dashboard',
            'parent.messages.index',
            'parent.counseling.index',
            'parent.attendance.index',
            'parent.results.index',
            'parent.resources.index',
        ] as $routeName) {
            $this->actingAs($parent)
                ->get(route($routeName))
                ->assertOk($routeName . ' must render successfully from parent navigation.');
        }
    }

    public function test_navigation_smoke_test_uses_the_seeded_owner_academy(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $academy = Academy::query()->where('owner_id', $owner->id)->where('status', 'active')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('owner.people.index', $academy))
            ->assertOk()
            ->assertSee('اعضای آموزشگاه')
            ->assertSee('مدرس‌ها')
            ->assertSee('دانش‌آموزان')
            ->assertSee('والدین');
    }
}
