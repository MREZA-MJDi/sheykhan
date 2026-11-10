<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OwnerContextualNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_pages_share_permission_aware_contextual_tabs_and_keep_academy_scope(): void
    {
        $ownerRole = Role::query()->create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);

        $permissions = collect([
            'courses.view',
            'classrooms.view',
            'attendance.view',
            'assignments.view',
            'exams.view',
            'live_classes.view',
            'resources.view',
        ])->map(fn (string $name) => Permission::query()->create([
            'name' => $name,
            'label' => $name,
            'group' => 'learning',
        ]));

        $ownerRole->permissions()->attach($permissions->pluck('id')->all());
        $owner = User::factory()->create(['email' => 'context-tabs-' . Str::random(8) . '@test.local']);
        $owner->roles()->attach($ownerRole->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی تب‌های آموزشی',
            'slug' => 'context-tabs-' . Str::random(8),
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('owner.classrooms.index', $academy))
            ->assertOk()
            ->assertSee('admin-context-tabs', false)
            ->assertSee('دوره‌ها')
            ->assertSee('کلاس‌ها و گروه‌ها')
            ->assertSee('حضور و غیاب')
            ->assertSee('تکالیف و تصحیح')
            ->assertSee('آزمون‌ها و نتایج')
            ->assertSee('برنامه کلاس آنلاین')
            ->assertSee('هماهنگی جلسات')
            ->assertSee('ضبط کلاس‌ها')
            ->assertSee('منابع آموزشی');

        $html = $response->getContent();
        $this->assertStringContainsString('href="' . route('owner.attendance.index', $academy) . '"', $html);
        $this->assertStringContainsString('href="' . route('owner.exams.index', $academy) . '"', $html);
        $this->assertMatchesRegularExpression(
            '/<a\b[^>]*href="' . preg_quote(route('owner.classrooms.index', $academy), '/') . '"[^>]*aria-current="page"[^>]*>/',
            $html,
        );
    }

    public function test_contextual_learning_tabs_hide_modules_without_the_required_permission(): void
    {
        $ownerRole = Role::query()->create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);
        $attendance = Permission::query()->create([
            'name' => 'attendance.view',
            'label' => 'View attendance',
            'group' => 'learning',
        ]);
        $ownerRole->permissions()->attach($attendance->id);

        $owner = User::factory()->create(['email' => 'context-limited-' . Str::random(8) . '@test.local']);
        $owner->roles()->attach($ownerRole->id);
        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی دسترسی محدود',
            'slug' => 'context-limited-' . Str::random(8),
            'status' => 'active',
        ]);

        $this->actingAs($owner)
            ->get(route('owner.attendance.index', $academy))
            ->assertOk()
            ->assertSee('حضور و غیاب')
            ->assertDontSee('کلاس‌ها و گروه‌ها')
            ->assertDontSee('تکالیف و تصحیح');
    }
}
