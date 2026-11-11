<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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
        $seoAccount = collect(config('role_access.test_accounts', []))
            ->firstWhere('role_key', 'seo-specialist');
        $this->assertIsArray($seoAccount);

        $seoRole = Role::query()->where('slug', 'seo-specialist')->firstOrFail();
        $user = User::factory()->create([
            'name' => 'متخصص SEO تست',
            'email' => 'seo@sheykhan.com',
            'mobile' => $seoAccount['username'],
            'password' => $password,
            'status' => 'active',
        ]);
        $user->roles()->attach($seoRole->id);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee($seoAccount['username'])
            ->assertSee('متخصص SEO');

        $this->post(route('login.store'), [
            'mobile' => $seoAccount['username'],
            'password' => $password,
        ])->assertRedirect(route('seo.index'));

        $this->assertAuthenticatedAs($user);

        $this->get(route('owner.dashboard'))->assertForbidden();
        $this->get(route('seo.index'))->assertOk();
    }

    public function test_seo_specialist_can_audit_public_pages_across_the_site_without_owner_permissions(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        $this->actingAs($specialist)
            ->get(route('dashboard'))
            ->assertRedirect(route('seo.index'));

        $this->actingAs($specialist)
            ->get(route('seo.index'))
            ->assertOk()
            ->assertSee('SEO Visible Course')
            ->assertSee(route('courses.show', $course), false)
            ->assertSee('مدیریت دیده‌شدن شیخان در جست‌وجو')
            ->assertSee('عملکرد واقعی در Google Search Console')
            ->assertSee('عنوان SEO اختصاصی تنظیم نشده')
            ->assertSee('توضیحات SEO اختصاصی تنظیم نشده')
            ->assertSee('robots.txt')
            ->assertSee('owner-admin-v2', false)
            ->assertSee('سایت و SEO')
            ->assertSee('آکادمی شیخان')
            ->assertSee('SEO و محتوای عمومی')
            ->assertDontSee('role-sidebar owner-sidebar', false)
            ->assertDontSee('admin.css', false)
            ->assertDontSee('owner-design-system.css', false)
            ->assertDontSee('کارتابل ادمین / منشی');

        $this->actingAs($specialist)
            ->get(route('owner.dashboard'))
            ->assertForbidden();

        $this->actingAs($specialist)
            ->get(route('seo.website.index'))
            ->assertOk()
            ->assertSee('ظاهر و محتوای قابل‌دیدن آموزشگاه را از یکجا مدیریت کن.');
    }

    public function test_seo_blog_uses_dedicated_seo_route_and_not_owner_url(): void
    {
        [$specialist] = $this->makeSeoWorkspace();

        $url = route('seo.blog.index');
        $this->assertStringContainsString('/seo/blog', $url);
        $this->assertStringNotContainsString('/owner/', $url);

        $this->actingAs($specialist)
            ->get($url)
            ->assertOk();
    }

    public function test_all_primary_seo_workspace_pages_render_for_an_authorized_specialist(): void
    {
        [$specialist] = $this->makeSeoWorkspace();

        $this->actingAs($specialist)
            ->get(route('seo.blog.index'))
            ->assertOk()
            ->assertSee('بلاگ و مقالات')
            ->assertSee('محتوای عمومی');

        foreach ([
            'seo.keywords.index',
            'seo.keywords.create',
            'seo.blog.create',
            'seo.content.index',
            'seo.content.create',
            'seo.website.hero.edit',
        ] as $routeName) {
            $this->actingAs($specialist)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    public function test_academy_owner_without_seo_role_cannot_open_the_seo_console(): void
    {
        $role = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner without SEO access',
        ]);
        $user = User::factory()->create();
        $user->roles()->attach($role->id);

        $this->actingAs($user)
            ->get(route('seo.index'))
            ->assertForbidden();
    }

    public function test_seo_dashboard_does_not_fail_when_search_console_snapshot_table_is_missing(): void
    {
        [$specialist] = $this->makeSeoWorkspace();
        Schema::shouldReceive('hasTable')
            ->with('seo_performance_snapshots')
            ->andReturn(false);

        $this->actingAs($specialist)
            ->get(route('seo.index'))
            ->assertOk()
            ->assertSee('جدول ذخیرهٔ آمار SEO هنوز آماده نیست.');
    }

    public function test_seo_page_inventory_filters_by_type_status_and_search_term(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        $this->actingAs($specialist)
            ->get(route('seo.index', [
                'q' => 'SEO Visible Course',
                'type' => 'course',
                'status' => 'unconfigured',
            ]))
            ->assertOk()
            ->assertSee('SEO Visible Course')
            ->assertSee('عملکرد واقعی در Google Search Console')
            ->assertSee('بدون دادهٔ دریافتی');

        $this->actingAs($specialist)
            ->get(route('seo.index', ['type' => 'not-a-real-type']))
            ->assertOk()
            ->assertSee('SEO Visible Course');
    }

    public function test_seo_page_inventory_paginates_public_records(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        for ($index = 1; $index <= 18; $index++) {
            $page = $course->replicate(['slug']);
            $page->slug = 'seo-paginated-' . $index . '-' . Str::lower(Str::random(5));
            $page->title = 'Paginated SEO Course ' . $index;
            $page->save();
        }

        $this->actingAs($specialist)
            ->get(route('seo.index', ['q' => 'Paginated SEO Course', 'type' => 'course']))
            ->assertOk()
            ->assertSee('18 نتیجه')
            ->assertSee('seo-pagination', false);

        $this->actingAs($specialist)
            ->get(route('seo.index', ['q' => 'Paginated SEO Course', 'type' => 'course', 'page' => 2]))
            ->assertOk()
            ->assertSee('Paginated SEO Course');
    }

    public function test_audit_flags_duplicate_public_page_titles(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        $duplicate = $course->replicate(['slug']);
        $duplicate->slug = 'seo-duplicate-' . Str::lower(Str::random(8));
        $duplicate->save();

        $this->actingAs($specialist)
            ->get(route('seo.index'))
            ->assertOk()
            ->assertSee('عنوان SEO تکراری')
            ->assertSee('صفحات با متادیتای تکراری');
    }

    public function test_seo_specialist_can_update_only_the_seo_metadata_of_a_public_course(): void
    {
        [$specialist, $course] = $this->makeSeoWorkspace();

        $this->actingAs($specialist)
            ->patch(route('seo.pages.update', ['course', $course->id]), [
                'title' => 'SEO title written by specialist',
                'description' => 'A real description saved to this published course.',
                'robots' => 'index,follow',
                'og_title' => 'Share title',
                'og_description' => 'Share description',
            ])
            ->assertRedirect(route('seo.index'))
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
            ->patch(route('seo.pages.update', ['course', $course->id]), [
                'title' => 'Keep this page out of search',
                'robots' => 'noindex,follow',
            ])
            ->assertRedirect(route('seo.index'));

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

    public function test_access_command_refuses_to_turn_an_existing_owner_account_into_a_dual_role_seo_account(): void
    {
        $ownerRole = Role::create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Existing operational role',
        ]);
        $user = User::factory()->create([
            'email' => 'existing-owner-' . Str::random(8) . '@test.local',
        ]);
        $user->roles()->attach($ownerRole->id);

        $exitCode = Artisan::call('sheykhan:seo-access', [
            'email' => $user->email,
            'action' => 'grant',
        ]);

        $this->assertSame(1, $exitCode);
        $this->assertTrue($user->fresh()->hasRole('academy-owner'));
        $this->assertFalse($user->fresh()->hasRole('seo-specialist'));
        $this->assertFalse($user->fresh()->hasPermission('seo.manage'));
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

    public function test_seo_workspace_loads_hero_and_banner_editor_runtime(): void
    {
        $entrypoint = file_get_contents(resource_path('js/seo-dashboard.js'));
        $layout = file_get_contents(resource_path('views/layouts/seo-specialist.blade.php'));

        $this->assertStringContainsString("import './owner/seo-site-editors.js';", $entrypoint);
        $this->assertStringContainsString("resources/js/seo-dashboard.js", $layout);
    }

}
