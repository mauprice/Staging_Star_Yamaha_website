<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Honda\Catalog\Models\HondaOffer;
use Illuminate\Database\Eloquent\Collection;

class HomepageSliders extends Page
{
    protected string $view = 'filament.admin.pages.homepage-sliders';

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Homepage Sliders';

    protected static ?string $title = 'Homepage Sliders';

    protected static ?int $navigationSort = 6;

    public bool $show_yamaha_promotions = true;

    public bool $show_honda_offers = true;

    public function mount(): void
    {
        $this->show_yamaha_promotions = Setting::get('slider_show_yamaha_promotions', '1') === '1';
        $this->show_honda_offers      = Setting::get('slider_show_honda_offers', '1') === '1';
    }

    public function save(): void
    {
        Setting::set('slider_show_yamaha_promotions', $this->show_yamaha_promotions ? '1' : '0');
        Setting::set('slider_show_honda_offers', $this->show_honda_offers ? '1' : '0');

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    /**
     * Top-level Honda offers, newest/lowest-sort first — the same pool the
     * homepage slider draws from — so the admin can see exactly what's
     * eligible and flip individual ones on or off without touching whether
     * the offer is active elsewhere on the site.
     */
    public function hondaOffers(): Collection
    {
        return HondaOffer::whereNull('parent_id')
            ->with('image')
            ->orderBy('sort')
            ->get();
    }

    public function toggleHondaOfferSlide(int $offerId): void
    {
        $offer = HondaOffer::findOrFail($offerId);

        if (! $offer->image_asset_id) {
            Notification::make()
                ->title('This offer has no image')
                ->body('Add an image to this offer before including it in the slider.')
                ->danger()
                ->send();
            return;
        }

        $offer->update(['show_in_homepage_slider' => ! $offer->show_in_homepage_slider]);
    }
}
