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
     * yamaha_parts_{table} in the dump it emits, so no rewriting happens here.
     */
    private function syncTable(string $table): void
    {
        $ssh = config('yamaha_parts.catalogue_sync');
        $db  = config('database.connections.mysql');

        DB::table("yamaha_parts_{$table}")->truncate();

        // pipefail so a failed ssh export doesn't get masked by mysql exiting 0
        // on empty input.
        $shell = sprintf(
            'set -o pipefail; ssh -i %s -p %s -o BatchMode=yes -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15 %s@%s %s | mysql -h %s -P %s -u %s %s',
            escapeshellarg($ssh['key_path']),
            escapeshellarg((string) $ssh['port']),
            escapeshellarg($ssh['user']),
            escapeshellarg($ssh['host']),
            escapeshellarg($table),
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
    }

    private function setProgress(array $data, $ttl = null): void
    {
        Cache::put('parts_catalogue_sync_progress', array_merge($data, [
            'updated' => now()->toIso8601String(),
        ]), $ttl ?? now()->addHours(2));
    }
}
