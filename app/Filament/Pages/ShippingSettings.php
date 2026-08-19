<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ShippingSettings extends Page
{
    protected string $view = 'filament.admin.pages.shipping-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Shipping Settings';

    protected static ?string $title = 'Shipping Settings';

    protected static ?int $navigationSort = 3;

    public string $shipping_flat_rate = '10';
    public string $shipping_free_threshold = '150';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function mount(): void
    {
        $this->shipping_flat_rate = Setting::get('shipping_flat_rate', '10');
        $this->shipping_free_threshold = Setting::get('shipping_free_threshold', '150');
    }

    public function save(): void
    {
        $rate = (float) $this->shipping_flat_rate;
        $threshold = (float) $this->shipping_free_threshold;

        if ($rate < 0) {
            Notification::make()
                ->title('Invalid flat rate')
                ->body('Shipping rate cannot be negative.')
                ->danger()
                ->send();

            return;
        }

        if ($threshold < 0) {
            Notification::make()
                ->title('Invalid free-shipping threshold')
                ->body('Free-shipping threshold cannot be negative.')
                ->danger()
                ->send();

            return;
        }

        Setting::set('shipping_flat_rate', (string) $rate);
        Setting::set('shipping_free_threshold', (string) $threshold);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
