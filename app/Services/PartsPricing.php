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
     * @return Collection<string, array{cents: int, currency: string, superseded_by: ?string, superseded_note: ?string}> keyed by part_number
     */
    public function forNumbers(Collection $partNumbers): Collection
    {
        $multiplier = $this->markupMultiplier();

        $prices = Price::whereIn('part_number', $partNumbers)
            ->get(['part_number', 'rrp_cents', 'currency', 'superseded_by', 'superseded_note']);

        $latest = $this->resolveLatest($prices->pluck('part_number'));

        return $prices->mapWithKeys(function ($price) use ($multiplier, $latest) {
            $current = $latest[$price->part_number] ?? $price->part_number;

            return [
                $price->part_number => [
                    'cents' => (int) round($price->rrp_cents * $multiplier),
                    'currency' => $price->currency,
                    'superseded_by' => $current !== $price->part_number ? $current : null,
                    'superseded_note' => $price->superseded_note,
                ],
            ];
        });
    }

    /**
     * @return array{cents: int, currency: string, superseded_by: ?string, superseded_note: ?string}|null
     */
    public function forNumber(string $partNumber): ?array
    {
        return $this->forNumbers(collect([$partNumber]))->get($partNumber);
    }

    /**
     * Walks each part number's superseded_by chain to its terminal (current)
     * part number — a part can be superseded several times over (seen in
     * practice: 6 hops for a common oil filter), so a single lookup only
     * tells you the *next* part, not the one actually worth selling.
     *
     * Batches one query per hop across every part number being resolved,
     * rather than one query per part, so this stays cheap on a 50-row
     * search result even when several rows have long chains.
     *
     * @param  \Illuminate\Support\Collection<int, string>  $partNumbers
     * @return array<string, string> original part_number => terminal part_number
     */
    private function resolveLatest(Collection $partNumbers): array
    {
        $frontier = $partNumbers->unique()->mapWithKeys(fn ($n) => [$n => $n])->all();

        for ($hop = 0; $hop < 20; $hop++) {
            $toLookup = collect($frontier)->unique()->values();

            $links = Price::whereIn('part_number', $toLookup)
                ->whereNotNull('superseded_by')
                ->pluck('superseded_by', 'part_number');

            if ($links->isEmpty()) {
                break;
            }

            $advanced = false;
            foreach ($frontier as $original => $current) {
                if ($links->has($current)) {
                    $frontier[$original] = $links->get($current);
                    $advanced = true;
                }
            }

            if (! $advanced) {
                break;
            }
        }

        return $frontier;
    }
}
