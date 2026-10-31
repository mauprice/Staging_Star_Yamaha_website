<?php

namespace App\Filament\Pages;

use App\Models\Special;
use App\Models\YamahaPromotion;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Honda\Catalog\Models\HondaOffer;
use Illuminate\Database\Eloquent\Collection;

class Offers extends Page
{
    protected string $view = 'filament.admin.pages.offers';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Offers';

    protected static ?string $title = 'Offers';

    protected static ?int $navigationSort = 7;

    /**
     * Admin-created specials (/specials page, homepage grid). Already fully
     * managed under Specials — this is a read-through so admins see every
     * offer type in one place without having to visit three pages.
     */
    public function specials(): Collection
    {
        return Special::orderBy('sort_order')->get();
    }

    public function toggleSpecial(int $id): void
    {
        $special = Special::findOrFail($id);
        $special->update(['is_active' => ! $special->is_active]);
    }

    public function moveSpecialUp(int $id): void
    {
        $this->swapSort(Special::query(), 'sort_order', $id, -1);
    }

    public function moveSpecialDown(int $id): void
    {
        $this->swapSort(Special::query(), 'sort_order', $id, 1);
    }

    /**
     * All synced Yamaha promotions, including hidden ones (bypasses the
     * model's default scope) and Outboard-type ones, which the site already
     * excludes everywhere by design regardless of this toggle.
     */
    public function yamahaPromotions(): Collection
    {
        return YamahaPromotion::withoutGlobalScope('hidden')
            ->where('country', 'AU')
            ->orderBy('sort_index')
            ->get();
    }

    public function toggleYamahaPromotion(int $id): void
    {
        $promo = YamahaPromotion::withoutGlobalScope('hidden')->findOrFail($id);
        $promo->update(['hidden' => ! $promo->hidden]);
    }

    public function moveYamahaPromotionUp(int $id): void
    {
        $this->swapSort(YamahaPromotion::withoutGlobalScope('hidden')->where('country', 'AU'), 'sort_index', $id, -1);
    }

    public function moveYamahaPromotionDown(int $id): void
    {
        $this->swapSort(YamahaPromotion::withoutGlobalScope('hidden')->where('country', 'AU'), 'sort_index', $id, 1);
    }

    /**
     * Top-level Honda offers (/honda/offers page, homepage slider pool).
     * Already fully managed under Honda Offers — included here for the same
     * one-place-to-look reason as specials.
     */
    public function hondaOffers(): Collection
    {
        return HondaOffer::whereNull('parent_id')
            ->with('image')
            ->orderBy('sort')
            ->get();
    }

    public function toggleHondaOffer(int $id): void
    {
        $offer = HondaOffer::findOrFail($id);
        $offer->update(['is_active' => ! $offer->is_active]);
    }

    public function moveHondaOfferUp(int $id): void
    {
        $this->swapSort(HondaOffer::whereNull('parent_id'), 'sort', $id, -1);
    }

    public function moveHondaOfferDown(int $id): void
    {
        $this->swapSort(HondaOffer::whereNull('parent_id'), 'sort', $id, 1);
    }

    /**
     * Swap $column between the record at $id and its neighbour in $direction
     * (-1 up, 1 down) within $query's current ordering. A no-op at either
     * end of the list. Shared by all three offer types above — same idea as
     * Filament's own reorderable table column, just without a Table.
     */
    private function swapSort($query, string $column, int $id, int $direction): void
    {
        $records = $query->orderBy($column)->get();
        $position = $records->search(fn ($record) => $record->getKey() === $id);
        $swapWith = $position + $direction;

        if ($position === false || $swapWith < 0 || $swapWith >= $records->count()) {
            return;
        }

        $a = $records[$position];
        $b = $records[$swapWith];

        [$aValue, $bValue] = [$a->{$column}, $b->{$column}];
        $a->update([$column => $bValue]);
        $b->update([$column => $aValue]);
    }
}
