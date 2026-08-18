<x-filament-panels::page>
<style>
.hs-wrap { max-width: 760px; }

.hs-card {
    background: #1f2937;
    border: 1px solid #374151;
    border-radius: 12px;
    overflow: hidden;
}

.hs-card-header {
    padding: 20px 24px 18px;
    border-bottom: 1px solid #374151;
    background: #111827;
}

.hs-card-header h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 600;
    color: #f9fafb;
    letter-spacing: -0.01em;
}

.hs-card-header p {
    margin: 0;
    font-size: 13px;
    color: #9ca3af;
    line-height: 1.5;
}

.hs-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 20px 24px;
    border-bottom: 1px solid #374151;
}

.hs-row:last-child { border-bottom: none; }

.hs-row-label { flex: 1; min-width: 0; }

.hs-row-label strong {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
}

.hs-row-label span {
    font-size: 13px;
    color: #6b7280;
    line-height: 1.45;
}

/* Toggle switch */
.hs-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
    cursor: pointer;
}

.hs-switch input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    margin: 0;
    cursor: pointer;
}

.hs-switch-track {
    position: absolute;
    inset: 0;
    background: #374151;
    border-radius: 999px;
    transition: background .15s;
}

.hs-switch input:checked ~ .hs-switch-track {
    background: #f59e0b;
}

.hs-switch-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #f9fafb;
    border-radius: 50%;
    transition: transform .15s;
}

.hs-switch input:checked ~ .hs-switch-track .hs-switch-thumb { transform: translateX(20px); }

/* Instant per-row toggle (server state, not a bound checkbox) */
.hs-switch-btn {
    position: relative;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
    border: none;
    border-radius: 999px;
    background: #374151;
    padding: 0;
    cursor: pointer;
    transition: background .15s;
}

.hs-switch-btn.is-on { background: #f59e0b; }
.hs-switch-btn:disabled { opacity: .35; cursor: not-allowed; }

.hs-switch-btn .hs-switch-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    background: #f9fafb;
    border-radius: 50%;
    transition: transform .15s;
}

.hs-switch-btn.is-on .hs-switch-thumb { transform: translateX(20px); }

.hs-margin-top { margin-top: 20px; }

.hs-offer-row {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 24px;
    border-bottom: 1px solid #374151;
}

.hs-offer-row:last-child { border-bottom: none; }

.hs-offer-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
    background: #111827;
    border: 1px solid #374151;
    flex-shrink: 0;
}

.hs-offer-thumb-empty {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: #111827;
    border: 1px dashed #4b5563;
    flex-shrink: 0;
}

.hs-offer-info { flex: 1; min-width: 0; }

.hs-offer-title {
    font-size: 14px;
    font-weight: 500;
    color: #f3f4f6;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hs-offer-subtitle {
    font-size: 12px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.hs-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    padding: 2px 7px;
    border-radius: 4px;
    margin-left: 6px;
    vertical-align: middle;
}

.hs-badge-inactive { background: rgba(239,68,68,.15); color: #f87171; }
.hs-badge-noimage  { background: rgba(107,114,128,.2); color: #9ca3af; }

.hs-empty {
    padding: 32px 24px;
    text-align: center;
    font-size: 13px;
    color: #6b7280;
}

.hs-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 20px;
}

.hs-save-btn {
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

.hs-save-btn:hover { background: #d97706; }
.hs-save-btn:active { background: #b45309; }
.hs-save-btn:disabled { opacity: .5; cursor: not-allowed; }

.hs-save-btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}
</style>

<div class="hs-wrap">

    <div class="hs-card">

        <div class="hs-card-header">
            <h3>Homepage Hero Slider</h3>
            <p>Turn promotional sources on or off in the slider at the top of the homepage. Individual slides are still managed where they come from — Yamaha promotions sync from the Yamaha API, Honda offers are managed under "Honda Offers".</p>
        </div>

        <div class="hs-row">
            <div class="hs-row-label">
                <strong>Yamaha Promotions</strong>
                <span>Show Yamaha promotional slides synced from the Yamaha Motor API.</span>
            </div>
            <label class="hs-switch">
                <input type="checkbox" wire:model.live="show_yamaha_promotions">
                <span class="hs-switch-track"><span class="hs-switch-thumb"></span></span>
            </label>
        </div>

        <div class="hs-row">
            <div class="hs-row-label">
                <strong>Honda Offers</strong>
                <span>Show Honda offers as slides. Pick exactly which ones below.</span>
            </div>
            <label class="hs-switch">
                <input type="checkbox" wire:model.live="show_honda_offers">
                <span class="hs-switch-track"><span class="hs-switch-thumb"></span></span>
            </label>
        </div>

    </div>

    {{-- Individual Honda offers --}}
    <div class="hs-card hs-margin-top">

        <div class="hs-card-header">
            <h3>Honda Offers in the Slider</h3>
            <p>Choose exactly which Honda offers appear in the homepage slider. Turning one off here only hides it from the slider — the offer stays active everywhere else on the site (offers page, product pages, etc).</p>
        </div>

        @php $hondaOffers = $this->hondaOffers(); @endphp

        @forelse($hondaOffers as $offer)
        <div class="hs-offer-row">
            @if($offer->image)
                <img src="{{ $offer->image->url() }}" alt="" class="hs-offer-thumb">
            @else
                <div class="hs-offer-thumb-empty"></div>
            @endif

            <div class="hs-offer-info">
                <div class="hs-offer-title">
                    {{ $offer->title }}
                    @unless($offer->is_active)
                        <span class="hs-badge hs-badge-inactive">Inactive</span>
                    @endunless
                    @unless($offer->image_asset_id)
                        <span class="hs-badge hs-badge-noimage">No image</span>
                    @endunless
                </div>
                @if($offer->subtitle)
                <div class="hs-offer-subtitle">{{ $offer->subtitle }}</div>
                @endif
            </div>

            <button
                type="button"
                wire:click="toggleHondaOfferSlide({{ $offer->id }})"
                wire:loading.attr="disabled"
                wire:target="toggleHondaOfferSlide({{ $offer->id }})"
                @disabled(! $offer->image_asset_id)
                class="hs-switch-btn {{ $offer->show_in_homepage_slider ? 'is-on' : '' }}"
            >
                <span class="hs-switch-thumb"></span>
            </button>
        </div>
        @empty
        <div class="hs-empty">No Honda offers yet.</div>
        @endforelse

    </div>

    {{-- Save --}}
    <div class="hs-footer">
        <button
            wire:click="save"
            wire:loading.attr="disabled"
            type="button"
            class="hs-save-btn"
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
