<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Yamaha\Parts\Models\Price;

/**
 * Customer-facing part pricing: catalogue RRP + the admin-configured markup.
 * Shared between the parts-finder API (display) and cart/checkout (charging),
 * so a part's price is derived the same way everywhere and can't drift.
 */
class PartsPricing
{
    public function markupMultiplier(): float
    {
        return 1 + ((float) Setting::get('parts_markup_percent', '0') / 100);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $partNumbers
     * @return Collection<string, array{cents: int, currency: string}> keyed by part_number
     */
    public function forNumbers(Collection $partNumbers): Collection
    {
        $multiplier = $this->markupMultiplier();

        return Price::whereIn('part_number', $partNumbers)
            ->get(['part_number', 'rrp_cents', 'currency'])
            ->mapWithKeys(fn ($price) => [
                $price->part_number => [
                    'cents' => (int) round($price->rrp_cents * $multiplier),
                    'currency' => $price->currency,
                ],
            ]);
    }

    /**
     * @return array{cents: int, currency: string}|null
     */
    public function forNumber(string $partNumber): ?array
    {
        return $this->forNumbers(collect([$partNumber]))->get($partNumber);
    }
}
