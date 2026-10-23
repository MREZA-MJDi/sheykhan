<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SeoKeyword;
use App\Models\SeoKeywordRanking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SeoKeywordWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_dedicated_seo_specialist_can_open_keyword_workspace(): void
    {
        $specialist = $this->specialist();

        $this->actingAs($specialist)
            ->get(route('owner.seo.keywords.index'))
            ->assertOk()
            ->assertSee('مرکز کلمات کلیدی')
            ->assertSee('ورود و خروج دسته‌ای')
            ->assertSee('رتبهٔ زنده');
    }

    public function test_keyword_can_be_created_and_a_manual_position_is_saved_as_history(): void
    {
        $specialist = $this->specialist();

        $response = $this->actingAs($specialist)->post(route('owner.seo.keywords.store'), [
            'keyword' => 'کلاس آنلاین ریاضی',
            'cluster' => 'ریاضی هفتم',
            'intent' => 'informational',
            'target_url' => 'https://example.com/courses/math',
            'priority' => 'high',
            'status' => 'planned',
            'target_position' => 10,
            'observed_position' => 14,
            'observed_on' => '2026-10-20',
            'observation_notes' => 'مشاهده دستی از نتیجه جستجو',
            'notes' => 'صفحهٔ آموزشی اصلی',
        ]);

        $keyword = SeoKeyword::query()->where('keyword', 'کلاس آنلاین ریاضی')->firstOrFail();

        $response->assertRedirect(route('owner.seo.keywords.edit', $keyword));
        $this->assertDatabaseHas('seo_keywords', [
            'id' => $keyword->id,
            'keyword_key' => 'کلاس آنلاین ریاضی',
            'target_position' => 10,
            'last_observed_position' => 14,
            'last_observed_source' => 'manual',
        ]);
        $this->assertDatabaseHas('seo_keyword_rankings', [
            'seo_keyword_id' => $keyword->id,
            'position' => 14,
            'observed_on' => '2026-10-20',
            'source' => 'manual',
        ]);
    }

    public function test_normalized_duplicate_keywords_are_rejected(): void
    {
        $specialist = $this->specialist();

        $this->actingAs($specialist)->post(route('owner.seo.keywords.store'), [
            'keyword' => 'آموزش آنلاین',
            'intent' => 'informational',
            'priority' => 'medium',
            'status' => 'idea',
        ])->assertRedirect();

        $this->actingAs($specialist)->post(route('owner.seo.keywords.store'), [
            'keyword' => '  آموزش   آنلاین  ',
            'intent' => 'informational',
            'priority' => 'medium',
            'status' => 'idea',
        ])->assertSessionHasErrors('keyword');

        $this->assertSame(1, SeoKeyword::query()->count());
    }

    public function test_csv_import_creates_keyword_and_marks_imported_position_source(): void
    {
        $specialist = $this->specialist();
        $csv = "keyword,cluster,intent,target_url,priority,status,search_volume,observed_position,observed_on,notes\n"
            . "آزمون ریاضی هفتم,آزمون,commercial,https://example.com/exam,high,tracking,850,9,2026-10-22,وارد شده از فایل\n";

        $this->actingAs($specialist)
            ->post(route('owner.seo.keywords.import'), [
                'file' => UploadedFile::fake()->createWithContent('keywords.csv', $csv),
            ])
            ->assertRedirect(route('owner.seo.keywords.index'))
            ->assertSessionHasNoErrors();

        $keyword = SeoKeyword::query()->where('keyword', 'آزمون ریاضی هفتم')->firstOrFail();
        $this->assertSame(850, $keyword->search_volume);
        $this->assertSame(9, $keyword->last_observed_position);
        $this->assertSame('csv_import', $keyword->last_observed_source);
        $this->assertDatabaseHas('seo_keyword_rankings', [
            'seo_keyword_id' => $keyword->id,
            'position' => 9,
            'source' => 'csv_import',
            'observed_on' => '2026-10-22',
        ]);
    }

    public function test_keyword_workspace_is_not_available_to_non_seo_accounts(): void
    {
        $ordinaryUser = User::factory()->create();

        $this->actingAs($ordinaryUser)
            ->get(route('owner.seo.keywords.index'))
            ->assertForbidden();

        $this->actingAs($ordinaryUser)
            ->post(route('owner.seo.keywords.store'), [
                'keyword' => 'forbidden keyword',
                'intent' => 'informational',
                'priority' => 'medium',
                'status' => 'idea',
            ])
            ->assertForbidden();

        $this->assertSame(0, SeoKeyword::query()->count());
        $this->assertSame(0, SeoKeywordRanking::query()->count());
    }

    private function specialist(): User
    {
        $user = User::factory()->create([
            'email' => 'seo-keywords-' . uniqid() . '@test.local',
            'status' => 'active',
        ]);
        $role = Role::query()->where('slug', 'seo-specialist')->firstOrFail();
        $user->roles()->attach($role->id);

        return $user;
    }
}
