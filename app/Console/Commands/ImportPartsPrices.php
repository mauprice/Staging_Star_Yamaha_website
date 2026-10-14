<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPartsPrices extends Command
{
    protected $signature = 'parts:import-prices
                            {files?* : Price file paths (default: all P*.001 in project root)}
                            {--dir= : Directory to scan for P*.001 files}
                            {--fresh : Truncate yamaha_parts_prices table before import}';

    protected $description = 'Import Yamaha YPIC fixed-width price files into the yamaha_parts_prices table (local to this app, independent of the parts-catalogue sync)';

    // Line format (107 chars, CRLF-terminated):
    //   [0:20]  part number (left-aligned, space-padded)
    //   [20:70] free-text description zone — see parseSupercession() for
    //           how replacement part numbers and NLA notes are encoded here
    //   [70:82] 12-char search key  (tail)
    //   [82:90] RRP  8-digit cents
    //   [90:98] Cost 8-digit cents
    //   [98:107] suffix 01100001A

    public function handle(): int
    {
        $files = $this->argument('files');

        if (empty($files)) {
            $dir = $this->option('dir') ?: base_path();
            $found = glob(rtrim($dir, '/') . '/P*.001');
            if (empty($found)) {
                $this->error("No P*.001 files found in {$dir}");
                return 1;
            }
            $files = $found;
        }

        if ($this->option('fresh')) {
            DB::table('yamaha_parts_prices')->truncate();
            $this->info('yamaha_parts_prices table truncated.');
        }

        $total   = 0;
        $skipped = 0;

        foreach ($files as $file) {
            if (!file_exists($file)) {
                $this->warn("File not found: {$file}");
                continue;
            }
            [$imported, $skip] = $this->processFile($file);
            $total   += $imported;
            $skipped += $skip;
            $this->info(sprintf('  %-30s %6d imported, %d skipped', basename($file), $imported, $skip));
        }

        $this->info("Done. Total: {$total} imported, {$skipped} skipped.");
        return 0;
    }

    private function processFile(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            $this->warn("Cannot open: {$path}");
            return [0, 0];
        }

        $source   = basename($path);
        $now      = now()->toDateTimeString();
        $batch    = [];
        $imported = 0;
        $skipped  = 0;
        $lineNo   = 0;

        while (($raw = fgets($handle)) !== false) {
            $lineNo++;
            $line = rtrim($raw, "\r\n");

            // Skip header (line 1) and lines too short to contain price data
            if ($lineNo === 1 || strlen($line) < 99) {
                $skipped++;
                continue;
            }

            $partNumber = trim(substr($line, 0, 20));
            if ($partNumber === '') {
                $skipped++;
                continue;
            }

            $rrpRaw  = substr($line, 82, 8);
            $costRaw = substr($line, 90, 8);

            if (!ctype_digit($rrpRaw) || !ctype_digit($costRaw)) {
                $skipped++;
                continue;
            }

            $rrp  = (int) $rrpRaw;
            $cost = (int) $costRaw;

            // Skip zero-price lines (usually continuation/summary rows)
            if ($rrp === 0 && $cost === 0) {
                $skipped++;
                continue;
            }

            [$supersededBy, $supersededNote] = $this->parseSupercession(substr($line, 20, 50));

            $batch[] = [
                'part_number'      => $partNumber,
                'description'      => trim(substr($line, 20, 36)),
                'rrp_cents'        => $rrp,
                'cost_cents'       => $cost,
                'superseded_by'    => $supersededBy,
                'superseded_note'  => $supersededNote,
                'currency'         => 'AUD',
                'source_file'      => $source,
                'imported_at'      => $now,
            ];

            if (count($batch) >= 500) {
                $this->upsertBatch($batch);
                $imported += count($batch);
                $batch = [];
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            $this->upsertBatch($batch);
            $imported += count($batch);
        }

        return [$imported, $skipped];
    }

    private function upsertBatch(array $rows): void
    {
        DB::table('yamaha_parts_prices')->upsert(
            $rows,
            ['part_number'],
            ['description', 'rrp_cents', 'cost_cents', 'superseded_by', 'superseded_note', 'currency', 'source_file', 'imported_at']
        );
    }

    /**
     * Parse supersession info out of the free-text description zone.
     *
     * Yamaha encodes this inconsistently. Two reliable shapes:
     *   1. "<description>  <replacement part number>" — description and
     *      replacement separated by a run of 2+ spaces, replacement looks
     *      like a real part number (alnum segments joined by hyphens).
     *   2. Anywhere in the zone, an "NLA" (No Longer Available) marker,
     *      optionally with a date — used whether or not a replacement is
     *      also present.
     * The literal "SUPERCESSION" description (Yamaha's own spelling) is
     * just one specific value for shape 1's description half — it carries
     * no separate meaning beyond "a replacement follows".
     *
     * Known gap: ~0.4% of rows glue an explanatory note directly onto the
     * replacement part number with no space ("HEAT RED18G-24770-A1"); those
     * are left unflagged rather than guessed, to avoid false positives.
     *
     * @return array{0: ?string, 1: ?string} [replacement part number, note]
     */
    private function parseSupercession(string $zone): array
    {
        $chunks = array_values(array_filter(
            array_map('trim', preg_split('/ {2,}/', $zone)),
            fn ($c) => $c !== ''
        ));

        $replacement = null;
        if (count($chunks) >= 2) {
            $last = end($chunks);
            if (strlen($last) >= 8 && preg_match('/^[0-9A-Z]{2,6}(-[0-9A-Z]{1,6}){1,3}$/', $last)) {
                $replacement = $last;
            }
        }

        $note = null;
        if (preg_match('/\bN[.*]?L[.*]?A[.*]?\b\.?\s*(\d{1,2}\/\d{2,4})?/i', $zone, $m)) {
            $note = isset($m[1]) && $m[1] !== '' ? 'NLA ' . $m[1] : 'NLA';
        } elseif ($replacement === null && count($chunks) === 1 && $chunks[0] === 'SUPERCESSION') {
            $note = 'Discontinued';
        }

        return [$replacement, $note];
    }
}
