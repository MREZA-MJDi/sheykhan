<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\Course;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductEntitlement;
use App\Models\ProductFile;
use App\Models\SpotPlayerLicense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProtectedMediaEventRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_retention_command_is_a_dry_run_by_default_and_deletes_only_after_confirmation(): void
    {
        $user = User::factory()->create();
        $academy = Academy::query()->create([
            'owner_id' => $user->id,
            'name' => 'آکادمی نگهداری رویداد',
            'slug' => 'retention-' . uniqid(),
            'status' => 'active',
        ]);
        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $user->id,
            'title' => 'دوره نگهداری رویداد',
            'slug' => 'retention-course-' . uniqid(),
            'status' => 'published',
            'access_type' => 'free',
            'price' => 0,
            'published_at' => now()->subDay(),
        ]);
        $license = SpotPlayerLicense::query()->create([
            'course_id' => $course->id,
            'student_id' => $user->id,
            'external_license_id' => 'retention-license-' . uniqid(),
            'license_key' => 'test-license-key',
            'license_url' => 'https://example.test/license',
            'status' => 'active',
            'issued_at' => now()->subDay(),
            'provider_course_id' => 'retention-course',
            'provider_mode' => 'test',
        ]);

        $media = Media::query()->create([
            'uploaded_by' => $user->id,
            'disk' => 'local',
            'path' => 'retention/sample.pdf',
            'original_name' => 'sample.pdf',
            'file_name' => 'sample.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 1024,
            'visibility' => 'private',
            'collection' => 'retention-test',
            'status' => 'active',
        ]);

        $category = ProductCategory::query()->create([
            'name' => 'فایل آزمایشی',
            'slug' => 'retention-category-' . uniqid(),
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'academy_id' => $academy->id,
            'category_id' => $category->id,
            'created_by' => $user->id,
            'title' => 'فایل آزمایشی',
            'slug' => 'retention-product-' . uniqid(),
            'product_type' => 'digital',
            'delivery_type' => 'download',
            'price' => 1000,
            'currency' => 'IRR',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
        $order = Order::query()->create([
            'order_number' => 'RET-' . strtoupper(bin2hex(random_bytes(4))),
            'buyer_id' => $user->id,
            'status' => 'paid',
            'currency' => 'IRR',
            'subtotal' => 1000,
            'discount' => 0,
            'total' => 1000,
            'legal_consent_completed' => true,
            'paid_at' => now()->subDay(),
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_title_snapshot' => $product->title,
            'unit_price' => 1000,
            'quantity' => 1,
            'total_price' => 1000,
        ]);
        $entitlement = ProductEntitlement::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => $item->id,
            'status' => 'active',
            'granted_at' => now()->subDay(),
        ]);
        $productFile = ProductFile::query()->create([
            'product_id' => $product->id,
            'media_id' => $media->id,
            'version' => '1.0',
            'is_primary' => true,
            'is_preview' => false,
            'requires_watermark' => false,
        ]);

        $old = now()->subDays(60)->toDateTimeString();
        $recent = now()->subDays(5)->toDateTimeString();

        foreach ([$old, $recent] as $viewedAt) {
            DB::table('protected_file_views')->insert([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'product_file_id' => $productFile->id,
                'entitlement_id' => $entitlement->id,
                'event_type' => 'open',
                'viewed_at' => $viewedAt,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);

            DB::table('protected_learning_media_views')->insert([
                'user_id' => $user->id,
                'media_id' => $media->id,
                'academy_id' => $academy->id,
                'event_type' => 'open',
                'viewed_at' => $viewedAt,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);

            DB::table('spot_player_playback_views')->insert([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'spot_player_license_id' => $license->id,
                'event_type' => 'player_open',
                'viewed_at' => $viewedAt,
                'created_at' => $viewedAt,
                'updated_at' => $viewedAt,
            ]);
        }

        $tables = [
            'protected_file_views',
            'protected_learning_media_views',
            'spot_player_playback_views',
        ];

        $this->artisan('protected-media-events:prune', ['--days' => 30])
            ->expectsOutputToContain('پیش‌نمایش')
            ->assertExitCode(0);

        foreach ($tables as $table) {
            $this->assertSame(2, DB::table($table)->count(), $table . ' must remain untouched during dry run');
        }

        $this->artisan('protected-media-events:prune', ['--days' => 30, '--force' => true])
            ->expectsOutputToContain('حذف شد')
            ->assertExitCode(0);

        foreach ($tables as $table) {
            $this->assertSame(1, DB::table($table)->count(), $table . ' should keep recent history');
            $this->assertSame($recent, DB::table($table)->value('viewed_at'));
        }
    }

    public function test_retention_command_rejects_short_or_missing_periods(): void
    {
        $this->artisan('protected-media-events:prune')
            ->expectsOutputToContain('--days')
            ->assertExitCode(2);

        $this->artisan('protected-media-events:prune', ['--days' => 14, '--force' => true])
            ->expectsOutputToContain('حداقل ۳۰')
            ->assertExitCode(2);
    }
}
