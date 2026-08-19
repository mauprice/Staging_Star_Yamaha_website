<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SyncPartsCatalogue extends Command
{
    protected $signature = 'parts:sync-catalogue';

    protected $description = 'Pull the latest YPIC parts catalogue data from NorthStar Yamaha into the local yamaha_parts_* tables';

    // Order matters: children before parents would leave dangling foreign
    // references mid-sync, so parents are truncated/reloaded first.
    private const TABLES = [
        'products', 'contents', 'assemblies', 'images',
        'assembly_images', 'parts', 'model_images', 'prices',
    ];

    public function handle(): int
    {
        $total = count(self::TABLES);

        $this->info('Starting parts catalogue sync...');

        $this->setProgress([
            'status'  => 'running',
            'phase'   => 'starting',
            'current' => 0,
            'total'   => $total,
            'started' => now()->toIso8601String(),
        ]);

        try {
            foreach (self::TABLES as $i => $table) {
                $this->setProgress([
                    'status'  => 'running',
                    'phase'   => $table,
                    'current' => $i,
                    'total'   => $total,
                ]);

                $this->info("Syncing {$table}...");
                $this->syncTable($table);
            }
        } catch (\Throwable $e) {
            Log::error('Parts catalogue sync: aborted with unhandled exception', ['error' => $e->getMessage()]);

            $this->setProgress([
                'status'  => 'failed',
                'phase'   => 'failed',
                'current' => 0,
                'total'   => $total,
                'error'   => $e->getMessage(),
            ]);

            $this->error('Sync failed: '.$e->getMessage());

            return self::FAILURE;
        }

        Setting::set('yamaha_parts_catalogue_synced_at', now()->toIso8601String());

        $this->setProgress([
            'status'  => 'done',
            'phase'   => 'done',
            'current' => $total,
            'total'   => $total,
            'started' => null,
        ], now()->addMinutes(10));

        $this->info('Parts catalogue sync complete.');

        return self::SUCCESS;
    }

    /**
     * Streams a table straight from NorthStar's mysqldump (over a restricted,
     * single-purpose SSH key) into the local mysql client via an OS-level pipe
     * — the parts table alone is ~1GB, so buffering the dump in PHP memory
     * isn't an option. The remote end already renames the table to
     * yamaha_parts_{table} in the dump it emits.
     *
     * The load target is a throwaway _sync_tmp table, never the live table
     * directly: if the SSH pull or import fails partway through, the live
     * table must be left exactly as it was. Only once the new data has fully
     * landed does an atomic RENAME TABLE swap it in — MySQL guarantees that
     * swap has no window where the table is missing or half-loaded.
     */
    private function syncTable(string $table): void
    {
        $ssh = config('yamaha_parts.catalogue_sync');
        $db  = config('database.connections.mysql');

        $live = "yamaha_parts_{$table}";
        $tmp  = "{$live}_sync_tmp";
        $old  = "{$live}_sync_old";

        DB::statement("DROP TABLE IF EXISTS `{$tmp}`");
        DB::statement("CREATE TABLE `{$tmp}` LIKE `{$live}`");

        try {
            // pipefail so a failed ssh export doesn't get masked by mysql
            // exiting 0 on empty input. The extra sed stage retargets the
            // dump's INSERTs from the live table name to the tmp one.
            $shell = sprintf(
                'set -o pipefail; ssh -i %s -p %s -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15 %s@%s %s | sed %s | mysql -h %s -P %s -u %s %s',
                escapeshellarg($ssh['key_path']),
                escapeshellarg((string) $ssh['port']),
                escapeshellarg($ssh['user']),
                escapeshellarg($ssh['host']),
                escapeshellarg($table),
                escapeshellarg("s/`{$live}`/`{$tmp}`/g"),
                escapeshellarg($db['host']),
                escapeshellarg((string) $db['port']),
                escapeshellarg($db['username']),
                escapeshellarg($db['database']),
            );

            $result = Process::timeout(1200)
                ->env(['MYSQL_PWD' => $db['password']])
                ->run(['bash', '-c', $shell]);

            if (! $result->successful()) {
                $error = trim($result->errorOutput()) ?: trim($result->output());

                throw new \RuntimeException("Failed to sync '{$table}': {$error}");
            }

            DB::statement("DROP TABLE IF EXISTS `{$old}`");
            DB::statement("RENAME TABLE `{$live}` TO `{$old}`, `{$tmp}` TO `{$live}`");
            DB::statement("DROP TABLE IF EXISTS `{$old}`");
        } finally {
            // Leftover only on failure — the happy path already renamed $tmp away.
            DB::statement("DROP TABLE IF EXISTS `{$tmp}`");
        }
    }

    private function setProgress(array $data, $ttl = null): void
    {
        Cache::put('parts_catalogue_sync_progress', array_merge($data, [
            'updated' => now()->toIso8601String(),
        ]), $ttl ?? now()->addHours(2));
    }
}
