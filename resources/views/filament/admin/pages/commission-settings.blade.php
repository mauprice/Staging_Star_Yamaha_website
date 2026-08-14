<x-filament-panels::page>
<style>
.cs-wrap { max-width: 640px; }

.cs-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.cs-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.cs-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.cs-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.cs-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 20px 24px;
    border-bottom: 1px solid #374151;
}

.cs-row-label { flex: 1; min-width: 0; }

.cs-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
}

.cs-row-label span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.45;
}

.cs-input-wrap {
    position: relative;
    flex-shrink: 0;
}

.cs-input-prefix,
.cs-input-suffix {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    color: #6b7280;
    pointer-events: none;
    line-height: 1;
}

.cs-input-prefix { left: 11px; }
.cs-input-suffix { right: 11px; }

.cs-input {
    height: 38px;
    border-radius: 8px;
    border: 1px solid #374151;
    background: #111827;
    color: #f9fafb;
    font-size: 14px;
    text-align: right;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    -moz-appearance: textfield;
}

.cs-input::-webkit-inner-spin-button,
.cs-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

.cs-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,.15);
}

.cs-input-rate  { width: 80px; padding: 0 28px 0 12px; }
.cs-input-min   { width: 96px; padding: 0 12px 0 24px; }

.cs-formula {
    padding: 16px 24px;
    background: rgba(245,158,11,.08);
    border-top: 1px solid rgba(245,158,11,.15);
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.cs-formula-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
    color: #f59e0b;
}

.cs-formula-body {}

.cs-formula-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #d97706;
    margin-bottom: 4px;
}

.cs-formula-code {
    font-family: 'SFMono-Regular', ui-monospace, monospace;
    font-size: 13px;
    color: #fcd34d;
    line-height: 1.5;
}

.cs-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.cs-save-btn {
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

.cs-save-btn:hover { background: #d97706; }
.cs-save-btn:active { background: #b45309; }
.cs-save-btn:disabled { opacity: .5; cursor: not-allowed; }

.cs-save-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>

<div class="cs-wrap">

    <div class="cs-card">

        <div class="cs-card-header">
            <h3>Default Commission Rules</h3>
            <p>Applied automatically when no manual commission is entered on a stock entry.</p>
        </div>

        {{-- Rate row --}}
        <div class="cs-row">
            <div class="cs-row-label">
                <strong>Commission Rate</strong>
                <span>Percentage of gross profit paid to the salesperson.</span>
            </div>
            <div class="cs-input-wrap">
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                    wire:model.live="commission_rate"
                    class="cs-input cs-input-rate"
                />
                <span class="cs-input-suffix">%</span>
            </div>
        </div>

        {{-- Minimum row --}}
        <div class="cs-row" style="border-bottom:none;">
            <div class="cs-row-label">
                <strong>Minimum Commission</strong>
                <span>Commission will never fall below this amount, regardless of gross profit.</span>
            </div>
            <div class="cs-input-wrap">
                <span class="cs-input-prefix">$</span>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    wire:model.live="commission_minimum"
                    class="cs-input cs-input-min"
                />
            </div>
        </div>

        {{-- Formula --}}
        <div class="cs-formula">
            <svg class="cs-formula-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.611 4.5 4.668v10.664c0 1.057.807 1.969 1.907 2.096l.004-.001a48.177 48.177 0 0 0 5.589.32 48.177 48.177 0 0 0 5.589-.32l.004.001c1.1-.127 1.907-1.039 1.907-2.096V4.668c0-1.057-.807-1.968-1.907-2.096A48.232 48.232 0 0 0 12 2.25Z" />
            </svg>
            <div class="cs-formula-body">
                <div class="cs-formula-label">Calculation Formula</div>
                <div class="cs-formula-code">commission = max(${{ number_format((float) $commission_minimum, 2) }}, gross_profit &times; {{ $commission_rate }}%)</div>
            </div>
        </div>

    </div>

    {{-- Save --}}
    <div class="cs-footer">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            type="button"
            class="cs-save-btn"
        >
            <svg wire:loading.remove wire:target="save" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <svg wire:loading wire:target="save" fill="none" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span wire:loading.remove wire:target="save">Save Settings</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </div>

</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</x-filament-panels::page>
