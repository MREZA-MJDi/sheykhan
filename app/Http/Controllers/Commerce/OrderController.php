<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Order;
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
        abort_unless($order->status === 'pending', 409, 'برای این سفارش امکان ارسال رسید وجود ندارد.');

        $payment = $order->payments()
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->firstOrFail();

        $data = $request->validate([
            'proof' => ['required', 'file', 'max:12288', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);

        $newMedia = $media->upload($data['proof'], null, [
            'disk' => 'local',
            'directory' => 'orders/' . $order->id . '/payment-proofs',
            'collection' => 'payment-proof',
            'visibility' => 'private',
        ]);
        $oldMediaId = $payment->proof_media_id;

        try {
            DB::transaction(function () use ($payment, $newMedia): void {
                $locked = $payment->newQuery()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->status === 'pending', 409);

                $locked->forceFill([
                    'proof_media_id' => $newMedia->id,
                    'proof_uploaded_at' => now(),
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                ])->save();
            });
        } catch (Throwable $exception) {
            $media->delete($newMedia);
            throw $exception;
        }

        if ($oldMediaId && (int) $oldMediaId !== (int) $newMedia->id) {
            $oldMedia = Media::query()->find($oldMediaId);
            if ($oldMedia && ! $oldMedia->payments()->exists() && $oldMedia->attachments()->doesntExist()) {
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
