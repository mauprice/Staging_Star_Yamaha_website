<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;

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

    public int $warm_concurrency = 8;

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

    public function warmCache(): void
    {
        $php         = PHP_BINARY;
        $artisan     = base_path('artisan');
        $concurrency = max(1, (int) $this->warm_concurrency);
        $pipes       = [];

        proc_open("{$php} {$artisan} parts:warm-cdn-cache --concurrency={$concurrency} > /dev/null 2>&1 &", [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        Notification::make()
            ->title('Cache warm-up started')
            ->body('Requesting every diagram image through the CDN in the background. Progress updates automatically.')
            ->info()
            ->persistent()
            ->send();
    }

    public function getLastWarmed(): ?string
    {
        $ts = Setting::get('yamaha_parts_cdn_warmed_at', '');

        return $ts ? \Carbon\Carbon::parse($ts)->format('j M Y, g:i a') : null;
    }

    public function getWarmProgress(): array
    {
        $progress = Cache::get('parts_cdn_warm_progress');

        if (! $progress) {
            return ['running' => false, 'failed' => false];
        }

        if ($progress['status'] === 'running' && isset($progress['updated'])) {
            $updated = \Carbon\Carbon::parse($progress['updated']);

            if ($updated->lt(now()->subMinutes(10))) {
                return [
                    'running' => false,
                    'failed'  => true,
                    'error'   => 'The warm-up stopped responding and was likely interrupted. Please try again.',
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
            return [
                'running'     => false,
                'failed'      => false,
                'ok'          => $progress['ok'] ?? null,
                'failedCount' => $progress['failed'] ?? null,
            ];
        }

        $current = (int) ($progress['current'] ?? 0);
        $total   = (int) ($progress['total'] ?? 0);
        $pct     = $total > 0 ? (int) round(($current / $total) * 100) : 0;

        return [
            'running'     => true,
            'failed'      => false,
            'current'     => $current,
            'total'       => $total,
            'pct'         => $pct,
            'ok'          => $progress['ok'] ?? 0,
            'failedCount' => $progress['failed'] ?? 0,
        ];
    }
}
