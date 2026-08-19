<x-filament-panels::page>
<style>
.ps-wrap { max-width: 640px; }

.ps-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 20px;
}

.ps-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.ps-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.ps-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.ps-status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 24px;
    border-bottom: 1px solid #374151;
    font-size: 14px;
    color: #f3f4f6;
}

.ps-status-row:last-child { border-bottom: none; }

.ps-badge {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 3px 10px;
    border-radius: 999px;
}

.ps-badge-on { background: rgba(16,185,129,.15); color: #34d399; }
.ps-badge-off { background: rgba(107,114,128,.2); color: #9ca3af; }

.ps-row {
    padding: 16px 24px;
    border-bottom: 1px solid #374151;
}

.ps-row:last-child { border-bottom: none; }

.ps-row label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 6px;
}

.ps-input {
    width: 100%;
    height: 38px;
    border-radius: 8px;
    border: 1px solid #374151;
    background: #111827;
    color: #f9fafb;
    font-size: 14px;
    padding: 0 12px;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.ps-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,.15);
}

.ps-footer {
    display: flex;
    justify-content: flex-end;
}

.ps-save-btn {
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

.ps-save-btn:hover { background: #d97706; }
.ps-save-btn:disabled { opacity: .5; cursor: not-allowed; }
</style>

<div class="ps-wrap">

    <div class="ps-card">
        <div class="ps-card-header">
            <h3>Gateway Status</h3>
            <p>Checkout automatically offers whichever gateways are configured, and defaults to Direct Deposit when neither online gateway is set up.</p>
        </div>
        <div class="ps-status-row">
            <span>Stripe (card payments)</span>
            @if($this->getGatewayStatus()['stripe'])
            <span class="ps-badge ps-badge-on">Configured</span>
            @else
            <span class="ps-badge ps-badge-off">Not Configured</span>
            @endif
        </div>
        <div class="ps-status-row">
            <span>PayPal</span>
            <span class="ps-badge ps-badge-off">Not Yet Available</span>
        </div>
        <div class="ps-status-row">
            <span>Direct Deposit</span>
            <span class="ps-badge ps-badge-on">Always Available</span>
        </div>
    </div>

    <form wire:submit="save">
        <div class="ps-card">
            <div class="ps-card-header">
                <h3>Direct Deposit Bank Details</h3>
                <p>Shown to customers who choose Direct Deposit, and included in the order notification email. Orders stay "Awaiting Bank Deposit" until a staff member manually verifies the payment.</p>
            </div>
            <div class="ps-row">
                <label for="bank_deposit_bank_name">Bank Name</label>
                <input id="bank_deposit_bank_name" type="text" wire:model="bank_deposit_bank_name" class="ps-input" placeholder="Commonwealth Bank">
            </div>
            <div class="ps-row">
                <label for="bank_deposit_account_name">Account Name</label>
                <input id="bank_deposit_account_name" type="text" wire:model="bank_deposit_account_name" class="ps-input" placeholder="Star Yamaha Pty Ltd">
            </div>
            <div class="ps-row">
                <label for="bank_deposit_bsb">BSB</label>
                <input id="bank_deposit_bsb" type="text" wire:model="bank_deposit_bsb" class="ps-input" placeholder="062-000">
            </div>
            <div class="ps-row">
                <label for="bank_deposit_account_number">Account Number</label>
                <input id="bank_deposit_account_number" type="text" wire:model="bank_deposit_account_number" class="ps-input" placeholder="12345678">
            </div>
        </div>

        <div class="ps-footer">
            <button type="submit" wire:loading.attr="disabled" class="ps-save-btn">
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>

</div>
</x-filament-panels::page>
