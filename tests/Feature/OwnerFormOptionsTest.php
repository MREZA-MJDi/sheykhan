<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerFormOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_large_resource_and_exam_form_options_are_searchable_and_limited_to_twenty(): void
    {
        [$owner, $academy, $teacher] = $this->workspace(['resources.manage', 'exams.manage']);

        for ($index = 1; $index <= 23; $index++) {
            $course = Course::query()->create([
                'academy_id' => $academy->id,
                'created_by' => $owner->id,
                'title' => 'دوره قابل جست‌وجو ' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'slug' => 'search-course-' . Str::lower(Str::random(10)),
                'status' => 'draft',
                'access_type' => 'free',
                'price' => 0,
            ]);
            $course->teachers()->attach($teacher->id, ['is_primary' => true]);
        }

        $response = $this->actingAs($owner)->getJson(route('owner.form-options', [
            'academy' => $academy,
            'type' => 'exam-courses',
            'q' => 'دوره قابل جست‌وجو',
        ]));

        $response->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('has_more', true);

        $secondPage = $this->actingAs($owner)->getJson(route('owner.form-options', [
            'academy' => $academy,
            'type' => 'resource-courses',
            'q' => 'دوره قابل جست‌وجو',
            'page' => 2,
        ]));

        $secondPage->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('has_more', false);
    }

    public function test_resource_class_and_lesson_options_are_scoped_to_the_selected_course_and_academy(): void
    {
        [$owner, $academy, $teacher] = $this->workspace(['resources.manage']);
        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره منابع',
            'slug' => 'resource-course-' . Str::lower(Str::random(8)),
            'status' => 'draft',
            'access_type' => 'free',
            'price' => 0,
        ]);
        $course->teachers()->attach($teacher->id, ['is_primary' => true]);

        $classroom = Classroom::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'title' => 'کلاس منابع',
            'code' => 'RES-' . Str::upper(Str::random(6)),
            'status' => 'active',
        ]);

        $section = CourseSection::query()->create([
            'course_id' => $course->id,
            'title' => 'فصل منابع',
            'sort_order' => 1,
        ]);
        $lesson = Lesson::query()->create([
            'course_section_id' => $section->id,
            'title' => 'درس قابل جست‌وجو',
            'slug' => 'resource-lesson-' . Str::lower(Str::random(8)),
            'type' => 'text',
            'status' => 'draft',
            'sort_order' => 1,
        ]);

        $this->actingAs($owner)->getJson(route('owner.form-options', [
            'academy' => $academy,
            'type' => 'resource-classrooms',
            'course_id' => $course->id,
            'q' => 'کلاس منابع',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $classroom->id);

        $this->actingAs($owner)->getJson(route('owner.form-options', [
            'academy' => $academy,
            'type' => 'resource-lessons',
            'course_id' => $course->id,
            'q' => 'درس قابل جست‌وجو',
        ]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $lesson->id);
    }

    public function test_owner_form_options_require_the_matching_management_permission(): void
    {
        [$owner, $academy] = $this->workspace(['resources.manage']);

        $this->actingAs($owner)->getJson(route('owner.form-options', [
            'academy' => $academy,
            'type' => 'exam-courses',
            'q' => 'دوره',
        ]))->assertForbidden();
    }

    private function workspace(array $permissions): array
    {
        $owner = User::factory()->create(['status' => 'active']);
        $teacher = User::factory()->create(['status' => 'active']);

        $role = Role::query()->create([
            'name' => 'مدیر فرم‌های مقیاس‌پذیر',
            'slug' => 'academy-owner',
            'description' => 'Owner form options test',
        ]);

        $permissionIds = collect(array_merge(['academy.view'], $permissions))->map(function (string $name): int {
            return Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $name, 'group' => explode('.', $name)[0]],
            )->id;
        });
        $role->permissions()->sync($permissionIds);
        $owner->roles()->attach($role->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی فرم‌ها',
            'slug' => 'form-options-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        return [$owner, $academy, $teacher];
    }
}
