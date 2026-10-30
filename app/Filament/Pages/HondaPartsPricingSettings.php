<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class HondaPartsPricingSettings extends Page
{
    protected string $view = 'filament.admin.pages.honda-parts-pricing-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Honda Parts Pricing';

    protected static ?string $title = 'Honda Parts Pricing';

    protected static ?int $navigationSort = 5;

    public string $honda_parts_markup_percent = '0';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function mount(): void
    {
        $this->honda_parts_markup_percent = Setting::get('honda_parts_markup_percent', '0');
    }

    public function save(): void
    {
        $markup = (float) $this->honda_parts_markup_percent;

        if ($markup < 0 || $markup > 500) {
            Notification::make()
                ->title('Invalid markup')
                ->body('Markup must be between 0 and 500%.')
                ->danger()
                ->send();
            return;
        }

        Setting::set('honda_parts_markup_percent', (string) $markup);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
