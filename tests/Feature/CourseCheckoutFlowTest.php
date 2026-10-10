<?php

namespace Tests\\Feature;

use App\\Models\\Academy;
use App\\Models\\Course;
use App\\Models\\CourseEnrollment;
use App\\Models\\FinancialTransaction;
use App\\Models\\LegalDocument;
use App\\Models\\Media;
use App\\Models\\Order;
use App\\Models\\OrderItem;
use App\\Models\\User;
use App\\Services\\CourseAccessService;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Illuminate\\Support\\Facades\\Storage;
use Illuminate\\Support\\Str;
use Tests\\TestCase;

class CourseCheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_checkout_creates_pending_order_but_does_not_grant_access(): void
    {
        config(['services.sheykhan_transfer' => [
            'bank_name' => 'بانک آزمون',
            'account_holder' => 'آکادمی شیخان',
            'iban' => 'IR000000000000000000000000',
            'account_number' => '',
        ]]);

        [$academy, $course, $student] = $this->preparedCourseAndStudent();

        $response = $this->actingAs($student)->post(route('checkout.course.store', $course), [
            'billing_name' => $student->name,
            'billing_mobile' => '09120000000',
            'beneficiary_id' => $student->id,
            'consents' => LegalDocument::query()->whereIn('code', ['purchase-terms', 'copyright'])
                ->pluck('id')->mapWithKeys(fn ($id) => [$id => '1'])->all(),
        ]);

        $order = Order::query()->firstOrFail();
        $item = $order->items()->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('pending', $order->status);
        $this->assertNull($order->paid_at);
        $this->assertSame($course->id, (int) $item->course_id);
        $this->assertNull($item->product_id);
        $this->assertSame($student->id, (int) $item->beneficiary_id);
        $this->assertDatabaseMissing('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
            'payment_status' => 'paid',
        ]);
        $this->assertFalse(app(CourseAccessService::class)->canAccess($student, $course));
    }

    public function test_parent_purchases_for_an_eligible_child_not_for_the_parent_account(): void
    {
        config(['services.sheykhan_transfer' => [
            'bank_name' => 'بانک آزمون',
            'account_holder' => 'آکادمی شیخان',
            'iban' => 'IR000000000000000000000000',
            'account_number' => '',
        ]]);

        [$academy, $course] = $this->preparedCourseAndStudent();
        $parent = User::query()->where('email', 'parent.armin@sheykhan.test')->firstOrFail();
        $child = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();
        $this->assertTrue($parent->children()->whereKey($child->id)->exists());

        $response = $this->actingAs($parent)->post(route('checkout.course.store', $course), [
            'billing_name' => $parent->name,
            'billing_mobile' => '09120000000',
            'beneficiary_id' => $child->id,
            'consents' => LegalDocument::query()->whereIn('code', ['purchase-terms', 'copyright'])
                ->pluck('id')->mapWithKeys(fn ($id) => [$id => '1'])->all(),
        ]);

        $order = Order::query()->firstOrFail();
        $item = $order->items()->firstOrFail();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame($parent->id, (int) $order->buyer_id);
        $this->assertSame($child->id, (int) $item->beneficiary_id);
        $this->assertSame('pending', $order->status);
        $this->assertFalse(app(CourseAccessService::class)->canAccess($child, $course));
    }

    public function test_verified_transfer_grants_the_course_to_the_selected_student_and_records_finance(): void
    {
        Storage::fake('local');
        config(['services.sheykhan_transfer' => [
            'bank_name' => 'بانک آزمون',
            'account_holder' => 'آکادمی شیخان',
            'iban' => 'IR000000000000000000000000',
            'account_number' => '',
        ]]);

        [$academy, $course, $student] = $this->preparedCourseAndStudent();
        $this->actingAs($student)->post(route('checkout.course.store', $course), [
            'billing_name' => $student->name,
            'billing_mobile' => '09120000000',
            'beneficiary_id' => $student->id,
            'consents' => LegalDocument::query()->whereIn('code', ['purchase-terms', 'copyright'])
                ->pluck('id')->mapWithKeys(fn ($id) => [$id => '1'])->all(),
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $item = $order->items()->firstOrFail();
        $payment = $order->payments()->firstOrFail();

        Storage::disk('local')->put('orders/course-proof.jpg', 'proof');
        $proof = Media::query()->create([
            'uploaded_by' => $student->id,
            'disk' => 'local',
            'path' => 'orders/course-proof.jpg',
            'original_name' => 'proof.jpg',
            'file_name' => 'course-proof.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size' => 5,
            'checksum' => hash('sha256', 'proof'),
            'visibility' => 'private',
            'collection' => 'payment-proof',
            'metadata' => [],
            'status' => 'active',
        ]);
        $payment->update(['proof_media_id' => $proof->id, 'proof_uploaded_at' => now()]);

        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $response = $this->actingAs($owner)->post(
            route('owner.orders.confirm', [$academy, $order, $payment]),
            [
                'tracking_code' => 'BANK-COURSE-' . Str::upper(Str::random(6)),
                'review_note' => 'تطبیق با صورت‌حساب بانکی',
                'bank_statement_checked' => '1',
            ]
        );

        $response->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
        $this->assertSame('successful', $payment->fresh()->status);

        $enrollment = CourseEnrollment::query()
            ->where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->firstOrFail();
        $this->assertSame('active', $enrollment->status);
        $this->assertSame('paid', $enrollment->payment_status);
        $this->assertGreaterThanOrEqual((int) $item->unit_price, (int) $enrollment->paid_amount);
        $this->assertTrue(app(CourseAccessService::class)->canAccess($student, $course));
        $this->assertDatabaseHas('financial_transactions', [
            'enrollment_id' => $enrollment->id,
            'type' => 'enrollment_payment',
            'status' => 'completed',
            'amount' => $item->total_price,
            'reference' => 'course-order-' . $order->id . '-item-' . $item->id,
        ]);
    }

    private function preparedCourseAndStudent(): array
    {
        $this->seed();

        $academy = Academy::query()->where('slug', 'sheykhan-academy')->firstOrFail();
        $owner = User::query()->where('email', 'owner@sheykhan.test')->firstOrFail();
        $student = User::query()->where('email', 'student.armin@sheykhan.test')->firstOrFail();

        foreach ([
            ['purchase-terms', 'شرایط خرید دوره'],
            ['copyright', 'قوانین حق نشر دوره'],
        ] as [$code, $title]) {
            $document = LegalDocument::query()->where('code', $code)->firstOrFail();
            $body = $title . '؛ نسخه منتشرشده برای آزمون مسیر خرید. ' . str_repeat('اطلاعات رسمی خرید. ', 8);
            $document->update([
                'title' => $title,
                'content' => $body,
                'content_hash' => hash('sha256', $body),
                'is_active' => true,
                'required_for_purchase' => true,
                'published_at' => now()->subMinute(),
            ]);
        }

        $course = Course::query()->create([
            'academy_id' => $academy->id,
            'created_by' => $owner->id,
            'title' => 'دوره خرید آزمایشی',
            'slug' => 'paid-checkout-' . Str::lower(Str::random(10)),
            'short_description' => 'دوره برای آزمون چرخه خرید',
            'description' => 'مسیر خرید واقعی و فعال‌سازی پس از تأیید وجه.',
            'level' => 'متوسط',
            'status' => 'published',
            'access_type' => 'paid',
            'price' => 250000,
            'duration_minutes' => 600,
            'published_at' => now()->subMinute(),
        ]);

        return [$academy, $course, $student];
    }
}
