<?php

namespace Tests\Feature;

use App\Models\Academy;
use App\Models\LegalConsent;
use App\Models\LegalDocument;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductEntitlement;
use App\Models\ProductFile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CommerceCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_is_blocked_until_both_real_legal_documents_are_published(): void
    {
        config(['services.sheykhan_transfer' => [
            'bank_name' => 'بانک آزمون',
            'account_holder' => 'آکادمی شیخان',
            'iban' => 'IR000000000000000000000000',
            'account_number' => '',
        ]]);

        $buyer = User::factory()->create(['status' => 'active']);
        $product = $this->product();

        $this->actingAs($buyer)
            ->post(route('checkout.store', $product), [
                'billing_name' => $buyer->name,
                'billing_mobile' => '09120000000',
                'consents' => [],
            ])
            ->assertStatus(503);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('legal_consents', 0);
    }

    public function test_checkout_records_exact_consent_versions_and_never_marks_a_new_order_paid(): void
    {
        config(['services.sheykhan_transfer' => [
            'bank_name' => 'بانک آزمون',
            'account_holder' => 'آکادمی شیخان',
            'iban' => 'IR000000000000000000000000',
            'account_number' => '',
        ]]);

        $buyer = User::factory()->create(['status' => 'active']);
        $product = $this->product();
        $terms = $this->legalDocument('purchase-terms', '1.0');
        $copyright = $this->legalDocument('copyright', '2.1');

        $response = $this->actingAs($buyer)->post(route('checkout.store', $product), [
            'billing_name' => $buyer->name,
            'billing_mobile' => '09120000000',
            'consents' => [
                $terms->id => '1',
                $copyright->id => '1',
            ],
        ]);

        $order = Order::query()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('pending', $order->status);
        $this->assertNull($order->paid_at);
        $this->assertTrue($order->legal_consent_completed);
        $this->assertDatabaseCount('legal_consents', 2);
        $this->assertDatabaseCount('product_entitlements', 0);

        $payment = $order->payments()->firstOrFail();
        $this->assertSame('manual_transfer', $payment->gateway);
        $this->assertSame('pending', $payment->status);
        $this->assertSame((int) $product->price, (int) $order->total);

        $recordedConsents = LegalConsent::query()->where('order_id', $order->id)->get();
        $this->assertSameCanonicalizing(
            [
                ['version' => $terms->version, 'hash' => $terms->content_hash],
                ['version' => $copyright->version, 'hash' => $copyright->content_hash],
            ],
            $recordedConsents->map(fn (LegalConsent $consent) => [
                'version' => $consent->document_version,
                'hash' => $consent->content_hash,
            ])->all()
        );
    }

    public function test_unpaid_order_cannot_download_a_product_file(): void
    {
        $buyer = User::factory()->create(['status' => 'active']);
        $product = $this->product();
        $order = Order::query()->create([
            'order_number' => 'SHK-' . Str::upper(Str::random(12)),
            'buyer_id' => $buyer->id,
            'status' => 'pending',
            'currency' => 'IRR',
            'subtotal' => 250000,
            'discount' => 0,
            'total' => 250000,
            'legal_consent_completed' => true,
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'beneficiary_id' => $buyer->id,
            'product_title_snapshot' => $product->title,
            'unit_price' => 250000,
            'quantity' => 1,
            'total_price' => 250000,
        ]);

        Storage::fake('local');
        Storage::disk('local')->put('products/source.pdf', '%PDF-1.4 source-pdf-must-never-be-returned');

        $media = Media::query()->create([
            'uploaded_by' => $buyer->id,
            'disk' => 'local',
            'path' => 'products/source.pdf',
            'original_name' => 'book.pdf',
            'file_name' => 'source.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 45,
            'checksum' => hash('sha256', 'source-pdf-must-never-be-returned'),
            'visibility' => 'private',
            'collection' => 'product-source',
            'metadata' => [],
            'status' => 'active',
        ]);

        $file = ProductFile::query()->create([
            'product_id' => $product->id,
            'media_id' => $media->id,
            'version' => '1.0',
            'is_primary' => true,
            'is_preview' => false,
            'requires_watermark' => true,
        ]);

        $order->payments()->create([
            'gateway' => 'manual_transfer',
            'amount' => 250000,
            'currency' => 'IRR',
            'status' => 'pending',
        ]);

        $this->actingAs($buyer)
            ->get(route('orders.files.download', [$order, $file]))
            ->assertForbidden();

        $this->assertDatabaseCount('product_entitlements', 0);
        Storage::disk('local')->assertExists('products/source.pdf');
    }

    public function test_only_verified_manual_transfer_confirmation_grants_product_entitlement(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create(['status' => 'active']);
        $buyer = User::factory()->create(['status' => 'active']);
        $role = Role::query()->create([
            'name' => 'مدیر آموزشگاه',
            'slug' => 'academy-owner',
            'description' => 'Owner',
        ]);
        $permission = Permission::query()->create([
            'name' => 'orders.manage',
            'label' => 'مدیریت سفارش‌ها',
            'group' => 'orders',
        ]);
        $role->permissions()->attach($permission->id);
        $owner->roles()->attach($role->id);

        $academy = Academy::query()->create([
            'owner_id' => $owner->id,
            'name' => 'آکادمی سفارش',
            'slug' => 'order-academy-' . Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
        $product = $this->product($academy->id);
        $order = Order::query()->create([
            'order_number' => 'SHK-' . Str::upper(Str::random(12)),
            'buyer_id' => $buyer->id,
            'status' => 'pending',
            'currency' => 'IRR',
            'subtotal' => 250000,
            'discount' => 0,
            'total' => 250000,
            'legal_consent_completed' => true,
        ]);
        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'beneficiary_id' => $buyer->id,
            'product_title_snapshot' => $product->title,
            'unit_price' => 250000,
            'quantity' => 1,
            'total_price' => 250000,
        ]);

        Storage::disk('local')->put('orders/proof.jpg', 'proof');
        $proof = Media::query()->create([
            'uploaded_by' => $buyer->id,
            'disk' => 'local',
            'path' => 'orders/proof.jpg',
            'original_name' => 'proof.jpg',
            'file_name' => 'proof.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 5,
            'checksum' => hash('sha256', 'proof'),
            'visibility' => 'private',
            'collection' => 'payment-proof',
            'metadata' => [],
            'status' => 'active',
        ]);
        $payment = $order->payments()->create([
            'gateway' => 'manual_transfer',
            'amount' => 250000,
            'currency' => 'IRR',
            'status' => 'pending',
            'proof_media_id' => $proof->id,
            'proof_uploaded_at' => now(),
        ]);

        $response = $this->actingAs($owner)->post(
            route('owner.orders.confirm', [$academy, $order, $payment]),
            [
                'tracking_code' => 'BANK-TRACK-2026',
                'review_note' => 'تطبیق با صورتحساب بانکی',
                'bank_statement_checked' => '1',
            ]
        );

        $response->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame('successful', $payment->fresh()->status);
        $this->assertSame('BANK-TRACK-2026', $payment->fresh()->tracking_code);
        $this->assertDatabaseHas('product_entitlements', [
            'order_item_id' => $item->id,
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);
    }

    private function product(?int $academyId = null): Product
    {
        Storage::fake('local');
        $uploader = User::query()->firstOrFail();
        $category = ProductCategory::query()->create([
            'name' => 'آزمون',
            'slug' => 'exam-' . Str::lower(Str::random(8)),
            'description' => 'دسته تست',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'academy_id' => $academyId,
            'category_id' => $category->id,
            'title' => 'آزمون آزمایشی',
            'slug' => 'exam-' . Str::lower(Str::random(12)),
            'product_type' => 'exam',
            'delivery_type' => 'download',
            'price' => 250000,
            'currency' => 'IRR',
            'status' => 'published',
            'published_at' => now()->subMinute(),
        ]);

        $path = 'products/' . $product->id . '/source.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 fake source file for the checkout tests');
        $media = Media::query()->create([
            'uploaded_by' => $uploader->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => 'source.pdf',
            'file_name' => 'source.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 48,
            'checksum' => hash('sha256', 'fake source file for the checkout tests'),
            'visibility' => 'private',
            'collection' => 'product-source',
            'metadata' => [],
            'status' => 'active',
        ]);

        ProductFile::query()->create([
            'product_id' => $product->id,
            'media_id' => $media->id,
            'version' => '1.0',
            'is_primary' => true,
            'is_preview' => false,
            'requires_watermark' => true,
        ]);

        return $product;
    }

    private function legalDocument(string $code, string $version): LegalDocument
    {
        $content = 'نسخه رسمی آزمایشی سند حقوقی شیخان. ' . str_repeat('این متن فقط برای تست گردش رضایت است. ', 8);

        return LegalDocument::query()->create([
            'code' => $code,
            'title' => $code === 'copyright' ? 'کپی‌رایت' : 'شرایط خرید',
            'version' => $version,
            'content' => $content,
            'content_hash' => hash('sha256', $content),
            'document_type' => $code === 'copyright' ? 'copyright' : 'terms_purchase',
            'required_for_purchase' => true,
            'is_active' => true,
            'published_at' => now()->subMinute(),
        ]);
    }
}
