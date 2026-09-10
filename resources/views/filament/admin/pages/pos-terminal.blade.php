<x-filament-panels::page>
<style>
.pos-wrap { display: flex; gap: 20px; align-items: flex-start; }
.pos-col-main { flex: 1 1 60%; min-width: 0; }
.pos-col-side { flex: 1 1 40%; max-width: 380px; position: sticky; top: 20px; }

.pos-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.pos-card-header {
    padding: 16px 20px 14px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.pos-card-header h3 { margin: 0; font-size: 15px; font-weight: 600; color: #f9fafb; }

.pos-input {
    width: 100%;
    height: 42px;
    border-radius: 8px;
    border: 1px solid #374151;
    background: #111827;
    color: #f9fafb;
    font-size: 14px;
    padding: 0 12px;
    outline: none;
}

.pos-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.15); }

.pos-search-body { padding: 16px 20px; }

.pos-result {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #374151;
}
.pos-result:last-child { border-bottom: none; }

.pos-result-name { font-size: 13px; font-weight: 600; color: #f3f4f6; }
.pos-result-meta { font-size: 12px; color: #9ca3af; }

.pos-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 8px;
    background: #f59e0b;
    color: #fff;
    font-size: 13px;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.pos-btn:hover { background: #d97706; }
.pos-btn:disabled { opacity: .5; cursor: not-allowed; }
.pos-btn-sm { padding: 5px 10px; font-size: 12px; }
.pos-btn-ghost { background: transparent; border: 1px solid #4b5563; color: #d1d5db; }
.pos-btn-ghost:hover { background: #374151; }
.pos-btn-danger { background: #ef4444; }
.pos-btn-danger:hover { background: #dc2626; }
.pos-btn-block { width: 100%; justify-content: center; padding: 12px; font-size: 15px; }

.pos-variant-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }

.pos-cart-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    border-bottom: 1px solid #374151;
}
.pos-cart-row:last-child { border-bottom: none; }

.pos-cart-name { flex: 1; min-width: 0; }
.pos-cart-name strong { display: block; font-size: 13px; color: #f3f4f6; }
.pos-cart-name span { font-size: 12px; color: #9ca3af; }

.pos-qty-input { width: 56px; height: 32px; text-align: center; border-radius: 6px; border: 1px solid #374151; background: #111827; color: #f9fafb; }

.pos-line-total { width: 80px; text-align: right; font-size: 13px; font-weight: 600; color: #f3f4f6; }

.pos-totals-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 20px;
    font-size: 13px;
    color: #d1d5db;
}
.pos-totals-row.grand { font-size: 16px; font-weight: 700; color: #f9fafb; border-top: 1px solid #374151; padding-top: 14px; margin-top: 4px; }

.pos-empty { padding: 24px 20px; text-align: center; color: #6b7280; font-size: 13px; }

.pos-success { padding: 16px 20px; background: rgba(16,185,129,.1); border-top: 1px solid #374151; }
.pos-success p { margin: 0 0 8px; color: #34d399; font-size: 13px; font-weight: 600; }
</style>

<div class="pos-wrap">
    <div class="pos-col-main">
        <div class="pos-card">
            <div class="pos-card-header"><h3>Find a product</h3></div>
            <div class="pos-search-body">
                <input
                    type="text"
                    class="pos-input"
                    placeholder="Search by name, part number, or scan a barcode…"
                    wire:model.live.debounce.300ms="search"
                    autofocus
                >

                @if(count($searchResults))
                <div style="margin-top: 14px;">
                    @foreach($searchResults as $product)
                    <div class="pos-result">
                        <div>
                            <div class="pos-result-name">{{ $product['name'] }}</div>
                            <div class="pos-result-meta">
                                {{ $product['part_number'] ?? '—' }} &bull; ${{ number_format((float) $product['price'], 2) }}
                                @if(!$product['is_clothing']) &bull; {{ $product['stock'] }} in stock @endif
                            </div>

                            @if($product['is_clothing'])
                            <div class="pos-variant-row">
                                @forelse($product['variants'] as $variant)
                                <button
                                    type="button"
                                    class="pos-btn pos-btn-sm pos-btn-ghost"
                                    wire:click="addItem({{ $product['id'] }}, {{ $variant['id'] }})"
                                    @if($variant['quantity'] < 1) disabled @endif
                                >{{ $variant['label'] ?: 'Default' }} ({{ $variant['quantity'] }})</button>
                                @empty
                                <span class="pos-result-meta">No variants in stock</span>
                                @endforelse
                            </div>
                            @endif
                        </div>

                        @if(!$product['is_clothing'])
                        <button
                            type="button"
                            class="pos-btn pos-btn-sm"
                            wire:click="addItem({{ $product['id'] }})"
                            @if($product['stock'] < 1) disabled @endif
                        >Add</button>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif(trim($search) !== '')
                <p class="pos-result-meta" style="margin-top:14px;">No matching products.</p>
                @endif
            </div>
        </div>

        <div class="pos-card">
            <div class="pos-card-header"><h3>Cart</h3></div>

            @forelse($cart as $key => $item)
            <div class="pos-cart-row" wire:key="cart-{{ $key }}">
                <div class="pos-cart-name">
                    <strong>{{ $item['product_name'] }}{{ $item['variant_label'] ? " ({$item['variant_label']})" : '' }}</strong>
                    <span>${{ number_format($item['unit_price'], 2) }} each</span>
                </div>
                <input
                    type="number"
                    min="1"
                    max="{{ $item['available_stock'] }}"
                    class="pos-qty-input"
                    value="{{ $item['quantity'] }}"
                    wire:change="updateQuantity('{{ $key }}', $event.target.value)"
                >
                <div class="pos-line-total">${{ number_format($item['line_total'], 2) }}</div>
                <button type="button" class="pos-btn pos-btn-sm pos-btn-danger" wire:click="removeItem('{{ $key }}')">&times;</button>
            </div>
            @empty
            <div class="pos-empty">Search above to add items to this sale.</div>
            @endforelse
        </div>
    </div>

    <div class="pos-col-side">
        <div class="pos-card">
            <div class="pos-card-header"><h3>Totals</h3></div>
            <div class="pos-totals-row"><span>Subtotal</span><span>${{ number_format($this->subtotal, 2) }}</span></div>
            <div class="pos-totals-row grand"><span>Total (inc. GST)</span><span>${{ number_format($this->total, 2) }}</span></div>
        </div>

        <div class="pos-card">
            <div class="pos-card-header">
                <h3>Payment</h3>
                <div class="pos-variant-row" style="margin-top:10px;">
                    <button type="button" class="pos-btn pos-btn-sm {{ $paymentMethod === 'cash' ? '' : 'pos-btn-ghost' }}" wire:click="$set('paymentMethod', 'cash')">Cash</button>
                    <button type="button" class="pos-btn pos-btn-sm {{ $paymentMethod === 'card_present' ? '' : 'pos-btn-ghost' }}" wire:click="$set('paymentMethod', 'card_present')">Card</button>
                </div>
            </div>

            @if($paymentMethod === 'cash')
            <div class="pos-search-body">
                <label style="display:block; font-size:12px; color:#9ca3af; margin-bottom:6px;">Cash tendered</label>
                <input type="number" step="0.05" min="0" class="pos-input" wire:model.live="cashTendered" placeholder="0.00">

                @if($this->changeDue !== null)
                <div class="pos-totals-row" style="padding-left:0; padding-right:0;">
                    <span>Change due</span>
                    <span style="font-weight:700; color: {{ $this->changeDue < 0 ? '#f87171' : '#34d399' }};">
                        ${{ number_format($this->changeDue, 2) }}
                    </span>
                </div>
                @endif

                <button
                    type="button"
                    class="pos-btn pos-btn-block"
                    style="margin-top:12px;"
                    wire:click="completeCashSale"
                    wire:loading.attr="disabled"
                    @if(empty($cart)) disabled @endif
                >
                    <span wire:loading.remove wire:target="completeCashSale">Complete Cash Sale</span>
                    <span wire:loading wire:target="completeCashSale">Processing…</span>
                </button>
            </div>
            @else
            <div class="pos-search-body" @if($pendingCardOrderId) wire:poll.2s="pollCardPayment" @endif>
                @if(!$this->isCardPaymentConfigured())
                <p class="pos-result-meta">No card reader is set up yet. Add a Stripe Terminal reader ID in the server config, or take cash instead.</p>
                @elseif($pendingCardOrderId)
                <p style="color:#f3f4f6; font-size:13px; margin:0 0 12px;">Waiting for card on the reader…</p>
                <button type="button" class="pos-btn pos-btn-sm pos-btn-ghost" wire:click="cancelCardPayment">Cancel</button>
                @else
                @if($cardError)
                <p style="color:#f87171; font-size:13px; margin:0 0 12px;">{{ $cardError }}</p>
                @endif
                <button
                    type="button"
                    class="pos-btn pos-btn-block"
                    wire:click="startCardPayment"
                    wire:loading.attr="disabled"
                    @if(empty($cart)) disabled @endif
                >
                    <span wire:loading.remove wire:target="startCardPayment">Charge ${{ number_format($this->total, 2) }} on Card</span>
                    <span wire:loading wire:target="startCardPayment">Sending to reader…</span>
                </button>
                @endif
            </div>
            @endif

            @if($lastOrderNumber)
            <div class="pos-success">
                <p>Sale complete — {{ $lastOrderNumber }}</p>
                <a href="{{ route('yamaha.pos.receipt', ['order' => $lastOrderId]) }}" target="_blank" class="pos-btn pos-btn-sm pos-btn-ghost">Print Receipt</a>
            </div>
            @endif
        </div>
    </div>
</div>
</x-filament-panels::page>
