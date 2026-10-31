<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderAccountMatchMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Services\PayPalCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Safety net alongside CheckoutController::paypalReturn() - normally the
 * customer's browser redirect back from PayPal is what captures the
 * payment, but this catches it if they close the tab before that happens,
 * or if PayPal reports a later async outcome (e.g. a denied capture).
 */
class PayPalWebhookController extends Controller
{
    public function handle(Request $request, PayPalCheckoutService $paypal): Response
    {
        $event = $request->json()->all();

        if (! $paypal->verifyWebhookSignature($request->headers->all(), $event)) {
            Log::warning('PayPal webhook signature verification failed', ['type' => $event['event_type'] ?? null]);

            return response('Invalid signature', 400);
        }

        $eventId = $event['id'] ?? null;

        if (! $eventId) {
            return response('Missing event id', 400);
        }

        // Idempotency: PayPal may redeliver the same event more than once.
        if (PaymentWebhookEvent::where('event_id', $eventId)->exists()) {
            return response('Already processed', 200);
        }

        $webhookEvent = PaymentWebhookEvent::create([
            'provider' => 'paypal',
            'event_id' => $eventId,
            'type' => $event['event_type'] ?? 'unknown',
            'payload' => $event,
        ]);

        match ($event['event_type'] ?? null) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($event),
            'PAYMENT.CAPTURE.DENIED' => $this->handleCaptureDenied($event),
            default => Log::info('Unhandled PayPal webhook event type', ['type' => $event['event_type'] ?? null]),
        };

        $webhookEvent->update(['processed_at' => now()]);

        return response('OK', 200);
    }

    private function handleCaptureCompleted(array $event): void
    {
        $paypalOrderId = $event['resource']['supplementary_data']['related_ids']['order_id'] ?? null;

        if (! $paypalOrderId) {
            Log::error('PayPal PAYMENT.CAPTURE.COMPLETED missing related order id', ['event_id' => $event['id'] ?? null]);

            return;
        }

        $order = DB::transaction(function () use ($paypalOrderId, $event) {
            $payment = Payment::where('provider', 'paypal')->where('provider_reference', $paypalOrderId)->lockForUpdate()->first();

            if (! $payment) {
                return null;
            }

            $order = Order::where('id', $payment->order_id)->lockForUpdate()->first();

            if (! $order || $payment->status === PaymentStatus::Succeeded) {
                return $order;
            }

            if ($order->status !== OrderStatus::Paid) {
                $order->update(['status' => OrderStatus::Paid]);
            }

            $payment->update([
                'status' => PaymentStatus::Succeeded,
                'paid_at' => now(),
                'raw_response' => $event,
            ]);

            return $order;
        });

        if (! $order) {
            Log::error('PayPal PAYMENT.CAPTURE.COMPLETED referenced an unknown order', ['paypal_order_id' => $paypalOrderId]);

            return;
        }

        if ($order->placed_as_guest && $order->user_id) {
            $order->loadMissing('user');
            Mail::to($order->user->email)->queue(new OrderAccountMatchMail($order));
        }
    }

    private function handleCaptureDenied(array $event): void
    {
        $paypalOrderId = $event['resource']['supplementary_data']['related_ids']['order_id'] ?? null;

        if (! $paypalOrderId) {
            return;
        }

        $payment = Payment::where('provider', 'paypal')->where('provider_reference', $paypalOrderId)->first();

        if ($payment && $payment->order && $payment->order->status === OrderStatus::PendingPayment) {
            $payment->order->update(['status' => OrderStatus::PaymentFailed]);
            $payment->update(['status' => PaymentStatus::Failed, 'raw_response' => $event]);
        }
    }
}
