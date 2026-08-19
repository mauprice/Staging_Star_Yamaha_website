<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\OrderBankDepositMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentAvailability;
use App\Services\ShippingCalculator;
use App\Services\StripeCheckoutService;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public const AU_STATES = ['NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'ACT', 'NT'];

    private const BANK_TRANSFER_SESSION_KEY = 'checkout.pending_bank_transfer';

    public function index(ShippingCalculator $shippingCalculator, PaymentAvailability $paymentAvailability): RedirectResponse|View
    {
        $items = CartItem::forCurrentCart()->with(['product', 'variant'])->get();

        if ($items->isEmpty()) {
            return redirect()->route('yamaha.cart.index');
        }

        $subtotal = $items->sum('line_total');
        $shippingTotal = $shippingCalculator->forSubtotal($subtotal);
        $total = $subtotal + $shippingTotal;

        $availableMethods = $paymentAvailability->availableMethods();
        $defaultMethod = $paymentAvailability->defaultMethod();

        // Repopulates the form if the customer came back from the bank
        // deposit review step to change something, instead of losing what
        // they already typed.
        $prefill = session(self::BANK_TRANSFER_SESSION_KEY, []);

        return view('yamaha.checkout.index', compact('items', 'subtotal', 'shippingTotal', 'total', 'availableMethods', 'defaultMethod', 'prefill'));
    }

    public function store(Request $request, PaymentAvailability $paymentAvailability): RedirectResponse
    {
        $items = CartItem::forCurrentCart()->with(['product', 'variant'])->get();

        if ($items->isEmpty()) {
            return redirect()->route('yamaha.cart.index');
        }

        if ($this->hasStockConflict($items)) {
            return redirect()->route('yamaha.cart.index')
                ->with('error', 'Some items in your cart are no longer available in the quantity requested. Please review your cart.');
        }

        $availableMethods = $paymentAvailability->availableMethods();

        $validated = $request->validate($this->checkoutValidationRules($availableMethods));

        $paymentMethod = PaymentMethod::from($validated['payment_method']);

        // Direct deposit isn't instant like a card payment, so the customer
        // gets an explicit review-and-confirm step before the order is
        // placed and the deposit-instructions email goes out - nothing is
        // created yet, the validated details are just held in the session.
        if ($paymentMethod === PaymentMethod::BankTransfer) {
            $request->session()->put(self::BANK_TRANSFER_SESSION_KEY, $validated);

            return redirect()->route('yamaha.checkout.review');
        }

        [$order, $matchedUser] = $this->createOrder($items, $validated, $paymentMethod, $request);

        try {
            $stripeCheckoutService = app(StripeCheckoutService::class);
            $session = $stripeCheckoutService->createSessionFor($order->fresh('items'));

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'stripe',
                'provider_reference' => $session->id,
                'status' => PaymentStatus::Pending,
                'amount' => $order->total,
                'currency' => $order->currency,
            ]);

            return redirect()->away($session->url);
        } catch (\Throwable $e) {
            Log::error('Stripe checkout session creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $order->update(['status' => OrderStatus::PaymentFailed]);
            $this->restoreCartItems($order);

            return redirect()->route('yamaha.checkout.index')
                ->with('error', "We couldn't start the payment process. Your cart has been restored - please try again.");
        }
    }

    public function review(ShippingCalculator $shippingCalculator): RedirectResponse|View
    {
        $validated = session(self::BANK_TRANSFER_SESSION_KEY);

        if (! $validated) {
            return redirect()->route('yamaha.checkout.index');
        }

        $items = CartItem::forCurrentCart()->with(['product', 'variant'])->get();

        if ($items->isEmpty()) {
            return redirect()->route('yamaha.cart.index');
        }

        if ($this->hasStockConflict($items)) {
            return redirect()->route('yamaha.cart.index')
                ->with('error', 'Some items in your cart are no longer available in the quantity requested. Please review your cart.');
        }

        $subtotal = $items->sum('line_total');
        $shippingTotal = $shippingCalculator->forSubtotal($subtotal);
        $total = $subtotal + $shippingTotal;

        return view('yamaha.checkout.review', compact('items', 'subtotal', 'shippingTotal', 'total', 'validated'));
    }

    public function confirm(Request $request): RedirectResponse
    {
        $validated = session(self::BANK_TRANSFER_SESSION_KEY);

        if (! $validated) {
            return redirect()->route('yamaha.checkout.index');
        }

        $items = CartItem::forCurrentCart()->with(['product', 'variant'])->get();

        if ($items->isEmpty()) {
            $request->session()->forget(self::BANK_TRANSFER_SESSION_KEY);

            return redirect()->route('yamaha.cart.index');
        }

        if ($this->hasStockConflict($items)) {
            return redirect()->route('yamaha.cart.index')
                ->with('error', 'Some items in your cart are no longer available in the quantity requested. Please review your cart.');
        }

        [$order] = $this->createOrder($items, $validated, PaymentMethod::BankTransfer, $request);

        $ref = Str::random(40);

        Payment::create([
            'order_id' => $order->id,
            'provider' => 'bank_transfer',
            'provider_reference' => $ref,
            'status' => PaymentStatus::Pending,
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        Mail::to($order->customer_email)->queue(new OrderBankDepositMail($order->fresh('items')));

        // Cleared only once the order is actually placed, so an accidental
        // resubmit (back button, double click) can't create a second order.
        $request->session()->forget(self::BANK_TRANSFER_SESSION_KEY);

        return redirect()->route('yamaha.checkout.success', ['ref' => $ref]);
    }

    public function success(Request $request): View
    {
        // A generic opaque reference token, unguessable like Stripe's own
        // session id, so this lookup is the sole access control for the
        // success page - no auth required, but the token can't be brute
        // forced or enumerated across orders.
        $ref = $request->query('ref');

        $payment = $ref ? Payment::where('provider_reference', $ref)->first() : null;

        abort_unless($payment, 404);

        $order = $payment->order;

        return view('yamaha.checkout.success', compact('order'));
    }

    public function cancel(Order $order): View
    {
        abort_unless(in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentFailed], true), 404);

        return view('yamaha.checkout.cancel', compact('order'));
    }

    public function restore(Order $order): RedirectResponse
    {
        abort_unless(in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::PaymentFailed], true), 404);

        $this->restoreCartItems($order);

        if ($order->status === OrderStatus::PendingPayment) {
            $order->update(['status' => OrderStatus::Cancelled, 'cancelled_at' => now()]);
        }

        return redirect()->route('yamaha.cart.index')->with('success', 'Your items have been restored to your cart.');
    }

    /**
     * @return array{0: Order, 1: ?User}
     */
    private function createOrder(Collection $items, array $validated, PaymentMethod $paymentMethod, Request $request): array
    {
        $customerEmail = strtolower(trim($validated['email']));
        $customerPhone = filled($validated['phone'] ?? null) ? PhoneNumber::normalize($validated['phone']) : null;
        $billingSame = empty($validated['different_billing']);

        // Safe dedupe: run only for guests, never surface the result in the
        // response - the HTTP outcome is identical whether or not this
        // matches, so nothing here can be used as an "account exists" oracle.
        $matchedUser = null;
        if (! auth()->check()) {
            $matchedUser = User::query()
                ->where('email', $customerEmail)
                ->when($customerPhone, fn ($q, $p) => $q->orWhere('phone', $p))
                ->first();
        }

        $subtotal = $items->sum('line_total');
        $shippingTotal = app(ShippingCalculator::class)->forSubtotal($subtotal);
        $total = $subtotal + $shippingTotal;

        $order = DB::transaction(function () use (
            $items, $validated, $customerEmail, $customerPhone, $matchedUser,
            $billingSame, $subtotal, $shippingTotal, $total, $request, $paymentMethod,
        ) {
            $order = Order::create([
                'user_id' => auth()->id() ?? $matchedUser?->id,
                'placed_as_guest' => ! auth()->check(),
                'customer_name' => $validated['name'],
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'status' => $paymentMethod === PaymentMethod::BankTransfer ? OrderStatus::AwaitingBankDeposit : OrderStatus::PendingPayment,
                'payment_method' => $paymentMethod,
                'currency' => 'AUD',
                'subtotal' => $subtotal,
                'shipping_total' => $shippingTotal,
                'total' => $total,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_label' => $item->variant?->label,
                    'part_number' => $item->product->part_number,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                ]);
            }

            $order->addresses()->create([
                'type' => 'shipping',
                'full_name' => $validated['name'],
                'phone' => $customerPhone,
                'line1' => $validated['line1'],
                'line2' => $validated['line2'] ?? null,
                'suburb' => $validated['suburb'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'country' => 'AU',
            ]);

            if (! $billingSame) {
                $order->addresses()->create([
                    'type' => 'billing',
                    'full_name' => $validated['name'],
                    'phone' => $customerPhone,
                    'line1' => $validated['billing_line1'],
                    'line2' => $validated['billing_line2'] ?? null,
                    'suburb' => $validated['billing_suburb'],
                    'state' => $validated['billing_state'],
                    'postcode' => $validated['billing_postcode'],
                    'country' => 'AU',
                ]);
            }

            CartItem::whereIn('id', $items->pluck('id'))->delete();

            return $order;
        });

        return [$order, $matchedUser];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function checkoutValidationRules(array $availableMethods): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'suburb' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'in:' . implode(',', self::AU_STATES)],
            'postcode' => ['required', 'string', 'max:10'],
            'different_billing' => ['nullable', 'boolean'],
            'billing_line1' => ['required_if:different_billing,1', 'nullable', 'string', 'max:255'],
            'billing_line2' => ['nullable', 'string', 'max:255'],
            'billing_suburb' => ['required_if:different_billing,1', 'nullable', 'string', 'max:100'],
            'billing_state' => ['required_if:different_billing,1', 'nullable', 'string', 'in:' . implode(',', self::AU_STATES)],
            'billing_postcode' => ['required_if:different_billing,1', 'nullable', 'string', 'max:10'],
            'payment_method' => ['required', 'string', 'in:' . implode(',', array_map(fn ($m) => $m->value, $availableMethods))],
        ];
    }

    private function hasStockConflict(Collection $items): bool
    {
        foreach ($items as $item) {
            if ($item->quantity > $item->available_stock) {
                return true;
            }
        }

        return false;
    }

    private function restoreCartItems(Order $order): void
    {
        foreach ($order->items as $item) {
            if (! $item->product_id) {
                continue;
            }

            CartItem::create([
                'session_id' => session()->getId(),
                'user_id' => auth()->id(),
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ]);
        }
    }
}
