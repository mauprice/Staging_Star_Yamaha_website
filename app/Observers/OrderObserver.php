<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderReadyForPickupMail;
use App\Mail\OrderReceiptMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Centralises what happens when an order's status changes, regardless of
 * where that change came from (Stripe webhook, staff editing the order in
 * the admin, POS terminal). Stock only decrements once, on the transition
 * into Paid - the guard against re-firing is the "wasn't already Paid" check,
 * not which code path triggered the update.
 */
class OrderObserver
{
    public function __construct(private readonly StockService $stockService) {}

    public function updating(Order $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        if ($order->status === OrderStatus::Paid && $order->getOriginal('status') !== OrderStatus::Paid) {
            $conflicts = $this->stockService->decrementForOrder($order);

            if ($conflicts) {
                $order->notes = trim(($order->notes ? $order->notes . "\n" : '') . "STOCK CONFLICT (needs manual review):\n" . implode("\n", $conflicts));
            }

            $order->paid_at ??= now();
        }

        if ($order->status === OrderStatus::Shipped && $order->getOriginal('status') !== OrderStatus::Shipped) {
            $order->shipped_at ??= now();
        }

        if ($order->status === OrderStatus::ReadyForPickup && $order->getOriginal('status') !== OrderStatus::ReadyForPickup) {
            $order->ready_for_pickup_at ??= now();
        }

        if ($order->status === OrderStatus::Completed && $order->getOriginal('status') !== OrderStatus::Completed) {
            $order->completed_at ??= now();
        }
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status') || blank($order->customer_email)) {
            // POS sales don't collect a customer email, so there's nothing
            // to send these to for in-store orders.
            return;
        }

        $from = $order->getOriginal('status');

        if ($order->status === OrderStatus::Paid && $from !== OrderStatus::Paid) {
            DB::afterCommit(fn () => Mail::to($order->customer_email)->queue(new OrderReceiptMail($order)));
        }

        if ($order->status === OrderStatus::Shipped && $from !== OrderStatus::Shipped) {
            DB::afterCommit(fn () => Mail::to($order->customer_email)->queue(new OrderShippedMail($order)));
        }

        if ($order->status === OrderStatus::ReadyForPickup && $from !== OrderStatus::ReadyForPickup) {
            DB::afterCommit(fn () => Mail::to($order->customer_email)->queue(new OrderReadyForPickupMail($order)));
        }

        if ($order->status === OrderStatus::Completed && $from !== OrderStatus::Completed) {
            DB::afterCommit(fn () => Mail::to($order->customer_email)->queue(new OrderCompletedMail($order)));
        }
    }
}
