<?php

namespace App\Filament\Pages;

use App\Models\YamahaBanner;
use App\Models\YamahaColor;
use App\Models\YamahaFeature;
use App\Models\YamahaImage;
use App\Models\YamahaProduct;
use App\Models\YamahaPromotion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

class YamahaSync extends Page
{
    protected string $view = 'filament.pages.yamaha-sync';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Sync Yamaha API Data';

    protected static ?string $title = 'Yamaha API Sync';

    protected static ?int $navigationSort = 2;

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
                ->modalHeading('Sync Yamaha Product Data')
                ->modalDescription('This fetches all products, features, colours, images and promotions from the Yamaha Motor AU API. It runs in the background and takes 2–3 minutes — the counts on this page will update automatically when it finishes.')
                ->modalSubmitActionLabel('Start Sync')
                ->action(function () {
                    $php     = PHP_BINARY;
                    $artisan = base_path('artisan');
                    $pipes   = [];

                    proc_open("{$php} {$artisan} yamaha:sync > /dev/null 2>&1 &", [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w'],
                    ], $pipes);

                    Notification::make()
                        ->title('Sync started')
                        ->body('The Yamaha API sync is running in the background. Counts will update when complete.')
                        ->info()
                        ->persistent()
                        ->send();
                }),
        ];
    }

    public function getStats(): array
    {
        $lastSynced = YamahaProduct::max('synced_at');

        return [
            [
                'label' => 'Products',
                'value' => number_format(YamahaProduct::count()),
                'icon'  => 'heroicon-o-cube',
                'color' => 'blue',
            ],
            [
                'label' => 'Promotions',
                'value' => number_format(YamahaPromotion::count()),
                'icon'  => 'heroicon-o-megaphone',
                'color' => 'red',
            ],
            [
                'label' => 'Features',
                'value' => number_format(YamahaFeature::count()),
                'icon'  => 'heroicon-o-star',
                'color' => 'yellow',
            ],
            [
                'label' => 'Colours',
                'value' => number_format(YamahaColor::count()),
                'icon'  => 'heroicon-o-swatch',
                'color' => 'purple',
            ],
            [
                'label' => 'Banners',
                'value' => number_format(YamahaBanner::count()),
                'icon'  => 'heroicon-o-photo',
                'color' => 'green',
            ],
            [
                'label' => 'Gallery Images',
                'value' => number_format(YamahaImage::count()),
                'icon'  => 'heroicon-o-camera',
                'color' => 'orange',
            ],
        ];
    }

    public function getLastSynced(): ?string
    {
        $ts = YamahaProduct::max('synced_at');
        return $ts ? \Carbon\Carbon::parse($ts)->format('j M Y, g:i a') : null;
    }

    public function getSyncProgress(): array
    {
        $progress = Cache::get('yamaha_sync_progress');

        if (! $progress) {
            return ['running' => false, 'failed' => false];
        }

        // Self-heal: if a "running" sync hasn't touched its progress cache in
        // 10 minutes, the background process died without reaching a terminal
        // state (killed, fatal error, etc). Treat it as failed instead of
        // showing an indefinite spinner.
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

        $label = match ($phase) {
            'starting'   => 'Starting sync…',
            'products'   => "Syncing products ({$current} of {$total})",
            'promotions' => 'Syncing promotions…',
            'news'       => 'Syncing news & events…',
            default      => 'Running…',
        };

        return [
            'running'  => true,
            'failed'   => false,
            'phase'    => $phase,
            'current'  => $current,
            'total'    => $total,
            'pct'      => $pct,
            'label'    => $label,
        ];
    }
}
