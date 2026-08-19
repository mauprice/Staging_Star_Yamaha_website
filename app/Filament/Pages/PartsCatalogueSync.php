<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Yamaha\Parts\Models\Assembly;
use Yamaha\Parts\Models\Image;
use Yamaha\Parts\Models\Part;
use Yamaha\Parts\Models\Price;
use Yamaha\Parts\Models\Product;

class PartsCatalogueSync extends Page
{
    protected string $view = 'filament.pages.parts-catalogue-sync';

    protected static string|\UnitEnum|null $navigationGroup = 'Admin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Sync Parts Catalogue';

    protected static ?string $title = 'Parts Catalogue Sync';

    protected static ?int $navigationSort = 8;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'Manager']) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Now')
                ->icon('heroicon-o-circle-stack')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Sync Parts Catalogue')
                ->modalDescription('This pulls the latest products, parts, assemblies, images and prices from NorthStar Yamaha\'s catalogue database, replacing what\'s stored here. It runs in the background and can take several minutes — the counts on this page will update automatically when it finishes.')
                ->modalSubmitActionLabel('Start Sync')
                ->action(function () {
                    $php     = PHP_BINARY;
                    $artisan = base_path('artisan');
                    $pipes   = [];

                    proc_open("{$php} {$artisan} parts:sync-catalogue > /dev/null 2>&1 &", [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ], $pipes);

                    Notification::make()
                        ->title('Sync started')
                        ->body('The parts catalogue sync is running in the background. Counts will update when complete.')
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
                'label' => 'Products',
                'value' => number_format(Product::count()),
                'icon'  => 'heroicon-o-cube',
                'color' => 'blue',
            ],
            [
                'label' => 'Parts',
                'value' => number_format(Part::count()),
                'icon'  => 'heroicon-o-cog-6-tooth',
                'color' => 'purple',
            ],
            [
                'label' => 'Assemblies',
                'value' => number_format(Assembly::count()),
                'icon'  => 'heroicon-o-squares-2x2',
                'color' => 'yellow',
            ],
            [
                'label' => 'Images',
                'value' => number_format(Image::count()),
                'icon'  => 'heroicon-o-photo',
                'color' => 'green',
            ],
            [
                'label' => 'Prices',
                'value' => number_format(Price::count()),
                'icon'  => 'heroicon-o-currency-dollar',
                'color' => 'orange',
            ],
        ];
    }

    public function getLastSynced(): ?string
    {
        $ts = Setting::get('yamaha_parts_catalogue_synced_at', '');

        return $ts ? \Carbon\Carbon::parse($ts)->format('j M Y, g:i a') : null;
    }

    public function getSyncProgress(): array
    {
        $progress = Cache::get('parts_catalogue_sync_progress');

        if (! $progress) {
            return ['running' => false, 'failed' => false];
        }

        if ($progress['status'] === 'running' && isset($progress['updated'])) {
            $updated = \Carbon\Carbon::parse($progress['updated']);

            if ($updated->lt(now()->subMinutes(10))) {
                return [
                    'running' => false,
                    'failed'  => true,
                    'error'   => 'The sync stopped responding and was likely interrupted. Please try again.',
                ];
            }
        }

        if ($progress['status'] === 'failed') {
            return [
                'running' => false,
                'failed'  => true,
                'error'   => $progress['error'] ?? 'Unknown error.',
            ];
        }

        if ($progress['status'] !== 'running') {
            return ['running' => false, 'failed' => false];
        }

        $current = (int) ($progress['current'] ?? 0);
        $total   = (int) ($progress['total'] ?? 0);
        $phase   = $progress['phase'] ?? 'starting';

        $pct = ($total > 0) ? (int) round(($current / $total) * 100) : 0;

        $label = $phase === 'starting'
            ? 'Starting sync…'
            : "Syncing {$phase}… ({$current} of {$total} tables)";

        return [
            'running' => true,
            'failed'  => false,
            'phase'   => $phase,
            'current' => $current,
            'total'   => $total,
            'pct'     => $pct,
            'label'   => $label,
        ];
    }
}
