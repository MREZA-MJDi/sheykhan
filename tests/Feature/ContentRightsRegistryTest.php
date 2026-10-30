<?php

namespace Tests\Feature;

use App\Models\ContentRight;
use App\Models\Media;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ContentRightsRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_register_a_draft_and_activate_a_new_immutable_rights_revision(): void
    {
        [$owner] = $this->ownerWithLegalPermission();
        $media = $this->createMedia($owner);

        $this->actingAs($owner)
            ->get(route('owner.content-rights.index'))
            ->assertOk()
            ->assertSee('حقوق نشر و مجوز فایل‌ها')
            ->assertSee('ثبت پرونده حقوق نشر');

        $first = $this->registerDraft($owner, $media, 'نسخه اول مجوز');
        $this->assertSame('draft', $first->status);

        $this->actingAs($owner)
            ->patch(route('owner.content-rights.activate', $first))
            ->assertRedirect(route('owner.content-rights.index'));

        $this->assertDatabaseHas('content_rights', ['id' => $first->id, 'status' => 'active']);

        $second = $this->registerDraft($owner, $media, 'نسخه دوم مجوز');

        $this->actingAs($owner)
            ->patch(route('owner.content-rights.activate', $second))
            ->assertRedirect(route('owner.content-rights.index'));

        $this->assertDatabaseHas('content_rights', ['id' => $first->id, 'status' => 'superseded']);
        $this->assertDatabaseHas('content_rights', ['id' => $second->id, 'status' => 'active']);

        $this->actingAs($owner)
            ->patch(route('owner.content-rights.update', $second), [
                'media_id' => $media->id,
                'title' => 'تغییر غیرمجاز',
                'rights_holder' => 'صاحب اثر',
                'source_type' => 'licensed',
                'license_name' => 'قرارداد',
                'territory' => 'ایران',
                'permitted_uses' => ['streaming'],
            ])
            ->assertStatus(409);
    }

    public function test_draft_cannot_be_activated_without_a_reference_or_evidence(): void
    {
        [$owner] = $this->ownerWithLegalPermission();
        $media = $this->createMedia($owner);

        $this->actingAs($owner)
            ->post(route('owner.content-rights.store'), [
                'media_id' => $media->id,
                'title' => 'پرونده بدون مدرک',
                'rights_holder' => 'صاحب اثر',
                'source_type' => 'licensed',
                'license_name' => 'مجوز',
                'permitted_uses' => ['streaming'],
            ])
            ->assertRedirect(route('owner.content-rights.index'));

        $right = ContentRight::query()->where('title', 'پرونده بدون مدرک')->firstOrFail();

        $this->actingAs($owner)
            ->from(route('owner.content-rights.index'))
            ->patch(route('owner.content-rights.activate', $right))
            ->assertSessionHasErrors('evidence');

        $this->assertDatabaseHas('content_rights', ['id' => $right->id, 'status' => 'draft']);
    }

    public function test_user_without_legal_permission_cannot_view_or_create_content_rights(): void
    {
        $role = Role::create([
            'name' => 'مدیر آموزشگاه بدون حقوقی',
            'slug' => 'academy-owner',
            'description' => 'Test role without legal permission',
        ]);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);
        $media = $this->createMedia($user);

        $this->actingAs($user)
            ->get(route('owner.content-rights.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('owner.content-rights.store'), [
                'media_id' => $media->id,
                'title' => 'مجوز غیرمجاز',
                'rights_holder' => 'صاحب اثر',
                'source_type' => 'owned',
                'license_name' => 'مالکیت مستقیم',
                'permitted_uses' => ['streaming'],
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('content_rights', 0);
    }

    public function test_content_rights_reject_inactive_media_and_invalid_usage_values(): void
    {
        [$owner] = $this->ownerWithLegalPermission();
        $inactiveMedia = $this->createMedia($owner, 'archived');

        $response = $this->actingAs($owner)
            ->from(route('owner.content-rights.index'))
            ->post(route('owner.content-rights.store'), [
                'media_id' => $inactiveMedia->id,
                'title' => 'مجوز فایل غیرفعال',
                'rights_holder' => 'صاحب اثر',
                'source_type' => 'owned',
                'license_name' => 'مالکیت مستقیم',
                'permitted_uses' => ['download-everything'],
            ]);

        $response->assertSessionHasErrors(['media_id', 'permitted_uses.0']);
        $this->assertDatabaseCount('content_rights', 0);
    }

    private function ownerWithLegalPermission(): array
    {
        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner with legal permission',
        ]);
        $permission = Permission::create([
            'name' => 'legal.manage',
            'label' => 'Manage content rights',
            'group' => 'legal',
        ]);
        $role->permissions()->attach($permission->id);

        $owner = User::factory()->create();
        $owner->roles()->attach($role->id);

        return [$owner, $role, $permission];
    }

    private function createMedia(User $uploader, string $status = 'active'): Media
    {
        return Media::query()->create([
            'uploaded_by' => $uploader->id,
            'disk' => 'local',
            'path' => 'test/rights/' . Str::random(12) . '.mp4',
            'original_name' => 'ویدئوی آزمایشی.mp4',
            'file_name' => Str::random(20) . '.mp4',
            'mime_type' => 'video/mp4',
            'extension' => 'mp4',
            'size' => 1024,
            'checksum' => hash('sha256', Str::random(20)),
            'visibility' => 'private',
            'collection' => 'recordings',
            'metadata' => [],
            'status' => $status,
        ]);
    }

    private function registerDraft(User $owner, Media $media, string $title): ContentRight
    {
        $this->actingAs($owner)
            ->post(route('owner.content-rights.store'), [
                'media_id' => $media->id,
                'title' => $title,
                'rights_holder' => 'صاحب اثر آزمایشی',
                'source_type' => 'licensed',
                'license_name' => 'قرارداد نمونه',
                'license_reference' => 'TEST-' . Str::upper(Str::random(8)),
                'territory' => 'ایران',
                'permitted_uses' => ['streaming', 'paid_distribution'],
                'attribution' => 'انتساب به صاحب اثر',
            ])
            ->assertRedirect(route('owner.content-rights.index'));

        return ContentRight::query()->where('title', $title)->firstOrFail();
    }
}
