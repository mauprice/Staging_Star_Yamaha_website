<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PartsImageSettings extends Page
{
    protected string $view = 'filament.admin.pages.parts-image-settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?string $navigationLabel = 'Parts Image CDN';

    protected static ?string $title = 'Parts Image CDN';

    protected static ?int $navigationSort = 7;

    public const SETTING_KEY = 'yamaha_parts_image_base_url';

    public string $image_base_url = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    public function mount(): void
    {
        $this->image_base_url = Setting::get(self::SETTING_KEY, config('yamaha_parts.image_base_url'));
    }

    public function save(): void
    {
        $url = trim($this->image_base_url);

        if (! filter_var($url, FILTER_VALIDATE_URL) || ! str_starts_with($url, 'https://')) {
            Notification::make()
                ->title('Invalid URL')
                ->body('Please enter a full https:// URL.')
                ->danger()
                ->send();
            return;
        }

        if (! str_ends_with($url, '/')) {
            $url .= '/';
        }

        $this->image_base_url = $url;

        Setting::set(self::SETTING_KEY, $url);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
