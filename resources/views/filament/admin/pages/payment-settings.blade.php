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

.ps-secret-row { display: flex; align-items: center; gap: 10px; }
.ps-secret-row .ps-input { flex: 1; }

.ps-clear-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #9ca3af;
    white-space: nowrap;
    cursor: pointer;
}

.ps-clear-label input { accent-color: #ef4444; }

.ps-hint { font-size: 12px; color: #6b7280; margin-top: 6px; }

.ps-switch-row { display: flex; align-items: center; gap: 10px; }
.ps-switch-row input[type="checkbox"] { accent-color: #10b981; width: 16px; height: 16px; }
.ps-switch-label { font-size: 12px; color: #9ca3af; white-space: nowrap; }

.ps-url-box {
    display: flex; align-items: center; gap: 8px;
    font-family: ui-monospace, monospace;
    font-size: 12px; color: #d1d5db;
    background: #111827; border: 1px solid #374151; border-radius: 6px;
    padding: 8px 10px; margin-top: 6px; overflow-x: auto; white-space: nowrap;
}
</style>

<div class="ps-wrap">

    <div class="ps-card">
        <div class="ps-card-header">
            <h3>Gateway Status</h3>
            <p>Checkout offers a gateway only when it's both configured below AND switched on here. Direct Deposit needs no credentials, so its switch is the only thing that matters. If nothing is on, Direct Deposit is used as the fallback.</p>
        </div>
        <div class="ps-status-row">
            <span>Stripe (card payments)</span>
            <div class="ps-switch-row">
                @if($this->isStripeConfigured())
                    @if($stripe_enabled)
                    <span class="ps-badge ps-badge-on">Active at Checkout</span>
                    @else
                    <span class="ps-badge ps-badge-off">Configured, Switched Off</span>
                    @endif
                @else
                <span class="ps-badge ps-badge-off">Not Configured</span>
                @endif
                <label class="ps-clear-label"><input type="checkbox" wire:model.live="stripe_enabled"> On</label>
            </div>
        </div>
        <div class="ps-status-row">
            <span>PayPal</span>
            <div class="ps-switch-row">
                @if($this->isPaypalConfigured())
                    @if($paypal_enabled)
                    <span class="ps-badge ps-badge-on">Active at Checkout</span>
                    @else
                    <span class="ps-badge ps-badge-off">Configured, Switched Off</span>
                    @endif
                @else
                <span class="ps-badge ps-badge-off">Not Configured</span>
                @endif
                <label class="ps-clear-label"><input type="checkbox" wire:model.live="paypal_enabled"> On</label>
            </div>
        </div>
        <div class="ps-status-row">
            <span>Direct Deposit</span>
            <div class="ps-switch-row">
                @if($bank_transfer_enabled)
                <span class="ps-badge ps-badge-on">Active at Checkout</span>
                @else
                <span class="ps-badge ps-badge-off">Switched Off</span>
                @endif
                <label class="ps-clear-label"><input type="checkbox" wire:model.live="bank_transfer_enabled"> On</label>
            </div>
        </div>
    </div>

    @if(!$this->isEnvWritable())
    <div class="ps-card" style="border-color:#dc2626;">
        <div class="ps-status-row" style="color:#fca5a5;">
            The server's .env file isn't writable by the web process, so Stripe/PayPal keys can't be saved from here right now — bank details will still save. Ask your host to fix file permissions.
        </div>
    </div>
    @endif

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

        <div class="ps-card">
            <div class="ps-card-header">
                <h3>Stripe Keys</h3>
                <p>From the Stripe Dashboard → Developers → API keys. Adding a secret key here is what turns Stripe on at checkout.</p>
            </div>
            <div class="ps-row">
                <label for="stripe_key">Publishable Key</label>
                <input id="stripe_key" type="text" wire:model="stripe_key" class="ps-input" placeholder="pk_live_...">
            </div>
            <div class="ps-row">
                <label for="stripe_secret">Secret Key</label>
                <div class="ps-secret-row">
                    <input id="stripe_secret" type="password" wire:model="stripe_secret" class="ps-input" placeholder="{{ $this->stripe_secret_saved ? '•••••••••••••• (saved — leave blank to keep)' : 'sk_live_...' }}">
                    @if($this->stripe_secret_saved)
                    <label class="ps-clear-label">
                        <input type="checkbox" wire:model="clear_stripe_secret"> Remove
                    </label>
                    @endif
                </div>
            </div>
            <div class="ps-row">
                <label for="stripe_webhook_secret">Webhook Signing Secret</label>
                <div class="ps-secret-row">
                    <input id="stripe_webhook_secret" type="password" wire:model="stripe_webhook_secret" class="ps-input" placeholder="{{ $this->stripe_webhook_secret_saved ? '•••••••••••••• (saved — leave blank to keep)' : 'whsec_...' }}">
                    @if($this->stripe_webhook_secret_saved)
                    <label class="ps-clear-label">
                        <input type="checkbox" wire:model="clear_stripe_webhook_secret"> Remove
                    </label>
                    @endif
                </div>
                <p class="ps-hint">From your webhook endpoint's settings in the Stripe Dashboard, or `stripe listen` for local testing. Endpoint URL:</p>
                <div class="ps-url-box">{{ $this->stripeWebhookUrl() }}</div>
            </div>
        </div>

        <div class="ps-card">
            <div class="ps-card-header">
                <h3>PayPal Keys</h3>
                <p>From your PayPal Developer Dashboard → Apps & Credentials. Adding a Client Secret here is what turns PayPal on at checkout (once the switch above is also on).</p>
            </div>
            <div class="ps-row">
                <label for="paypal_client_id">Client ID</label>
                <input id="paypal_client_id" type="text" wire:model="paypal_client_id" class="ps-input" placeholder="AeA1...">
            </div>
            <div class="ps-row">
                <label for="paypal_client_secret">Client Secret</label>
                <div class="ps-secret-row">
                    <input id="paypal_client_secret" type="password" wire:model="paypal_client_secret" class="ps-input" placeholder="{{ $this->paypal_client_secret_saved ? '•••••••••••••• (saved — leave blank to keep)' : 'EOx1...' }}">
                    @if($this->paypal_client_secret_saved)
                    <label class="ps-clear-label">
                        <input type="checkbox" wire:model="clear_paypal_client_secret"> Remove
                    </label>
                    @endif
                </div>
            </div>
            <div class="ps-row">
                <label for="paypal_mode">Mode</label>
                <select id="paypal_mode" wire:model="paypal_mode" class="ps-input">
                    <option value="sandbox">Sandbox (testing)</option>
                    <option value="live">Live</option>
                </select>
            </div>
            <div class="ps-row">
                <label for="paypal_webhook_id">Webhook ID</label>
                <input id="paypal_webhook_id" type="text" wire:model="paypal_webhook_id" class="ps-input" placeholder="1AB2345CD6789...">
                <p class="ps-hint">Register a webhook in the PayPal Dashboard pointed at the URL below, subscribed to "Payment capture completed" and "Payment capture denied", then paste its Webhook ID here. This is a backup for confirming payment if a customer closes the tab before returning from PayPal — checkout works without it, but payments won't be confirmed automatically if that happens.</p>
                <div class="ps-url-box">{{ $this->paypalWebhookUrl() }}</div>
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
