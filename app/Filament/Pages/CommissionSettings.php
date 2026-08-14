<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CommissionSettings extends Page
{
    protected string $view = 'filament.admin.pages.commission-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Commission Settings';

    protected static ?string $title = 'Commission Settings';

    protected static ?int $navigationSort = 1;

    public string $commission_rate    = '10';
    public string $commission_minimum = '50';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function mount(): void
    {
        $this->commission_rate    = Setting::get('commission_rate', '10');
        $this->commission_minimum = Setting::get('commission_minimum', '50');
    }

    public function save(): void
    {
        $rate = (float) $this->commission_rate;
        $min  = (float) $this->commission_minimum;

        if ($rate <= 0 || $rate > 100) {
            Notification::make()
                ->title('Invalid commission rate')
                ->body('Commission rate must be between 0 and 100.')
                ->danger()
                ->send();
            return;
        }

        if ($min < 0) {
            Notification::make()
                ->title('Invalid minimum commission')
                ->body('Minimum commission cannot be negative.')
                ->danger()
                ->send();
            return;
        }

        Setting::set('commission_rate', (string) $rate);
        Setting::set('commission_minimum', (string) $min);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
