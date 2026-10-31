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
.hs-badge-muted    { background: rgba(107,114,128,.2); color: #9ca3af; }

.hs-empty {
    padding: 32px 24px;
    text-align: center;
    font-size: 13px;
    color: #6b7280;
}

.hs-order-btns {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex-shrink: 0;
}

.hs-order-btn {
    width: 22px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #374151;
    border-radius: 4px;
    background: #111827;
    color: #9ca3af;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.hs-order-btn:hover:not(:disabled) { background: #374151; color: #f3f4f6; }
.hs-order-btn:disabled { opacity: .3; cursor: not-allowed; }
.hs-order-btn svg { width: 12px; height: 12px; }
</style>

<div class="hs-wrap">

    {{-- Specials --}}
    <div class="hs-card">

        <div class="hs-card-header">
            <h3>Specials</h3>
            <p>Shown on the /specials page and the homepage "Specials &amp; Offers" grid. Full editing is under "Specials" — this is a quick on/off.</p>
        </div>

        @php $specials = $this->specials(); @endphp

        @forelse($specials as $special)
        <div class="hs-offer-row">
            @php $image = $special->featured_image ?? (is_array($special->images) && count($special->images) ? $special->images[0] : null); @endphp
            @if($image)
                <img src="{{ asset('storage/' . $image) }}" alt="" class="hs-offer-thumb">
            @else
                <div class="hs-offer-thumb-empty"></div>
            @endif

            <div class="hs-offer-info">
                <div class="hs-offer-title">
                    {{ $special->title }}
                    @unless($special->is_active)
                        <span class="hs-badge hs-badge-inactive">Inactive</span>
                    @endunless
                </div>
                @if($special->special_price)
                <div class="hs-offer-subtitle">${{ number_format($special->special_price, 0) }}{{ $special->regular_price ? ' (was $' . number_format($special->regular_price, 0) . ')' : '' }}</div>
                @endif
            </div>

            <div class="hs-order-btns">
                <button type="button" wire:click="moveSpecialUp({{ $special->id }})" wire:loading.attr="disabled" @disabled($loop->first) class="hs-order-btn" title="Move up">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" wire:click="moveSpecialDown({{ $special->id }})" wire:loading.attr="disabled" @disabled($loop->last) class="hs-order-btn" title="Move down">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <button
                type="button"
                wire:click="toggleSpecial({{ $special->id }})"
                wire:loading.attr="disabled"
                wire:target="toggleSpecial({{ $special->id }})"
                class="hs-switch-btn {{ $special->is_active ? 'is-on' : '' }}"
            >
                <span class="hs-switch-thumb"></span>
            </button>
        </div>
        @empty
        <div class="hs-empty">No specials yet.</div>
        @endforelse

    </div>

    {{-- Yamaha Promotions --}}
    <div class="hs-card hs-margin-top">

        <div class="hs-card-header">
            <h3>Yamaha Promotions</h3>
            <p>Synced from the Yamaha Motor API — shown in the homepage hero slider, the homepage "Specials &amp; Offers" grid, and the /specials page. Turning one off here hides it everywhere and survives the next sync.</p>
        </div>

        @php $promos = $this->yamahaPromotions(); @endphp

        @forelse($promos as $promo)
        @php $isOutboard = $promo->type && str_starts_with($promo->type, 'Outboard'); @endphp
        <div class="hs-offer-row">
            @if($promo->image || $promo->brief_image)
                <img src="{{ $promo->image ?: $promo->brief_image }}" alt="" class="hs-offer-thumb">
            @else
                <div class="hs-offer-thumb-empty"></div>
            @endif

            <div class="hs-offer-info">
                <div class="hs-offer-title">
                    {{ $promo->head }}
                    @unless($promo->active)
                        <span class="hs-badge hs-badge-inactive">Inactive in Yamaha feed</span>
                    @endunless
                    @if($isOutboard)
                        <span class="hs-badge hs-badge-muted">Outboard — always excluded</span>
                    @endif
                </div>
                @if($promo->brief)
                <div class="hs-offer-subtitle">{{ $promo->brief }}</div>
                @endif
            </div>

            <div class="hs-order-btns">
                <button type="button" wire:click="moveYamahaPromotionUp({{ $promo->id }})" wire:loading.attr="disabled" @disabled($loop->first) class="hs-order-btn" title="Move up">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" wire:click="moveYamahaPromotionDown({{ $promo->id }})" wire:loading.attr="disabled" @disabled($loop->last) class="hs-order-btn" title="Move down">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <button
                type="button"
                wire:click="toggleYamahaPromotion({{ $promo->id }})"
                wire:loading.attr="disabled"
                wire:target="toggleYamahaPromotion({{ $promo->id }})"
                class="hs-switch-btn {{ ! $promo->hidden ? 'is-on' : '' }}"
            >
                <span class="hs-switch-thumb"></span>
            </button>
        </div>
        @empty
        <div class="hs-empty">No promotions synced yet.</div>
        @endforelse

    </div>

    {{-- Honda Offers --}}
    <div class="hs-card hs-margin-top">

        <div class="hs-card-header">
            <h3>Honda Offers</h3>
            <p>Shown on the /honda/offers page and eligible for the homepage slider. Full editing is under "Honda Offers" — this is a quick on/off.</p>
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
                </div>
                @if($offer->subtitle)
                <div class="hs-offer-subtitle">{{ $offer->subtitle }}</div>
                @endif
            </div>

            <div class="hs-order-btns">
                <button type="button" wire:click="moveHondaOfferUp({{ $offer->id }})" wire:loading.attr="disabled" @disabled($loop->first) class="hs-order-btn" title="Move up">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button type="button" wire:click="moveHondaOfferDown({{ $offer->id }})" wire:loading.attr="disabled" @disabled($loop->last) class="hs-order-btn" title="Move down">
                    <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <button
                type="button"
                wire:click="toggleHondaOffer({{ $offer->id }})"
                wire:loading.attr="disabled"
                wire:target="toggleHondaOffer({{ $offer->id }})"
                class="hs-switch-btn {{ $offer->is_active ? 'is-on' : '' }}"
            >
                <span class="hs-switch-thumb"></span>
            </button>
        </div>
        @empty
        <div class="hs-empty">No Honda offers yet.</div>
        @endforelse

    </div>

</div>
</x-filament-panels::page>
