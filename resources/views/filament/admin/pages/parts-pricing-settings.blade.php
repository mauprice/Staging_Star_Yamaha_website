<x-filament-panels::page>
<style>
.pp-wrap { max-width: 640px; }

.pp-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.pp-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.pp-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.pp-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.pp-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 20px 24px;
    border-bottom: none;
}

.pp-row-label { flex: 1; min-width: 0; }

.pp-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
}

.pp-row-label span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.45;
}

.pp-input-wrap {
    position: relative;
    flex-shrink: 0;
}

.pp-input-suffix {
    position: absolute;
    top: 50%;
    right: 11px;
    transform: translateY(-50%);
    font-size: 13px;
    color: #6b7280;
    pointer-events: none;
    line-height: 1;
}

.pp-input {
    height: 38px;
    width: 96px;
    padding: 0 28px 0 12px;
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

.pp-input::-webkit-inner-spin-button,
.pp-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

.pp-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,.15);
}

.pp-formula {
    padding: 16px 24px;
    background: rgba(245,158,11,.08);
    border-top: 1px solid rgba(245,158,11,.15);
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.pp-formula-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
    color: #f59e0b;
}

.pp-formula-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #d97706;
    margin-bottom: 4px;
}

.pp-formula-code {
    font-family: 'SFMono-Regular', ui-monospace, monospace;
    font-size: 13px;
    color: #fcd34d;
    line-height: 1.5;
}

.pp-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.pp-save-btn {
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

.pp-save-btn:hover { background: #d97706; }
.pp-save-btn:active { background: #b45309; }
.pp-save-btn:disabled { opacity: .5; cursor: not-allowed; }

.pp-save-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>

<div class="pp-wrap">

    <div class="pp-card">

        <div class="pp-card-header">
            <h3>Genuine Parts Markup</h3>
            <p>Applied to the RRP imported from Yamaha before it is shown on the parts diagrams and search results.</p>
        </div>

        {{-- Markup row --}}
        <div class="pp-row">
            <div class="pp-row-label">
                <strong>Markup Percentage</strong>
                <span>Percentage added on top of the Yamaha-supplied RRP for every part.</span>
            </div>
            <div class="pp-input-wrap">
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="500"
                    wire:model.live="parts_markup_percent"
                    class="pp-input"
                />
                <span class="pp-input-suffix">%</span>
            </div>
        </div>

        {{-- Formula --}}
        <div class="pp-formula">
            <svg class="pp-formula-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.611 4.5 4.668v10.664c0 1.057.807 1.969 1.907 2.096l.004-.001a48.177 48.177 0 0 0 5.589.32 48.177 48.177 0 0 0 5.589-.32l.004.001c1.1-.127 1.907-1.039 1.907-2.096V4.668c0-1.057-.807-1.968-1.907-2.096A48.232 48.232 0 0 0 12 2.25Z" />
            </svg>
            <div class="pp-formula-body">
                <div class="pp-formula-label">Calculation Formula</div>
                <div class="pp-formula-code">displayed_price = yamaha_rrp &times; (1 + {{ $parts_markup_percent }}%)</div>
            </div>
        </div>

    </div>

    {{-- Save --}}
    <div class="pp-footer">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            type="button"
            class="pp-save-btn"
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
