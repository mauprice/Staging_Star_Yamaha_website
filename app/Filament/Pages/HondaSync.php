<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Honda\Catalog\Models\HondaAsset;
use Honda\Catalog\Models\HondaColour;
use Honda\Catalog\Models\HondaModel;
use Honda\Catalog\Models\HondaModelFeature;
use Honda\Catalog\Models\HondaOffer;
use Honda\Catalog\Models\HondaSpecification;

class HondaSync extends Page
{
    protected string $view = 'filament.pages.honda-sync';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Sync Honda Site Data';

    protected static ?string $title = 'Honda Site Sync';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Website';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Now')
                ->icon('heroicon-o-arrow-path')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Sync Honda Site Data')
                ->modalDescription('This crawls the Honda Australia website for model catalog data and current offers. It runs in the background and can take a few minutes — the counts on this page will update automatically when it finishes.')
                ->modalSubmitActionLabel('Start Sync')
                ->action(function () {
                    $php     = PHP_BINARY;
                    $artisan = base_path('artisan');
                    $pipes   = [];

                    proc_open(
                        "{$php} {$artisan} honda-catalog:sync > /dev/null 2>&1 && {$php} {$artisan} honda-catalog:sync-offers > /dev/null 2>&1 &",
                        [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                        $pipes,
                    );

                    Notification::make()
                        ->title('Sync started')
                        ->body('The Honda site sync is running in the background. Counts will update when complete.')
                        ->info()
                        ->persistent()
                        ->send();
                }),
        ];
    }

    public function getStats(): array
    {
        return [
            [
                'label' => 'Models',
                'value' => number_format(HondaModel::count()),
                'icon'  => 'heroicon-o-cube',
                'color' => 'blue',
            ],
            [
                'label' => 'Offers',
                'value' => number_format(HondaOffer::count()),
                'icon'  => 'heroicon-o-megaphone',
                'color' => 'red',
            ],
            [
                'label' => 'Specifications',
                'value' => number_format(HondaSpecification::count()),
                'icon'  => 'heroicon-o-list-bullet',
                'color' => 'yellow',
            ],
            [
                'label' => 'Colours',
                'value' => number_format(HondaColour::count()),
                'icon'  => 'heroicon-o-swatch',
                'color' => 'purple',
            ],
            [
                'label' => 'Features',
                'value' => number_format(HondaModelFeature::count()),
                'icon'  => 'heroicon-o-star',
                'color' => 'green',
            ],
            [
                'label' => 'Assets',
                'value' => number_format(HondaAsset::count()),
                'icon'  => 'heroicon-o-camera',
                'color' => 'orange',
            ],
        ];
    }

    public function getLastSynced(): ?string
    {
        $ts = max(
            HondaModel::max('last_scraped_at'),
            HondaOffer::max('last_scraped_at'),
        );

        return $ts ? \Carbon\Carbon::parse($ts)->format('j M Y, g:i a') : null;
    }
}
