<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductEntitlement;
use App\Services\MediaService;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final class OrderReviewController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): View
    {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $orders = Order::query()
            ->whereHas('items.product', fn ($query) => $query->where('academy_id', $academy->id))
            ->with([
                'buyer:id,name,email,mobile',
                'items.product:id,title,academy_id',
                'payments.proofMedia:id,original_name,mime_type,size,status',
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('owner.orders.index', compact('academy', 'orders'));
    }

    public function proof(
        Academy $academy,
        Order $order,
        Payment $payment,
        Request $request,
        OwnerWorkspaceService $workspace,
        MediaService $media
    ): Response {
        $this->assertScopedOrder($request, $academy, $order, $workspace);
        abort_unless((int) $payment->order_id === (int) $order->id, 404);
        abort_unless($payment->proof_media_id && $payment->proofMedia?->status === 'active', 404);
        abort_unless($payment->proofMedia->visibility === 'private', 404);

        return $media->inline($payment->proofMedia);
    }

    public function confirm(
        Academy $academy,
        Order $order,
        Payment $payment,
        Request $request,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $this->assertScopedOrder($request, $academy, $order, $workspace);
        abort_unless((int) $payment->order_id === (int) $order->id, 404);

        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'max:128'],
            'review_note' => ['nullable', 'string', 'max:2000'],
            'bank_statement_checked' => ['accepted'],
        ]);

        DB::transaction(function () use ($academy, $order, $payment, $request, $data): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();

            abort_unless((int) $lockedPayment->order_id === (int) $lockedOrder->id, 404);
            abort_unless($lockedOrder->status === 'pending' && $lockedOrder->paid_at === null, 409);
            abort_unless($lockedPayment->status === 'pending' && $lockedPayment->proof_media_id, 409);
            abort_unless((int) $lockedPayment->amount === (int) $lockedOrder->total, 409);

            $belongsToAcademy = $lockedOrder->items()
                ->whereHas('product', fn ($query) => $query->where('academy_id', $academy->id))
                ->exists();
            abort_unless($belongsToAcademy, 404);

            $lockedPayment->forceFill([
                'status' => 'successful',
                'tracking_code' => trim($data['tracking_code']),
                'paid_at' => now(),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => $data['review_note'] ?? 'رسید با صورتحساب بانکی تطبیق داده شد.',
            ])->save();

            $lockedOrder->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'legal_consent_completed' => true,
            ])->save();

            foreach ($lockedOrder->items()->with('product')->lockForUpdate()->get() as $item) {
                $beneficiaryId = (int) ($item->beneficiary_id ?: $lockedOrder->buyer_id);
                $entitlement = ProductEntitlement::query()->firstOrNew(['order_item_id' => $item->id]);
                $entitlement->fill([
                    'user_id' => $beneficiaryId,
                    'product_id' => $item->product_id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'expires_at' => null,
                    'granted_at' => now(),
                ])->save();
            }
        });

        return back()->with('success', 'پرداخت با کد رهگیری ثبت شد و دسترسی محصولات سفارش فعال گردید.');
    }

    public function reject(
        Academy $academy,
        Order $order,
        Payment $payment,
        Request $request,
        OwnerWorkspaceService $workspace
    ): RedirectResponse {
        $this->assertScopedOrder($request, $academy, $order, $workspace);
        abort_unless((int) $payment->order_id === (int) $order->id, 404);

        $data = $request->validate([
            'review_note' => ['required', 'string', 'max:2000'],
            'bank_statement_checked' => ['accepted'],
        ]);

        DB::transaction(function () use ($academy, $order, $payment, $request, $data): void {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lockedPayment = Payment::query()->whereKey($payment->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $lockedPayment->order_id === (int) $lockedOrder->id, 404);
            abort_unless($lockedOrder->status === 'pending' && $lockedPayment->status === 'pending', 409);
            abort_unless($lockedOrder->items()->whereHas('product', fn ($query) => $query->where('academy_id', $academy->id))->exists(), 404);

            $lockedPayment->forceFill([
                'status' => 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_note' => trim($data['review_note']),
            ])->save();
            $lockedOrder->forceFill(['status' => 'payment_failed'])->save();
        });

        return back()->with('success', 'رسید رد شد؛ هیچ دسترسی خریدی صادر نشد.');
    }

    private function assertScopedOrder(
        Request $request,
        Academy $academy,
        Order $order,
        OwnerWorkspaceService $workspace
    ): void {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);
        abort_unless(
            $order->items()->whereHas('product', fn ($query) => $query->where('academy_id', $academy->id))->exists(),
            404
        );
    }
}
