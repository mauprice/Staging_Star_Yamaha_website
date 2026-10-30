<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncHondaPartsCatalogue extends Command
{
    protected $signature = 'honda:sync-catalogue';

    protected $description = 'Copy the Honda electronic parts catalogue from the standalone honda_parts app\'s database into the local honda_parts_* tables';

    // Parent tables before children — not strictly required for a flat
    // INSERT...SELECT copy (no FK constraints are declared), but keeps the
    // same load order convention as Yamaha's sync command for readability.
    // Maps target table suffix => source table name in the honda_parts DB
    // (the standalone app's tables aren't honda_parts_-prefixed).
    private const TABLES = [
        'catalogues' => 'catalogues',
        'models' => 'models',
        'assemblies' => 'assemblies',
        'model_assemblies' => 'model_assemblies',
        'parts' => 'parts',
        'model_parts' => 'model_parts',
        'assembly_images' => 'assembly_images',
        'images' => 'images',
        'prices' => 'prices',
    ];

    public function handle(): int
    {
        $total = count(self::TABLES);
        $this->info('Starting Honda parts catalogue sync...');

        $i = 0;
        foreach (self::TABLES as $targetSuffix => $sourceTable) {
            $i++;
            $this->line("  [{$i}/{$total}] Syncing {$targetSuffix}...");
            $rows = $this->syncTable($targetSuffix, $sourceTable);
            $this->line("    done — {$rows} rows");
        }

        Setting::set('honda_parts_catalogue_synced_at', now()->toIso8601String());

        $this->info('Honda parts catalogue sync complete.');

        return self::SUCCESS;
    }

    /**
     * Same zero-downtime swap pattern as Yamaha's SyncPartsCatalogue: build
     * into a throwaway _sync_tmp table, only swap it in atomically once fully
     * loaded, so a reader never sees a missing or half-loaded table. Unlike
     * Yamaha's version this is a single-server cross-database INSERT...SELECT
     * (both databases live on the same local MySQL instance) rather than an
     * SSH/mysqldump pipe, since there's no network boundary to cross here.
     */
    private function syncTable(string $targetSuffix, string $sourceTable): int
    {
        $live = "honda_parts_{$targetSuffix}";
        $tmp = "{$live}_sync_tmp";
        $old = "{$live}_sync_old";
        $sourceDb = config('database.connections.honda_parts.database');

        $targetColumns = Schema::getColumnListing($live);
        $sourceColumns = Schema::connection('honda_parts')->getColumnListing($sourceTable);
        $columns = array_values(array_intersect($targetColumns, $sourceColumns));

        if (empty($columns)) {
            throw new \RuntimeException("No matching columns between {$live} and honda_parts.{$sourceTable}");
        }

        $columnList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));

        DB::statement("DROP TABLE IF EXISTS `{$tmp}`");
        DB::statement("CREATE TABLE `{$tmp}` LIKE `{$live}`");

        try {
            DB::statement(
                "INSERT INTO `{$tmp}` ({$columnList}) SELECT {$columnList} FROM `{$sourceDb}`.`{$sourceTable}`"
            );

            $count = DB::table($tmp)->count();

            DB::statement("DROP TABLE IF EXISTS `{$old}`");
            DB::statement("RENAME TABLE `{$live}` TO `{$old}`, `{$tmp}` TO `{$live}`");
            DB::statement("DROP TABLE IF EXISTS `{$old}`");

            return $count;
        } finally {
            // Leftover only on failure — the happy path already renamed $tmp away.
            DB::statement("DROP TABLE IF EXISTS `{$tmp}`");
        }
    }
}
