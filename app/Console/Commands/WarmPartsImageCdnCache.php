<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Yamaha\Parts\Models\Image;

class WarmPartsImageCdnCache extends Command
{
    protected $signature = 'parts:warm-cdn-cache {--concurrency=8}';

    protected $description = 'Requests every diagram image through the CDN so it is pulled into the edge cache ahead of real customer traffic';

    public function handle(): int
    {
        $concurrency = max(1, (int) $this->option('concurrency'));
        $baseUrl     = Setting::get('yamaha_parts_image_base_url', config('yamaha_parts.image_base_url'));

        $total = Image::where('extracted', true)->count();

        $this->info("Warming CDN cache for {$total} images (concurrency {$concurrency})...");

        $this->setProgress([
            'status'  => 'running',
            'current' => 0,
            'total'   => $total,
            'ok'      => 0,
            'failed'  => 0,
            'started' => now()->toIso8601String(),
        ]);

        $processed = 0;
        $ok        = 0;
        $failed    = 0;

        try {
            Image::where('extracted', true)
                ->orderBy('image_id')
                ->chunk($concurrency, function ($images) use ($baseUrl, &$processed, &$ok, &$failed, $total) {
                    $responses = Http::pool(fn ($pool) => $images->map(
                        fn ($image) => $pool->as((string) $image->image_id)
                            ->timeout(30)
                            ->get($baseUrl.$image->image_id.'.'.$image->format)
                    )->all());

                    foreach ($responses as $response) {
                        $processed++;

                        if ($response instanceof Response && $response->successful()) {
                            $ok++;
                        } else {
                            $failed++;
                        }
                    }

                    $this->setProgress([
                        'status'  => 'running',
                        'current' => $processed,
                        'total'   => $total,
                        'ok'      => $ok,
                        'failed'  => $failed,
                    ]);
                });
        } catch (\Throwable $e) {
            Log::error('Parts CDN warm-up: aborted with unhandled exception', ['error' => $e->getMessage()]);

            $this->setProgress([
                'status'  => 'failed',
                'current' => $processed,
                'total'   => $total,
                'ok'      => $ok,
                'failed'  => $failed,
                'error'   => $e->getMessage(),
            ]);

            $this->error('Warm-up failed: '.$e->getMessage());

            return self::FAILURE;
        }

        Setting::set('yamaha_parts_cdn_warmed_at', now()->toIso8601String());

        $this->setProgress([
            'status'  => 'done',
            'current' => $processed,
            'total'   => $total,
            'ok'      => $ok,
            'failed'  => $failed,
            'started' => null,
        ], now()->addMinutes(10));

        $this->info("Warm-up complete. {$ok} ok, {$failed} failed.");

        return self::SUCCESS;
    }

    private function setProgress(array $data, $ttl = null): void
    {
        Cache::put('parts_cdn_warm_progress', array_merge($data, [
            'updated' => now()->toIso8601String(),
        ]), $ttl ?? now()->addHours(4));
    }
}
