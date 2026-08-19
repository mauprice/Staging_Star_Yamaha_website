<x-filament-panels::page>
<style>
.pi-wrap { max-width: 640px; }

.pi-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.pi-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.pi-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.pi-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.pi-row {
    padding: 20px 24px;
}

.pi-row-label { margin-bottom: 10px; }

.pi-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
}

.pi-row-label span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.45;
}

.pi-input {
    height: 38px;
    width: 100%;
    padding: 0 12px;
    border-radius: 8px;
    border: 1px solid #374151;
    background: #111827;
    color: #f9fafb;
    font-size: 14px;
    font-family: 'SFMono-Regular', ui-monospace, monospace;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.pi-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245,158,11,.15);
}

.pi-preview {
    padding: 16px 24px;
    background: rgba(245,158,11,.08);
    border-top: 1px solid rgba(245,158,11,.15);
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.pi-preview-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
    margin-top: 1px;
    color: #f59e0b;
}

.pi-preview-label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #d97706;
    margin-bottom: 4px;
}

.pi-preview-code {
    font-family: 'SFMono-Regular', ui-monospace, monospace;
    font-size: 13px;
    color: #fcd34d;
    line-height: 1.5;
    word-break: break-all;
}

.pi-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.pi-save-btn {
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

.pi-save-btn:hover { background: #d97706; }
.pi-save-btn:active { background: #b45309; }
.pi-save-btn:disabled { opacity: .5; cursor: not-allowed; }

.pi-save-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>

<div class="pi-wrap" wire:poll.10000ms>

    <div class="pi-card">

        <div class="pi-card-header">
            <h3>Parts Diagram Image CDN</h3>
            <p>Base URL that diagram and thumbnail images are loaded from. Images are not stored on this site — only the image ID and format are, and this URL is combined with them at request time.</p>
        </div>

        {{-- URL row --}}
        <div class="pi-row">
            <div class="pi-row-label">
                <strong>Base URL</strong>
                <span>Must start with https:// and include the trailing slash before the image filename.</span>
            </div>
            <input
                type="text"
                wire:model.live="image_base_url"
                placeholder="https://yamahaparts.b-cdn.net/storage/images/"
                class="pi-input"
            />
        </div>

        {{-- Preview --}}
        <div class="pi-preview">
            <svg class="pi-preview-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 3h18M3 3v18M3 3l18 18" />
            </svg>
            <div class="pi-preview-body">
                <div class="pi-preview-label">Resulting Image URL</div>
                <div class="pi-preview-code">{{ rtrim($image_base_url, '/') }}/75687.webp</div>
            </div>
        </div>

    </div>

    {{-- Save --}}
    <div class="pi-footer">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            type="button"
            class="pi-save-btn"
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

    {{-- CDN Cache Warm-Up --}}
    @php
        $warmProgress = $this->getWarmProgress();
        $lastWarmed = $this->getLastWarmed();
    @endphp

    <div class="pi-card" style="margin-top:24px;">

        <div class="pi-card-header">
            <h3>CDN Image Cache Warm-Up</h3>
            <p>Requests every diagram image through the CDN ({{ rtrim($image_base_url, '/') }}) in filename sequence so it gets pulled into the edge cache ahead of real traffic.</p>
        </div>

        <div class="pi-row">
            <div class="pi-row-label">
                <strong>Concurrent Requests</strong>
                <span>How many images to request at once. Higher is faster but puts more load on the CDN origin.</span>
            </div>
            <input
                type="number"
                min="1"
                max="32"
                wire:model.live="warm_concurrency"
                class="pi-input"
                style="width:120px;"
            />
        </div>

        @if($warmProgress['running'])
        <div class="pi-row" style="padding-top:0;">
            <div style="background:#111827; border:1px solid #374151; border-radius:8px; padding:14px 16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:13px; font-weight:600; color:#f3f4f6;">
                        Warming… {{ number_format($warmProgress['current']) }} / {{ number_format($warmProgress['total']) }}
                        ({{ number_format($warmProgress['ok']) }} ok{{ $warmProgress['failedCount'] ? ', '.number_format($warmProgress['failedCount']).' failed' : '' }})
                    </span>
                    <span style="font-size:13px; font-weight:700; color:#f59e0b;">{{ $warmProgress['pct'] }}%</span>
                </div>
                <div style="background:#1e293b; border-radius:9999px; height:8px; overflow:hidden;">
                    <div style="height:100%; border-radius:9999px; background:linear-gradient(90deg,#f59e0b,#fbbf24); width:{{ $warmProgress['pct'] }}%; transition:width .4s ease;"></div>
                </div>
            </div>
        </div>
        @elseif($warmProgress['failed'] ?? false)
        <div class="pi-row" style="padding-top:0;">
            <div style="border-radius:8px; border:1px solid #7f1d1d; background:rgba(220,38,38,.08); padding:12px 16px;">
                <p style="font-size:13px; color:#fca5a5; margin:0;"><strong>Warm-up failed:</strong> {{ $warmProgress['error'] }}</p>
            </div>
        </div>
        @elseif($lastWarmed)
        <div class="pi-row" style="padding-top:0;">
            <p style="font-size:13px; color:#6b7280; margin:0;">
                Last run: <strong style="color:#d1d5db;">{{ $lastWarmed }}</strong>
                @if(($warmProgress['ok'] ?? null) !== null)
                    — {{ number_format($warmProgress['ok']) }} ok{{ ($warmProgress['failedCount'] ?? 0) ? ', '.number_format($warmProgress['failedCount']).' failed' : '' }}
                @endif
            </p>
        </div>
        @endif

    </div>

    <div class="pi-footer">
        <button
            wire:click="warmCache"
            wire:loading.attr="disabled"
            type="button"
            class="pi-save-btn"
            @if($warmProgress['running']) disabled @endif
        >
            <svg wire:loading.remove wire:target="warmCache" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <svg wire:loading wire:target="warmCache" fill="none" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <span wire:loading.remove wire:target="warmCache">{{ $warmProgress['running'] ? 'Warming…' : 'Warm Cache Now' }}</span>
            <span wire:loading wire:target="warmCache">Starting…</span>
        </button>
    </div>

</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</x-filament-panels::page>
