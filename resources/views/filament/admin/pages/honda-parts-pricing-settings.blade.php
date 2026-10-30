<x-filament-panels::page>
<style>
.hpp-wrap { max-width: 640px; }

.hpp-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.hpp-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.hpp-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.hpp-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.hpp-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 20px 24px;
    border-bottom: none;
}

.hpp-row-label { flex: 1; min-width: 0; }

.hpp-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
}

.hpp-row-label span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.45;
}

.hpp-input-wrap {
    position: relative;
    flex-shrink: 0;
}

.hpp-input-suffix {
    position: absolute;
    top: 50%;
    right: 11px;
    transform: translateY(-50%);
    font-size: 13px;
    color: #6b7280;
    pointer-events: none;
    line-height: 1;
}

.hpp-input {
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

.hpp-input::-webkit-inner-spin-button,
.hpp-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

.hpp-input:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,.15);
}

.hpp-formula {
    padding: 16px 24px;
    background: rgba(220,38,38,.08);
    border-top: 1px solid rgba(220,38,38,.15);
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.hpp-formula-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
    color: #dc2626;
}

.hpp-formula-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #ef4444;
    margin-bottom: 4px;
}

.hpp-formula-code {
    font-family: 'SFMono-Regular', ui-monospace, monospace;
    font-size: 13px;
    color: #fca5a5;
    line-height: 1.5;
}

.hpp-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.hpp-save-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 8px;
    background: #dc2626;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: background .15s;
    box-shadow: 0 1px 3px rgba(0,0,0,.3);
}

.hpp-save-btn:hover { background: #b91c1c; }
.hpp-save-btn:active { background: #991b1b; }
.hpp-save-btn:disabled { opacity: .5; cursor: not-allowed; }

.hpp-save-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>

<div class="hpp-wrap">

    <div class="hpp-card">

        <div class="hpp-card-header">
            <h3>Genuine Parts Markup</h3>
            <p>Applied to the RRP imported from Honda before it is shown on the parts diagrams and search results.</p>
        </div>

        {{-- Markup row --}}
        <div class="hpp-row">
            <div class="hpp-row-label">
                <strong>Markup Percentage</strong>
                <span>Percentage added on top of the Honda-supplied RRP for every part.</span>
            </div>
            <div class="hpp-input-wrap">
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    max="500"
                    wire:model.live="honda_parts_markup_percent"
                    class="hpp-input"
                />
                <span class="hpp-input-suffix">%</span>
            </div>
        </div>

        {{-- Formula --}}
        <div class="hpp-formula">
            <svg class="hpp-formula-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.611 4.5 4.668v10.664c0 1.057.807 1.969 1.907 2.096l.004-.001a48.177 48.177 0 0 0 5.589.32 48.177 48.177 0 0 0 5.589-.32l.004.001c1.1-.127 1.907-1.039 1.907-2.096V4.668c0-1.057-.807-1.968-1.907-2.096A48.232 48.232 0 0 0 12 2.25Z" />
            </svg>
            <div class="hpp-formula-body">
                <div class="hpp-formula-label">Calculation Formula</div>
                <div class="hpp-formula-code">displayed_price = honda_rrp &times; (1 + {{ $honda_parts_markup_percent }}%)</div>
            </div>
        </div>

    </div>

    {{-- Save --}}
    <div class="hpp-footer">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            type="button"
            class="hpp-save-btn"
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
