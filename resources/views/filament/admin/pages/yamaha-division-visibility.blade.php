<x-filament-panels::page>
<style>
.dv-wrap { max-width: 640px; }

.dv-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.dv-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.dv-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.dv-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.dv-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 18px 24px;
    border-bottom: 1px solid #374151;
}

.dv-row:last-child { border-bottom: none; }

.dv-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
}

/* Toggle switch */
.dv-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
    cursor: pointer;
}

.dv-switch input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    cursor: pointer;
}

.dv-switch-track {
    position: absolute;
    inset: 0;
    background: #374151;
    border-radius: 999px;
    transition: background .15s;
}

.dv-switch input:checked ~ .dv-switch-track {
    background: #f59e0b;
}

.dv-switch-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #f9fafb;
    border-radius: 50%;
    transition: transform .15s;
}

.dv-switch input:checked ~ .dv-switch-track .dv-switch-thumb { transform: translateX(20px); }

.dv-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.dv-save-btn {
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

.dv-save-btn:hover { background: #d97706; }
.dv-save-btn:active { background: #b45309; }
.dv-save-btn:disabled { opacity: .5; cursor: not-allowed; }

.dv-save-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>

<div class="dv-wrap">

    <div class="dv-card">

        <div class="dv-card-header">
            <h3>Yamaha Product Categories</h3>
            <p>Turn Yamaha product divisions on or off across the site. Hiding a division removes it from the nav menu, the homepage "Shop by Category" tiles, and the sitemap — the division's pages still work if someone has a direct link, they just won't be promoted anywhere.</p>
        </div>

        @foreach($this->divisions() as $division)
        <div class="dv-row">
            <div class="dv-row-label">
                <strong>{{ $division['label'] }}</strong>
            </div>
            <label class="dv-switch">
                <input type="checkbox" wire:model.live="{{ $division['property'] }}">
                <span class="dv-switch-track"><span class="dv-switch-thumb"></span></span>
            </label>
        </div>
        @endforeach

    </div>

    {{-- Save --}}
    <div class="dv-footer">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            type="button"
            class="dv-save-btn"
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
