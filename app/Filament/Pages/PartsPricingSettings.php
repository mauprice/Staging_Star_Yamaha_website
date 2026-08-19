<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PartsPricingSettings extends Page
{
    protected string $view = 'filament.admin.pages.parts-pricing-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?string $navigationLabel = 'Parts Pricing';

    protected static ?string $title = 'Parts Pricing';

    protected static ?int $navigationSort = 4;

    public string $parts_markup_percent = '0';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function mount(): void
    {
        $this->parts_markup_percent = Setting::get('parts_markup_percent', '0');
    }

    public function save(): void
    {
        $markup = (float) $this->parts_markup_percent;

        if ($markup < 0 || $markup > 500) {
            Notification::make()
                ->title('Invalid markup')
                ->body('Markup must be between 0 and 500%.')
                ->danger()
                ->send();
            return;
        }

        Setting::set('parts_markup_percent', (string) $markup);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
