<?php

namespace App\\Http\\Controllers\\Commerce;

use App\\Http\\Controllers\\Controller;
use App\\Models\\Course;
use App\\Models\\LegalConsent;
use App\\Models\\LegalDocument;
use App\\Models\\Order;
use App\\Models\\OrderItem;
use App\\Models\\User;
use Illuminate\\Http\\RedirectResponse;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Collection;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Str;
use Illuminate\\Validation\\ValidationException;
use Illuminate\\View\\View;

final class CourseCheckoutController extends Controller
{
    public function show(Request $request, Course $course): View
    {
        $course = $this->purchasableCourse($course);
        $buyer = $request->user();
        $documents = $this->publishedPurchaseDocuments();
        $bank = (array) config('services.sheykhan_transfer', []);
        $transferReady = collect(['bank_name', 'account_holder', 'iban'])
            ->every(fn (string $key): bool => filled($bank[$key] ?? null));
        [$beneficiaries, $accessMessage] = $this->eligibleBeneficiaries($buyer, $course);
        $price = (int) round((float) $course->price);

        return view('commerce.course-checkout.show', [
            'course' => $course,
            'price' => $price,
            'documents' => $documents,
            'legalReady' => $documents->count() === 2,
            'transferReady' => $transferReady,
            'bank' => $bank,
            'beneficiaries' => $beneficiaries,
            'accessMessage' => $accessMessage,
            'isParent' => $buyer->hasRole('parent'),
            'canPurchase' => $beneficiaries->isNotEmpty(),
            'canSubmit' => $beneficiaries->isNotEmpty() && $documents->count() === 2 && $transferReady && $price > 0,
            'selectedBeneficiaryId' => old('beneficiary_id', $beneficiaries->first()?->id),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $course = $this->purchasableCourse($course);
        $buyer = $request->user();
        $documents = $this->publishedPurchaseDocuments();

        abort_unless($documents->count() === 2, 503, 'تا انتشار نسخهٔ رسمی شرایط خرید و کپی‌رایت، امکان سفارش فعال نمی‌شود.');
        $bank = (array) config('services.sheykhan_transfer', []);
        abort_unless(
            filled($bank['bank_name'] ?? null)
                && filled($bank['account_holder'] ?? null)
                && filled($bank['iban'] ?? null),
            503,
            'اطلاعات تسویهٔ آموزشگاه هنوز پیکربندی نشده است.'
        );

        $rules = [
            'billing_name' => ['required', 'string', 'max:160'],
            'billing_mobile' => ['required', 'string', 'max:32'],
            'consents' => ['required', 'array'],
            'beneficiary_id' => $buyer->hasRole('parent') ? ['required', 'integer'] : ['nullable', 'integer'],
        ];
        foreach ($documents as $document) {
            $rules['consents.' . $document->id] = ['accepted'];
        }

        $data = $request->validate($rules);
        foreach ($documents as $document) {
            if (! hash_equals($document->content_hash, hash('sha256', $document->content))) {
                throw ValidationException::withMessages([
                    'consents' => 'نسخهٔ سند حقوقی تغییر کرده است. صفحه را تازه‌سازی و دوباره تأیید کن.',
                ]);
            }
        }

        $beneficiary = $this->resolveBeneficiary($buyer, $course, $data['beneficiary_id'] ?? null);
        $unitPrice = (int) round((float) $course->price);
        abort_if($unitPrice <= 0, 422, 'قیمت این دوره معتبر نیست.');

        $order = DB::transaction(function () use ($request, $buyer, $beneficiary, $course, $documents, $data, $unitPrice): Order {
            $lockedCourse = Course::query()->whereKey($course->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedCourse->isPublished() && $lockedCourse->requiresPayment(), 409, 'وضعیت دوره تغییر کرده است؛ صفحه را تازه‌سازی کن.');
            abort_unless((int) round((float) $lockedCourse->price) === $unitPrice, 409, 'قیمت دوره تغییر کرده است؛ صفحه را تازه‌سازی کن.');

            $order = Order::query()->create([
                'order_number' => 'SHK-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(8)),
                'buyer_id' => $buyer->id,
                'status' => 'pending',
                'currency' => 'IRR',
                'subtotal' => $unitPrice,
                'discount' => 0,
                'total' => $unitPrice,
                'billing_name' => trim($data['billing_name']),
                'billing_mobile' => trim($data['billing_mobile']),
                'legal_consent_completed' => true,
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => null,
                'course_id' => $course->id,
                'beneficiary_id' => $beneficiary->id,
                'product_title_snapshot' => $course->title,
                'unit_price' => $unitPrice,
                'quantity' => 1,
                'total_price' => $unitPrice,
            ]);

            foreach ($documents as $document) {
                LegalConsent::query()->create([
                    'user_id' => $buyer->id,
                    'document_id' => $document->id,
                    'order_id' => $order->id,
                    'document_version' => $document->version,
                    'consent_type' => 'accepted',
                    'content_hash' => hash('sha256', $document->content),
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
                    'accepted_at' => now(),
                ]);
            }

            $order->payments()->create([
                'gateway' => 'manual_transfer',
                'amount' => $unitPrice,
                'currency' => 'IRR',
                'status' => 'pending',
            ]);

            return $order;
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'سفارش دوره ثبت شد. دسترسی آموزشی فقط پس از تطبیق و تأیید واقعی وجه فعال می‌شود.');
    }

    private function purchasableCourse(Course $course): Course
    {
        return Course::query()->published()
            ->whereKey($course->id)
            ->where('access_type', 'paid')
            ->whereHas('academy', fn ($query) => $query->where('status', 'active'))
            ->with([
                'academy:id,name,owner_id',
                'media' => fn ($query) => $query->where('visibility', 'public')->where('status', 'active')->orderByPivot('sort_order'),
            ])
            ->firstOrFail();
    }

    private function eligibleBeneficiaries(User $buyer, Course $course): array
    {
        if ($buyer->hasRole('student')) {
            $membership = $buyer->academies()->whereKey($course->academy_id)
                ->wherePivot('role', 'student')->wherePivot('status', 'active')->exists();
            if (! $membership) {
                return [collect(), 'حساب دانش‌آموز باید عضویت فعال در آموزشگاه برگزارکننده داشته باشد.'];
            }
            if ($buyer->enrollments()->where('course_id', $course->id)->fullyPaid()->exists()) {
                return [collect(), 'دسترسی این حساب به دوره قبلاً فعال شده است. آن را از «دوره‌های من» باز کن.'];
            }
            if ($this->hasPendingOrder($buyer, $course, $buyer->id)) {
                return [collect(), 'برای این دوره یک سفارش در انتظار بررسی داری؛ همان سفارش را پیگیری کن تا خرید تکراری ثبت نشود.'];
            }

            return [collect([$buyer]), null];
        }

        if (! $buyer->hasRole('parent')) {
            return [collect(), 'خرید مستقیم دوره فقط برای حساب دانش‌آموز یا والد مجاز است.'];
        }

        $parentMembership = $buyer->academies()->whereKey($course->academy_id)
            ->wherePivot('role', 'parent')->wherePivot('status', 'active')->exists();
        if (! $parentMembership) {
            return [collect(), 'برای خرید این دوره، حساب والد باید عضویت فعال در آموزشگاه برگزارکننده داشته باشد.'];
        }

        $children = $buyer->children()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'student'))
            ->whereHas('academies', fn ($query) => $query
                ->where('academies.id', $course->academy_id)
                ->where('academy_user.role', 'student')
                ->where('academy_user.status', 'active'))
            ->whereDoesntHave('enrollments', fn ($query) => $query->where('course_id', $course->id)->fullyPaid())
            ->orderBy('users.name')
            ->get(['users.id', 'users.name'])
            ->reject(fn (User $child) => $this->hasPendingOrder($buyer, $course, $child->id))
            ->values();

        return $children->isEmpty()
            ? [collect(), 'فرزندِ دارای عضویت فعال و واجد شرایط برای خرید این دوره پیدا نشد.']
            : [$children, null];
    }

    private function resolveBeneficiary(User $buyer, Course $course, mixed $beneficiaryId): User
    {
        [$eligible, $message] = $this->eligibleBeneficiaries($buyer, $course);
        abort_unless($eligible->isNotEmpty(), 403, $message ?: 'این حساب امکان خرید این دوره را ندارد.');
        if ($buyer->hasRole('student')) {
            return $buyer;
        }

        $beneficiary = $eligible->firstWhere('id', (int) $beneficiaryId);
        abort_unless($beneficiary, 422, 'دانش‌آموز انتخاب‌شده دیگر برای این دوره واجد شرایط نیست.');

        return $beneficiary;
    }

    private function hasPendingOrder(User $buyer, Course $course, int $beneficiaryId): bool
    {
        return OrderItem::query()->where('course_id', $course->id)
            ->where('beneficiary_id', $beneficiaryId)
            ->whereHas('order', fn ($query) => $query->where('buyer_id', $buyer->id)->where('status', 'pending'))
            ->exists();
    }

    private function publishedPurchaseDocuments(): Collection
    {
        return LegalDocument::query()
            ->whereIn('code', ['purchase-terms', 'copyright'])
            ->where('required_for_purchase', true)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('id')
            ->get()
            ->filter(fn (LegalDocument $document): bool =>
                filled(trim((string) $document->content))
                    && filled($document->content_hash)
                    && hash_equals($document->content_hash, hash('sha256', $document->content)))
            ->unique('code')
            ->values();
    }
}
