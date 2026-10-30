<?php

namespace App\Services;

use App\Models\Setting;
use Honda\Parts\Models\Price;
use Illuminate\Support\Collection;

/**
 * Honda equivalent of PartsPricing — deliberately simpler. Confirmed by
 * direct query against the live honda_parts database that superseded_by
 * never chains (no A->B->C), so unlike Yamaha's resolveLatest() hop-walk,
 * a flat lookup is correct here. Uses its own markup setting key
 * (honda_parts_markup_percent) so it can never silently inherit Yamaha's.
 */
class HondaPartsPricing
{
    public function markupMultiplier(): float
    {
        return 1 + ((float) Setting::get('honda_parts_markup_percent', '0') / 100);
    }

    /**
     * @param  Collection<int, string>  $searchPartNumbers  the de-hyphenated
     *         search_part_no values (not the hyphenated part_no) — that's
     *         the actual key Honda's price file is keyed by.
     * @return Collection<string, array{cents: int, currency: string, superseded_by: ?string, superseded_note: ?string}> keyed by search_part_no
     */
    public function forNumbers(Collection $searchPartNumbers): Collection
    {
        $multiplier = $this->markupMultiplier();

        return Price::whereIn('part_number', $searchPartNumbers->filter()->unique()->values())
            ->get(['part_number', 'rrp_cents', 'superseded_by', 'superseded_note'])
            ->mapWithKeys(fn ($price) => [
                $price->part_number => [
                    'cents' => (int) round($price->rrp_cents * $multiplier),
                    'currency' => 'AUD',
                    'superseded_by' => $price->superseded_by,
                    'superseded_note' => $price->superseded_note,
                ],
            ]);
    }

    /**
     * @return array{cents: int, currency: string, superseded_by: ?string, superseded_note: ?string}|null
     */
    public function forNumber(string $searchPartNumber): ?array
    {
        return $this->forNumbers(collect([$searchPartNumber]))->get($searchPartNumber);
    }
}
