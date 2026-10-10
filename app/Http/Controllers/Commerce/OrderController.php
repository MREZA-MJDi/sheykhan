<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MediaService;
use App\Services\ProductDeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

final class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $this->assertBuyer($request, $order);

        $order->load([
            'items.product.category',
            'items.product.files' => fn ($query) => $query->where('is_preview', false)->orderByDesc('is_primary'),
            'items.course:id,academy_id,title,slug,price',
            'items.beneficiary:id,name',
            'consents.document',
            'payments.proofMedia',
        ]);

        return view('commerce.orders.show', [
            'order' => $order,
            'payment' => $order->payments()->orderByDesc('id')->first(),
            'bank' => (array) config('services.sheykhan_transfer', []),
        ]);
    }

    public function uploadProof(
        Request $request,
        Order $order,
        MediaService $media
    ): RedirectResponse {
        $this->assertBuyer($request, $order);
        abort_unless(
            in_array($order->status, ['pending', 'payment_failed'], true),
            409,
            'برای این سفارش امکان ارسال رسید وجود ندارد.'
        );

        $data = $request->validate([
            'proof' => ['required', 'file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);

        $newMedia = $media->upload($data['proof'], null, [
            'disk' => 'local',
            'directory' => 'orders/' . $order->id . '/payment-proofs',
            'collection' => 'payment-proof',
            'visibility' => 'private',
        ]);

        $oldMediaId = null;

        try {
            DB::transaction(function () use ($request, $order, $newMedia, &$oldMediaId): void {
                $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
                abort_unless((int) $lockedOrder->buyer_id === (int) $request->user()->id, 404);
                abort_unless(
                    in_array($lockedOrder->status, ['pending', 'payment_failed'], true),
                    409,
                    'وضعیت سفارش تغییر کرده است؛ صفحه را تازه‌سازی کن.'
                );

                if ($lockedOrder->status === 'payment_failed') {
                    $rejectedPayment = $lockedOrder->payments()
                        ->where('status', 'rejected')
                        ->orderByDesc('id')
                        ->firstOrFail();

                    // Preserve the rejected attempt for audit; each retry receives a new payment row.
                    $payment = $lockedOrder->payments()->create([
                        'gateway' => $rejectedPayment->gateway ?: 'manual_transfer',
                        'amount' => $lockedOrder->total,
                        'currency' => $lockedOrder->currency ?: 'IRR',
                        'status' => 'pending',
                    ]);

                    $lockedOrder->forceFill(['status' => 'pending'])->save();
                } else {
                    $payment = $lockedOrder->payments()
                        ->where('status', 'pending')
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->firstOrFail();

                    $oldMediaId = $payment->proof_media_id;
                }

                $payment->forceFill([
                    'proof_media_id' => $newMedia->id,
                    'proof_uploaded_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                    'tracking_code' => null,
                ])->save();
            });
        } catch (Throwable $exception) {
            $media->delete($newMedia);
            throw $exception;
        }

        if ($oldMediaId && (int) $oldMediaId !== (int) $newMedia->id) {
            $oldMedia = Media::query()->find($oldMediaId);
            if ($oldMedia && ! Payment::query()->where('proof_media_id', $oldMedia->id)->exists() && $oldMedia->attachments()->doesntExist()) {
                $media->delete($oldMedia);
            }
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'رسید در فضای خصوصی ذخیره شد. سفارش تا بررسی واقعی تراکنش توسط تیم شیخان در انتظار تأیید می‌ماند.');
    }

    public function download(
        Request $request,
        Order $order,
        \App\Models\ProductFile $file,
        ProductDeliveryService $delivery
    ) {
        $this->assertBuyer($request, $order);

        return $delivery->download($request->user(), $order, $file, $request);
    }

    private function assertBuyer(Request $request, Order $order): void
    {
        abort_unless((int) $order->buyer_id === (int) $request->user()->id, 404);
    }
}
