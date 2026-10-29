<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\LearningResource;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParentLearningResourceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_view_resources_only_for_the_selected_linked_child_with_valid_course_access(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['status' => 'active']);
        $parent = User::factory()->create(['status' => 'active']);
        $child = User::factory()->create(['status' => 'active']);
        $otherLinkedChild = User::factory()->create(['status' => 'active']);
        $unlinkedChild = User::factory()->create(['status' => 'active']);

        $owner->roles()->attach($this->role('academy-owner', ['dashboard.view'])->id);
        $parent->roles()->attach($this->role('parent', ['dashboard.view'])->id);
        $studentRole = $this->role('student', ['dashboard.view', 'resources.view']);
        foreach ([$child, $otherLinkedChild, $unlinkedChild] as $student) {
            $student->roles()->attach($studentRole->id);
        }

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آموزشگاه تست منابع والد',
            'slug' => 'parent-resources-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);

        foreach ([$child, $otherLinkedChild, $unlinkedChild] as $student) {
            $academy->users()->attach($student->id, [
                'role' => 'student',
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $parent->children()->attach($child->id, ['relation' => 'والد']);
        $parent->children()->attach($otherLinkedChild->id, ['relation' => 'والد']);

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره دارای منبع',
            'slug' => 'course-resource-' . Str::lower(Str::random(8)),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        $child->enrollments()->create([
            'course_id' => $course->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now()->subDay(),
        ]);

        $otherLinkedChild->enrollments()->create([
            'course_id' => $course->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now()->subDay(),
        ]);

        $filePath = 'parent-resources/' . Str::uuid() . '.pdf';
        Storage::disk('local')->put($filePath, 'parent-resource-fixture');

        $media = Media::query()->create([
            'uploaded_by' => $owner->id,
            'disk' => 'local',
            'path' => $filePath,
            'original_name' => 'lesson-notes.pdf',
            'file_name' => basename($filePath),
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => Storage::disk('local')->size($filePath),
            'checksum' => hash('sha256', Storage::disk('local')->get($filePath)),
            'visibility' => 'private',
            'collection' => 'learning-resources',
            'metadata' => ['test' => true],
            'status' => 'active',
        ]);

        $resource = LearningResource::query()->create([
            'academy_id' => $academy->id,
            'course_id' => $course->id,
            'classroom_id' => null,
            'lesson_id' => null,
            'media_id' => $media->id,
            'uploaded_by' => $owner->id,
            'title' => 'جزوهٔ دوره',
            'description' => 'فایل خصوصی دورهٔ منتشرشده',
            'resource_type' => 'document',
            'visibility' => 'enrolled_students',
            'release_at' => now()->subMinute(),
            'downloadable' => false,
            'status' => 'active',
        ]);

        $this->actingAs($parent)
            ->get(route('parent.resources.index', ['child' => $child->id]))
            ->assertOk()
            ->assertSee('جزوهٔ دوره')
            ->assertSee($child->name);

        $this->actingAs($parent)
            ->get(route('parent.resources.view', ['resource' => $resource->id, 'child' => $child->id]))
            ->assertRedirect(route('protected-learning-media.view', [
                'media' => $media->id,
                'resource' => $resource->id,
                'child' => $child->id,
            ]));

        // A child linked to the same parent but not enrolled in this course
        // must not inherit the selected sibling's resource access.
        $otherLinkedChild->enrollments()->delete();

        $this->actingAs($parent)
            ->get(route('protected-learning-media.view', [
                'media' => $media->id,
                'resource' => $resource->id,
                'child' => $otherLinkedChild->id,
            ]))
            ->assertNotFound();

        $this->actingAs($parent)
            ->get(route('protected-learning-media.pages', [
                'media' => $media->id,
                'page' => 1,
                'resource' => $resource->id,
                'child' => $otherLinkedChild->id,
            ]))
            ->assertNotFound();

        $this->actingAs($parent)
            ->get(route('parent.resources.view', ['resource' => $resource->id, 'child' => $otherLinkedChild->id]))
            ->assertNotFound();

        // A student outside the parent-child relation must never be selectable
        // even if that student has a valid academy membership and enrollment.
        $unlinkedChild->enrollments()->create([
            'course_id' => $course->id,
            'status' => 'active',
            'paid_amount' => 0,
            'started_at' => now(),
        ]);

        $this->actingAs($parent)
            ->get(route('parent.resources.index', ['child' => $unlinkedChild->id]))
            ->assertNotFound();

        $this->actingAs($parent)
            ->get(route('parent.resources.view', ['resource' => $resource->id, 'child' => $unlinkedChild->id]))
            ->assertNotFound();
    }

    private function role(string $slug, array $permissionNames): Role
    {
        $role = Role::query()->create([
            'name' => $slug . '-' . Str::lower(Str::random(5)),
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
