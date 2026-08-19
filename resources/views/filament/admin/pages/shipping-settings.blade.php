<x-filament-panels::page>
<style>
.ss-wrap { max-width: 640px; }

.ss-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.ss-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.ss-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.ss-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.ss-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 20px 24px;
    border-bottom: 1px solid #374151;
}

.ss-row:last-child { border-bottom: none; }

.ss-row-label { flex: 1; min-width: 0; }

.ss-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
}

.ss-row-label span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.45;
}

.ss-input-wrap { position: relative; flex-shrink: 0; }

.ss-input-prefix {
    position: absolute;
    top: 50%;
    left: 11px;
    transform: translateY(-50%);
    font-size: 13px;
    color: #6b7280;
    pointer-events: none;
}

.ss-input {
    height: 38px;
    width: 110px;
    border-radius: 8px;
    border: 1px solid #374151;
    background: #111827;
    color: #f9fafb;
    font-size: 14px;
    text-align: right;
    padding: 0 12px 0 24px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    -moz-appearance: textfield;
}

.ss-input::-webkit-inner-spin-button,
.ss-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

.ss-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,.15);
}

.ss-note {
    padding: 16px 24px;
    background: rgba(245,158,11,.08);
    border-top: 1px solid rgba(245,158,11,.15);
    font-size: 13px;
    color: #d97706;
    line-height: 1.5;
}

.ss-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.ss-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 8px;
    background: #f59e0b;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: background .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.3);
}

.ss-save-btn:hover { background: #d97706; }
.ss-save-btn:disabled { opacity: .5; cursor: not-allowed; }
</style>

<div class="ss-wrap">

    <form wire:submit="save">
        <div class="ss-card">
            <div class="ss-card-header">
                <h3>Checkout Shipping Charge</h3>
                <p>Applied to every order at checkout. There's no rate-by-location or rate-by-weight calculation yet — this is a single flat rate with a free-shipping cutoff.</p>
            </div>

            <div class="ss-row">
                <div class="ss-row-label">
                    <strong>Flat Shipping Rate</strong>
                    <span>Charged on orders below the free-shipping threshold.</span>
                </div>
                <div class="ss-input-wrap">
                    <span class="ss-input-prefix">$</span>
                    <input type="number" step="0.01" min="0" wire:model="shipping_flat_rate" class="ss-input">
                </div>
            </div>

            <div class="ss-row">
                <div class="ss-row-label">
                    <strong>Free Shipping Threshold</strong>
                    <span>Orders with a subtotal at or above this amount ship free.</span>
                </div>
                <div class="ss-input-wrap">
                    <span class="ss-input-prefix">$</span>
                    <input type="number" step="0.01" min="0" wire:model="shipping_free_threshold" class="ss-input">
                </div>
            </div>

            <div class="ss-note">
                Products already have weight and dimensions recorded, and shipping addresses record state/postcode/country — a real per-carrier, per-location rate table can be built on top of this later without changing checkout.
            </div>
        </div>

        <div class="ss-footer">
            <button type="submit" wire:loading.attr="disabled" class="ss-save-btn">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>

</div>
</x-filament-panels::page>
