<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\LegalConsent;
use App\Models\LegalDocument;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    public function show(Request $request, Product $product): View
    {
        $product = $this->purchasableProduct($product);
        $documents = $this->publishedPurchaseDocuments();
        $bank = (array) config('services.sheykhan_transfer', []);
        $transferReady = collect(['bank_name', 'account_holder', 'iban'])
            ->every(fn (string $key): bool => filled($bank[$key] ?? null));

        return view('commerce.checkout.show', [
            'product' => $product,
            'price' => (int) ($product->sale_price ?? $product->price),
            'documents' => $documents,
            'legalReady' => $documents->count() === 2,
            'bank' => $bank,
            'transferReady' => $transferReady,
            'canSubmit' => $documents->count() === 2 && $transferReady,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $product = $this->purchasableProduct($product);
        $documents = $this->publishedPurchaseDocuments();
        abort_unless($documents->count() === 2, 503, 'تا انتشار متن رسمی شرایط خرید و کپی‌رایت، امکان سفارش فعال نمی‌شود.');

        $bank = (array) config('services.sheykhan_transfer', []);
        abort_unless(
            filled($bank['bank_name'] ?? null)
                && filled($bank['account_holder'] ?? null)
                && filled($bank['iban'] ?? null),
            503,
            'اطلاعات تسویهٔ فروشگاه هنوز پیکربندی نشده است.'
        );

        $rules = [
            'billing_name' => ['required', 'string', 'max:160'],
            'billing_mobile' => ['required', 'string', 'max:32'],
            'consents' => ['required', 'array'],
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

        $unitPrice = (int) ($product->sale_price ?? $product->price);
        abort_if($unitPrice < 0, 422, 'قیمت محصول معتبر نیست.');

        $order = DB::transaction(function () use ($request, $product, $documents, $data, $unitPrice): Order {
            $order = Order::query()->create([
                'order_number' => 'SHK-' . now()->format('ymdHis') . '-' . Str::upper(Str::random(8)),
                'buyer_id' => $request->user()->id,
                'status' => 'pending',
                'currency' => $product->currency ?: 'IRR',
                'subtotal' => $unitPrice,
                'discount' => 0,
                'total' => $unitPrice,
                'billing_name' => trim($data['billing_name']),
                'billing_mobile' => trim($data['billing_mobile']),
                'legal_consent_completed' => true,
            ]);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'beneficiary_id' => $request->user()->id,
                'product_title_snapshot' => $product->title,
                'unit_price' => $unitPrice,
                'quantity' => 1,
                'total_price' => $unitPrice,
            ]);

            foreach ($documents as $document) {
                LegalConsent::query()->create([
                    'user_id' => $request->user()->id,
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
                'currency' => $product->currency ?: 'IRR',
                'status' => 'pending',
            ]);

            return $order;
        });

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'سفارش ثبت شد. پس از واریز، رسید را بارگذاری کن؛ دسترسی محصول فقط پس از تطبیق و تأیید پرداخت فعال می‌شود.');
    }

    private function purchasableProduct(Product $product): Product
    {
        $product->loadMissing(['category', 'files.media']);

        $hasRealPrivatePdf = $product->files->contains(function ($file): bool {
            $media = $file->media;

            return ! $file->is_preview
                && $media
                && $media->status === 'active'
                && $media->visibility === 'private'
                && strtolower((string) $media->mime_type) === 'application/pdf'
                && filled($media->path)
                && Storage::disk($media->disk)->exists($media->path);
        });

        abort_unless(
            $product->status === 'published'
                && $product->published_at
                && $product->published_at->isPast()
                && $product->category?->is_active
                && $product->delivery_type === 'download'
                && $hasRealPrivatePdf,
            404
        );

        return $product;
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
                    && hash_equals($document->content_hash, hash('sha256', $document->content))
            )
            ->unique('code')
            ->values();
    }
}
