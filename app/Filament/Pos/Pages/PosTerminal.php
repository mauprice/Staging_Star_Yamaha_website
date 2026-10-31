<?php

namespace App\Filament\Pos\Pages;

use App\Enums\OrderItemFulfillmentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Quote;
use App\Services\StockService;
use App\Services\StripeTerminalService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PosTerminal extends Page
{
    protected string $view = 'filament.pos.pages.pos-terminal';

    // Root page of the dedicated POS panel (yamaha.test/pos) - no sidebar
    // nav in that panel, so navigation group/icon/sort don't apply here.
    protected static ?string $slug = '/';

    protected static ?string $title = 'POS Terminal';

    public string $search = '';

    /** @var array<int, array<string, mixed>> */
    public array $searchResults = [];

    /** @var array<string, array<string, mixed>> keyed by "{product_id}-{variant_id|0}" */
    public array $cart = [];

    public ?int $customerId = null;

    public string $customerSearch = '';

    /** @var array<int, array<string, mixed>> */
    public array $customerResults = [];

    public bool $showNewCustomerForm = false;

    /** @var array<string, string> */
    public array $newCustomerForm = ['name' => '', 'phone' => '', 'email' => ''];

    /** Set when the current cart was loaded from a saved quote - that quote
     *  gets marked converted once this sale completes. */
    public ?int $quoteId = null;

    public string $quoteSearch = '';

    /** @var array<int, array<string, mixed>> */
    public array $quoteResults = [];

    public string $paymentMethod = 'cash';

    public ?float $cashTendered = null;

    public ?string $lastOrderNumber = null;

    public ?int $lastOrderId = null;

    /** Card-present sale awaiting the reader, polled via wire:poll. Null once resolved. */
    public ?int $pendingCardOrderId = null;

    public ?string $cardError = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->can_access_pos ?? false;
    }

    public function isCardPaymentConfigured(): bool
    {
        return app(StripeTerminalService::class)->isConfigured();
    }

    public function updatedSearch(): void
    {
        $this->searchProducts();
    }

    public function searchProducts(): void
    {
        $term = trim($this->search);

        if ($term === '') {
            $this->searchResults = [];

            return;
        }

        $products = Product::query()
            ->where('active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('part_number', 'like', "%{$term}%")
                    ->orWhere('barcode', $term);
            })
            ->with(['variants' => fn ($q) => $q->where('active', true)])
            ->orderBy('name')
            ->limit(15)
            ->get();

        $this->searchResults = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'part_number' => $product->part_number,
            'price' => (string) $product->price,
            'is_clothing' => $product->isClothing(),
            'stock' => $product->total_stock,
            'variants' => $product->variants->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'label' => $v->label,
                'quantity' => $v->quantity,
                'price' => $v->effective_price,
            ])->all(),
        ])->all();
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        return $this->customerId ? Customer::find($this->customerId) : null;
    }

    public function updatedCustomerSearch(): void
    {
        $term = trim($this->customerSearch);

        if ($term === '') {
            $this->customerResults = [];

            return;
        }

        $this->customerResults = Customer::query()
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email'])
            ->map(fn (Customer $c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone])
            ->all();
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->customerSearch = '';
        $this->customerResults = [];
        $this->showNewCustomerForm = false;
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
    }

    public function createCustomer(): void
    {
        $name = trim($this->newCustomerForm['name'] ?? '');

        if ($name === '') {
            Notification::make()->title('Name is required')->warning()->send();

            return;
        }

        $customer = Customer::create([
            'name' => $name,
            'phone' => trim($this->newCustomerForm['phone'] ?? '') ?: null,
            'email' => trim($this->newCustomerForm['email'] ?? '') ?: null,
        ]);

        $this->newCustomerForm = ['name' => '', 'phone' => '', 'email' => ''];
        $this->selectCustomer($customer->id);
    }

    public function updatedQuoteSearch(): void
    {
        $term = trim($this->quoteSearch);

        if ($term === '') {
            $this->quoteResults = [];

            return;
        }

        $this->quoteResults = Quote::query()
            ->whereNull('converted_at')
            ->where(function ($query) use ($term) {
                $query->where('quote_number', 'like', "%{$term}%")
                    ->orWhere('customer_name', 'like', "%{$term}%")
                    ->orWhere('customer_phone', 'like', "%{$term}%");
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'quote_number', 'customer_name'])
            ->map(fn (Quote $q) => ['id' => $q->id, 'quote_number' => $q->quote_number, 'customer_name' => $q->customer_name])
            ->all();
    }

    public function saveAsQuote(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart is empty')->warning()->send();

            return;
        }

        $customer = $this->selectedCustomer;

        $quote = Quote::create([
            'customer_id' => $customer?->id,
            'user_id' => auth()->id(),
            'customer_name' => $customer->name ?? 'Walk-in customer',
            'customer_phone' => $customer->phone ?? null,
        ]);

        foreach ($this->cart as $item) {
            $quote->lines()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'product_name' => $item['product_name'],
                'variant_label' => $item['variant_label'],
                'part_number' => $item['part_number'],
                'unit_price' => $item['unit_price'],
                'quantity' => $item['quantity'],
                'line_total' => $item['line_total'],
            ]);
        }

        $this->cart = [];
        $this->search = '';
        $this->searchResults = [];
        $this->customerId = null;
        $this->customerSearch = '';
        $this->customerResults = [];

        Notification::make()->title("Quote saved — {$quote->quote_number}")->success()->send();
    }

    public function loadQuote(int $quoteId): void
    {
        $quote = Quote::with('lines')->findOrFail($quoteId);

        $this->cart = [];

        foreach ($quote->lines as $line) {
            $availableStock = $line->product_variant_id
                ? (ProductVariant::find($line->product_variant_id)?->quantity ?? 0)
                : (Product::find($line->product_id)?->stock_quantity ?? 0);

            $key = $line->product_id.'-'.($line->product_variant_id ?? '0');

            $this->cart[$key] = [
                'product_id' => $line->product_id,
                'product_variant_id' => $line->product_variant_id,
                'product_name' => $line->product_name,
                'variant_label' => $line->variant_label,
                'part_number' => $line->part_number,
                'unit_price' => (float) $line->unit_price,
                'quantity' => $line->quantity,
                'available_stock' => $availableStock,
                'line_total' => (float) $line->line_total,
            ];
        }

        $this->quoteId = $quote->id;
        $this->customerId = $quote->customer_id;
        $this->quoteSearch = '';
        $this->quoteResults = [];

        Notification::make()->title("Loaded {$quote->quote_number}")->success()->send();
    }

    public function addItem(int $productId, ?int $variantId = null): void
    {
        $product = Product::with('variants')->findOrFail($productId);
        $variant = $variantId ? $product->variants->firstWhere('id', $variantId) : null;

        if ($product->isClothing() && ! $variant) {
            Notification::make()->title('Select a size/colour first')->warning()->send();

            return;
        }

        $availableStock = $variant?->quantity ?? $product->stock_quantity;
        $key = $productId.'-'.($variantId ?? '0');
        $currentQty = $this->cart[$key]['quantity'] ?? 0;

        // No hard stock block - a sale can mix fulfilled and back-ordered
        // lines (see createPosOrder). Just let staff know when they cross
        // into back-order territory.
        if ($currentQty + 1 > $availableStock) {
            Notification::make()->title('Will be back-ordered')->body("Only {$availableStock} in stock.")->warning()->send();
        }

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $this->cart[$key] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name' => $product->name,
                'variant_label' => $variant?->label,
                'part_number' => $product->part_number,
                'unit_price' => (float) ($variant?->effective_price ?? $product->price),
                'quantity' => 1,
                'available_stock' => $availableStock,
            ];
        }

        $this->cart[$key]['line_total'] = $this->cart[$key]['unit_price'] * $this->cart[$key]['quantity'];
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        if ($quantity < 1) {
            $this->removeItem($key);

            return;
        }

        // No stock cap here either - see addItem().
        $quantity = min($quantity, 999);
        $this->cart[$key]['quantity'] = $quantity;
        $this->cart[$key]['line_total'] = $this->cart[$key]['unit_price'] * $quantity;
    }

    public function removeItem(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function getSubtotalProperty(): float
    {
        return round(collect($this->cart)->sum('line_total'), 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal;
    }

    public function getChangeDueProperty(): ?float
    {
        if ($this->cashTendered === null) {
            return null;
        }

        return round($this->cashTendered - $this->total, 2);
    }

    public function completeCashSale(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart is empty')->warning()->send();

            return;
        }

        if ($this->cashTendered === null || $this->cashTendered < $this->total) {
            Notification::make()->title('Cash tendered is less than the total')->danger()->send();

            return;
        }

        $order = DB::transaction(function () {
            $order = $this->createPosOrder(OrderStatus::Paid, PaymentMethod::Cash);

            app(StockService::class)->decrementForOrder($order);

            Payment::create([
                'order_id' => $order->id,
                'provider' => 'cash',
                'provider_reference' => Str::random(40),
                'status' => PaymentStatus::Succeeded,
                'amount' => $order->total,
                'currency' => $order->currency,
                'paid_at' => now(),
            ]);

            return $order;
        });

        $this->cashTendered = null;
        $this->finishSale($order);
    }

    public function startCardPayment(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart is empty')->warning()->send();

            return;
        }

        $terminal = app(StripeTerminalService::class);

        if (! $terminal->isConfigured()) {
            Notification::make()->title('Card reader is not set up yet')->body('Add the Stripe Terminal reader ID in .env, or take cash instead.')->danger()->send();

            return;
        }

        $this->cardError = null;

        try {
            $order = DB::transaction(function () {
                $order = $this->createPosOrder(OrderStatus::PendingPayment, PaymentMethod::CardPresent);

                Payment::create([
                    'order_id' => $order->id,
                    'provider' => 'stripe',
                    'provider_reference' => 'pending-'.$order->id,
                    'status' => PaymentStatus::Pending,
                    'amount' => $order->total,
                    'currency' => $order->currency,
                ]);

                return $order;
            });

            $intent = $terminal->createPaymentIntentForOrder($order);

            $order->payments()->where('provider', 'stripe')->update(['provider_reference' => $intent->id]);

            $terminal->sendToReader($intent);

            $this->pendingCardOrderId = $order->id;
        } catch (\Throwable $e) {
            Log::error('Failed to start POS card payment', ['error' => $e->getMessage()]);

            if (isset($order)) {
                $order->update(['status' => OrderStatus::PaymentFailed]);
            }

            $this->cardError = "Couldn't reach the card reader. Check it's online, or take cash instead.";
        }
    }

    /**
     * Polled from the Blade view (wire:poll) while a card payment is in
     * flight - the reader result arrives asynchronously via the
     * payment_intent.succeeded/payment_intent.payment_failed webhook, which
     * is the source of truth for the order's status. This just checks
     * whether that webhook has landed yet.
     */
    public function pollCardPayment(): void
    {
        if (! $this->pendingCardOrderId) {
            return;
        }

        $order = Order::find($this->pendingCardOrderId);

        if (! $order || $order->status === OrderStatus::PendingPayment) {
            return;
        }

        $this->pendingCardOrderId = null;

        if ($order->status === OrderStatus::Paid) {
            $this->finishSale($order);

            return;
        }

        $this->cardError = 'Card payment failed or was declined. Try again or take cash.';
    }

    public function cancelCardPayment(): void
    {
        if (! $this->pendingCardOrderId) {
            return;
        }

        $order = Order::find($this->pendingCardOrderId);

        try {
            app(StripeTerminalService::class)->cancelReaderAction();
        } catch (\Throwable $e) {
            Log::warning('Failed to cancel Stripe Terminal reader action', ['error' => $e->getMessage()]);
        }

        if ($order && $order->status === OrderStatus::PendingPayment) {
            $order->update(['status' => OrderStatus::Cancelled, 'cancelled_at' => now()]);
        }

        $this->pendingCardOrderId = null;
        $this->cardError = null;
    }

    private function createPosOrder(OrderStatus $status, PaymentMethod $paymentMethod): Order
    {
        $customer = $this->selectedCustomer;

        $order = Order::create([
            'source' => 'pos',
            'cashier_id' => auth()->id(),
            'customer_id' => $customer?->id,
            'placed_as_guest' => true,
            'customer_name' => $customer->name ?? 'Walk-in customer',
            'customer_email' => $customer->email ?? '',
            'customer_phone' => $customer->phone ?? null,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'currency' => 'AUD',
            'subtotal' => $this->subtotal,
            'shipping_total' => 0,
            'total' => $this->total,
            'placed_at' => now(),
            'paid_at' => $status === OrderStatus::Paid ? now() : null,
        ]);

        foreach ($this->cart as $item) {
            // Re-check stock now rather than trust the cart's cached
            // available_stock - it may have moved since the item was added.
            $availableNow = $item['product_variant_id']
                ? (ProductVariant::find($item['product_variant_id'])?->quantity ?? 0)
                : (Product::find($item['product_id'])?->stock_quantity ?? 0);

            $fulfillQty = min($item['quantity'], max(0, $availableNow));
            $backorderQty = $item['quantity'] - $fulfillQty;

            $base = [
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'product_name' => $item['product_name'],
                'variant_label' => $item['variant_label'],
                'part_number' => $item['part_number'],
                'unit_price' => $item['unit_price'],
            ];

            if ($fulfillQty > 0) {
                $order->items()->create([
                    ...$base,
                    'quantity' => $fulfillQty,
                    'line_total' => round($item['unit_price'] * $fulfillQty, 2),
                    'fulfillment_status' => OrderItemFulfillmentStatus::Fulfilled,
                    'fulfilled_at' => now(),
                ]);
            }

            if ($backorderQty > 0) {
                $order->items()->create([
                    ...$base,
                    'quantity' => $backorderQty,
                    'line_total' => round($item['unit_price'] * $backorderQty, 2),
                    'fulfillment_status' => OrderItemFulfillmentStatus::BackOrdered,
                ]);
            }
        }

        return $order;
    }

    private function finishSale(Order $order): void
    {
        if ($this->quoteId) {
            Quote::where('id', $this->quoteId)->update(['order_id' => $order->id, 'converted_at' => now()]);
        }

        $this->lastOrderNumber = $order->order_number;
        $this->lastOrderId = $order->id;
        $this->cart = [];
        $this->search = '';
        $this->searchResults = [];
        $this->cardError = null;
        $this->customerId = null;
        $this->customerSearch = '';
        $this->customerResults = [];
        $this->showNewCustomerForm = false;
        $this->quoteId = null;
        $this->quoteSearch = '';
        $this->quoteResults = [];

        Notification::make()->title("Sale complete — {$order->order_number}")->success()->send();
    }
}
