<?php

namespace Honda\Catalog\Console;

use Honda\Catalog\Assets\AssetManager;
use Honda\Catalog\DataTransferObjects\AssetRef;
use Honda\Catalog\Enums\AssetStatus;
use Honda\Catalog\Models\HondaAsset;
use Illuminate\Console\Command;

class MirrorAssetsCommand extends Command
{
    protected $signature = 'honda-catalog:assets:mirror';

    protected $description = 'Backfill/mirror any assets currently stored as remote (cdn) references.';

    public function handle(AssetManager $assets): int
    {
        $pending = HondaAsset::where('status', '!=', AssetStatus::Mirrored->value)->get();

        if ($pending->isEmpty()) {
            $this->info('Nothing to mirror - all assets are already mirrored.');

            return self::SUCCESS;
        }

        $this->info("Mirroring {$pending->count()} asset(s)...");
        $bar = $this->output->createProgressBar($pending->count());
        $bar->start();

        $mirrored = 0;
        $failed = 0;

        foreach ($pending as $asset) {
            $ref = new AssetRef($asset->guid, $asset->source_url, $asset->version_hash, $asset->host);
            $result = $assets->record($ref, 'mirror');

            $result->status === AssetStatus::Mirrored ? $mirrored++ : $failed++;

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->table(['Mirrored', 'Failed'], [[$mirrored, $failed]]);

        return self::SUCCESS;
    }
}
