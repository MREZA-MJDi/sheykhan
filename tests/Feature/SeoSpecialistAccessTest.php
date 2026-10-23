<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeoSpecialistAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_quick_login_seo_identity_authenticates_to_only_the_seo_console(): void
    {
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $password = (string) config('role_access.test_password');
        $seoRole = Role::query()->where('slug', 'seo-specialist')->firstOrFail();
        $user = User::factory()->create([
            'name' => 'متخصص SEO تست',
            'email' => 'seo@sheykhan.test',
            'password' => $password,
            'status' => 'active',
        ]);
        $user->roles()->attach($seoRole->id);

        $this->post(route('login.store'), [
            'identifier' => 'seo@sheykhan.test',
            'password' => $password,
        ])->assertRedirect(route('owner.seo.index'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('owner.dashboard'))->assertForbidden();
        $this->get(route('owner.seo.index'))->assertOk();
    }

    public function test_seo_specialist_can_audit_public_pages_across_the_site_without_owner_permissions(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        $this->actingAs($specialist)
            ->get(route('dashboard'))
            ->assertRedirect(route('owner.seo.index'));

        $this->actingAs($specialist)
            ->get(route('owner.seo.index'))
            ->assertOk()
            ->assertSee('SEO Visible Course')
            ->assertSee(route('courses.show', $course), false)
            ->assertSee('صفحات عمومی و منتشرشده')
            ->assertSee('ممیزی کیفیت فنی SEO')
            ->assertSee('عنوان SEO اختصاصی تنظیم نشده')
            ->assertSee('توضیحات SEO اختصاصی تنظیم نشده')
            ->assertSee('بررسی robots.txt')
            ->assertSee('role-sidebar owner-sidebar', false)
            ->assertSee('شیخان · پنل متخصص SEO')
            ->assertSee('پنل متخصص SEO</small>', false)
            ->assertDontSee('کارتابل ادمین / منشی');

        $this->actingAs($specialist)
            ->get(route('owner.dashboard'))
            ->assertForbidden();

        $this->actingAs($specialist)
            ->get(route('owner.website.index'))
            ->assertForbidden();
    }

    public function test_seo_specialist_can_update_only_the_seo_metadata_of_a_public_course(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        $this->actingAs($specialist)
            ->patch(route('owner.seo.update', ['course', $course->id]), [
                'title' => 'SEO title written by specialist',
                'description' => 'A real description saved to this published course.',
                'robots' => 'index,follow',
                'og_title' => 'Share title',
                'og_description' => 'Share description',
            ])
            ->assertRedirect(route('owner.seo.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('seo_metas', [
            'seoable_type' => $course->getMorphClass(),
            'seoable_id' => $course->id,
            'title' => 'SEO title written by specialist',
            'robots' => 'index,follow',
        ]);

        $this->assertSame('SEO Visible Course', $course->fresh()->title);
    }

    public function test_noindex_metadata_removes_a_published_course_from_the_sitemap_immediately(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();
        Cache::flush();

        $url = route('courses.show', $course);

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertSee($url, false);

        $this->actingAs($specialist)
            ->patch(route('owner.seo.update', ['course', $course->id]), [
                'title' => 'Keep this page out of search',
                'robots' => 'noindex,follow',
            ])
            ->assertRedirect(route('owner.seo.index'));

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertDontSee($url, false);
    }

    public function test_robots_txt_is_dynamic_and_references_the_real_sitemap_route(): void
    {
        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /owner', false)
            ->assertSee('Disallow: /checkout', false)
            ->assertSee('Sitemap: ' . route('seo.sitemap'), false);
    }

    public function test_access_command_grants_and_revokes_only_the_seo_specialist_role_for_an_existing_account(): void
    {
        $user = User::factory()->create([
            'email' => 'seo-colleague-' . Str::random(8) . '@test.local',
        ]);

        $this->assertSame(0, Artisan::call('sheykhan:seo-access', [
            'email' => $user->email,
            'action' => 'grant',
        ]));

        $user->refresh();
        $this->assertTrue($user->hasRole('seo-specialist'));
        $this->assertTrue($user->hasPermission('seo.manage'));
        $this->assertFalse($user->hasPermission('finance.view'));
        $this->assertFalse($user->hasPermission('students.view'));

        $this->assertSame(0, Artisan::call('sheykhan:seo-access', [
            'email' => $user->email,
            'action' => 'revoke',
        ]));

        $this->assertFalse($user->fresh()->hasRole('seo-specialist'));
    }

    private function makeSeoWorkspace(): array
    {
        $owner = User::factory()->create([
            'email' => 'content-owner-' . Str::random(8) . '@test.local',
        ]);
        $specialist = User::factory()->create([
            'email' => 'seo-specialist-' . Str::random(8) . '@test.local',
        ]);

        $role = Role::create([
            'name' => 'متخصص SEO',
            'slug' => 'seo-specialist',
            'description' => 'SEO specialist',
        ]);
        $dashboard = Permission::create([
            'name' => 'dashboard.view',
            'label' => 'View dashboard',
            'group' => 'dashboard',
        ]);
        $seo = Permission::create([
            'name' => 'seo.manage',
            'label' => 'Manage SEO',
            'group' => 'seo',
        ]);
        $role->permissions()->attach([$dashboard->id, $seo->id]);
        $specialist->roles()->attach($role->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی منتشرشده',
            'slug' => 'academy-' . Str::random(8),
            'status' => 'active',
        ]);

        $course = $academy->courses()->create([
            'created_by' => $owner->id,
            'title' => 'SEO Visible Course',
            'slug' => 'seo-course-' . Str::random(8),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);

        return [$specialist, $course];
    }
}
