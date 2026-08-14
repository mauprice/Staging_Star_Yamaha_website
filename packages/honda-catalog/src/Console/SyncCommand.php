<?php

namespace Honda\Catalog\Console;

use Honda\Catalog\Console\Support\DryRunAborted;
use Honda\Catalog\Crawling\SitemapCrawler;
use Honda\Catalog\Services\IngestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncCommand extends Command
{
    protected $signature = 'honda-catalog:sync
        {--model= : Only sync the model whose slug matches this value}
        {--dry-run : Run the full sync without persisting any changes}
        {--force : Re-ingest models even if their content hash is unchanged}
        {--with-assets : Mirror assets according to the configured strategy, instead of always using cdn}';

    protected $description = 'Crawl the Honda AU sitemap and sync model catalog data.';

    public function handle(SitemapCrawler $crawler, IngestService $ingest): int
    {
        $sitemapUrl = config('honda-catalog.sitemap_url');
        $this->info("Discovering models from {$sitemapUrl}...");

        try {
            $entries = $crawler->discover($sitemapUrl);
        } catch (Throwable $e) {
            $this->error("Failed to fetch sitemap: {$e->getMessage()}");

            return self::FAILURE;
        }

        if ($modelFilter = $this->option('model')) {
            $entries = array_values(array_filter(
                $entries,
                fn (array $e) => str_ends_with(rtrim($e['url'], '/'), "/{$modelFilter}"),
            ));

            if (empty($entries)) {
                $this->error("No model matching '{$modelFilter}' found in the sitemap.");

                return self::FAILURE;
            }
        }

        $this->info(count($entries).' model(s) to sync.');

        $bar = $this->output->createProgressBar(count($entries));
        $bar->start();

        $stats = ['synced' => 0, 'skipped' => 0, 'failed' => 0];
        $force = (bool) $this->option('force');
        $withAssets = (bool) $this->option('with-assets');
        $dryRun = (bool) $this->option('dry-run');

        $run = function () use ($entries, $ingest, $force, $withAssets, $bar, &$stats) {
            foreach ($entries as $entry) {
                $model = $ingest->ingest($entry['url'], $withAssets, $force);

                if ($model === null) {
                    $stats['failed']++;
                } elseif ($model->wasRecentlyCreated || $model->wasChanged()) {
                    $stats['synced']++;
                } else {
                    $stats['skipped']++;
                }

                $bar->advance();
            }
        };

        if ($dryRun) {
            try {
                DB::transaction(function () use ($run) {
                    $run();
                    throw new DryRunAborted;
                });
            } catch (DryRunAborted) {
                // expected - unwinds the transaction, changes are not persisted.
            }
        } else {
            $run();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(['Synced', 'Skipped (unchanged)', 'Failed'], [[
            $stats['synced'], $stats['skipped'], $stats['failed'],
        ]]);

        if ($dryRun) {
            $this->comment('Dry run - no changes were persisted.');
        }

        return $stats['failed'] > 0 && $stats['synced'] === 0 && $stats['skipped'] === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
