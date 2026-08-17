<?php

namespace Honda\Catalog\Console;

use Honda\Catalog\Console\Support\DryRunAborted;
use Honda\Catalog\Services\OfferIngestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OfferSyncCommand extends Command
{
    protected $signature = 'honda-catalog:sync-offers
        {--dry-run : Run the full sync without persisting any changes}
        {--force : Re-ingest offers even if their content hash is unchanged}
        {--with-assets : Mirror assets according to the configured strategy, instead of always using cdn}';

    protected $description = 'Crawl the Honda AU offers pages and sync current promotions.';

    public function handle(OfferIngestService $ingest): int
    {
        $force = (bool) $this->option('force');
        $withAssets = (bool) $this->option('with-assets');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Syncing Honda offers...');

        $stats = ['synced' => 0, 'skipped' => 0, 'deactivated' => 0, 'failed' => 0];

        $run = function () use ($ingest, $force, $withAssets, &$stats) {
            $stats = $ingest->syncAll($withAssets, $force);
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

        $this->table(['Synced', 'Skipped (unchanged)', 'Deactivated', 'Failed'], [[
            $stats['synced'], $stats['skipped'], $stats['deactivated'], $stats['failed'],
        ]]);

        if ($dryRun) {
            $this->comment('Dry run - no changes were persisted.');
        }

        return $stats['failed'] > 0 && $stats['synced'] === 0 && $stats['skipped'] === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
