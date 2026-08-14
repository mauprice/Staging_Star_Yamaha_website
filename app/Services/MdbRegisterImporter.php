<?php

namespace App\Services;

use App\Models\StockEntry;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class MdbRegisterImporter
{
    /**
     * @return array<int, string>
     */
    public function availableTables(string $path): array
    {
        $this->ensureMdbtoolsInstalled();
        $this->ensureFileExists($path);

        return explode("\n", trim(Process::run(['mdb-tables', '-1', $path])->output()));
    }

    /**
     * Imports an Access table, inserting only rows whose legacy `ID` isn't
     * already present in stock_entries.legacy_id. Safe to call repeatedly
     * with the same or an updated file.
     *
     * @return array{total: int, skipped: int, inserted: int}
     */
    public function import(string $path, string $table = 'registerTab'): array
    {
        $this->ensureMdbtoolsInstalled();
        $this->ensureFileExists($path);

        $tables = $this->availableTables($path);

        if (! in_array($table, $tables, true)) {
            throw new RuntimeException("Table '{$table}' not found in {$path}. Available tables: ".implode(', ', $tables));
        }

        $csvPath = tempnam(sys_get_temp_dir(), 'mdb_export_').'.csv';

        try {
            $result = Process::run(['mdb-export', $path, $table], function (string $type, string $output) use ($csvPath) {
                file_put_contents($csvPath, $output, FILE_APPEND);
            });

            if ($result->failed()) {
                throw new RuntimeException('mdb-export failed: '.$result->errorOutput());
            }

            return $this->importFromCsv($csvPath);
        } finally {
            @unlink($csvPath);
        }
    }

    /**
     * @return array{total: int, skipped: int, inserted: int}
     */
    private function importFromCsv(string $csvPath): array
    {
        $handle = fopen($csvPath, 'r');
        $header = fgetcsv($handle, 0, ',', '"', '\\');

        if ($header === false) {
            fclose($handle);

            throw new RuntimeException('Exported CSV was empty.');
        }

        $existingLegacyIds = StockEntry::query()->whereNotNull('legacy_id')->pluck('legacy_id')->flip();

        $totalRows = 0;
        $skipped = 0;
        $toInsert = [];
        $now = now();

        while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
            $totalRows++;
            $record = array_combine($header, $row);
            $legacyId = (int) $record['ID'];

            if ($existingLegacyIds->has($legacyId)) {
                $skipped++;

                continue;
            }

            $toInsert[] = $this->mapRow($record, $legacyId, $now);
        }

        fclose($handle);

        foreach (array_chunk($toInsert, 500) as $chunk) {
            StockEntry::insert($chunk);
        }

        return [
            'total' => $totalRows,
            'skipped' => $skipped,
            'inserted' => count($toInsert),
        ];
    }

    private function ensureMdbtoolsInstalled(): void
    {
        if (Process::run('which mdb-export')->failed()) {
            throw new RuntimeException('mdbtools is not installed. Run: sudo apt-get install -y mdbtools');
        }
    }

    private function ensureFileExists(string $path): void
    {
        if (! is_file($path)) {
            throw new RuntimeException("File not found: {$path}");
        }
    }

    /**
     * Maps an Access CSV row (same shouty legacy column names as the original
     * SQL dump) onto the stock_entries schema. sold_price is normalized the
     * same way the initial import was (legacy app never reliably persisted
     * it); profit and GST fields are kept verbatim, consistent with the
     * original import's policy of not silently rewriting historical figures.
     */
    private function mapRow(array $row, int $legacyId, $now): array
    {
        $balPaid = $this->numeric($row['BAL PAID'] ?? null);
        $tradeInPrice = $this->numeric($row['TRADE IN PRICE'] ?? null);

        return [
            'legacy_id' => $legacyId,
            'description' => $this->blank($row['DESCRIPTION'] ?? null),
            'stock_no' => $this->blank($row['STOCK NO'] ?? null),
            'vin_no' => $this->blank($row['VIN NO'] ?? null),
            'rego_no' => $this->blank($row['REGO_No'] ?? null),
            'ymaf_unit_no' => $this->blank($row['YMAF UNIT NO'] ?? null),
            'purchase_date' => $this->parseDate($row['PURCHASE DATE'] ?? null),
            'purchase_price' => $this->numeric($row['PURCHASE PRICE'] ?? null),
            'price_before_writeback' => $this->numeric($row['PUR PRICE BEFORE WRITE BACK'] ?? null),
            'writeback' => $this->numeric($row['WRITE BACK'] ?? null),
            'revs_transfer_fee' => $this->numeric($row['REVS TRANS FEE'] ?? null),
            'rego_fee' => $this->numeric($row['REGO'] ?? null),
            'stamp_duty' => $this->numeric($row['STAMP DUTY'] ?? null),
            'workshop_parts' => $this->numeric($row['W/SHOP PARTS ACC'] ?? null),
            'workshop_labour' => $this->numeric($row['W/SHOP LABOUR'] ?? null),
            'transport' => $this->numeric($row['TRANSPORT'] ?? null),
            'misc' => $this->numeric($row['MISC'] ?? null),
            'accessories_purchased' => $this->numeric($row['Acc_Purchased'] ?? null),
            'insurance' => $this->numeric($row['INSURANCE'] ?? null),
            'ymaf_rebate' => $this->numeric($row['YMAF REBATE'] ?? null),
            'gst_paid' => $this->numeric($row['GST_Piad'] ?? null),
            'gst_collected' => $this->numeric($row['GST_Collected'] ?? null),
            'gst_payable' => $this->numeric($row['GST PAYABLE'] ?? null),
            'trade_details' => $this->blank($row['TRADE DETAILS'] ?? null),
            'trade_in_price' => $tradeInPrice,
            'sold_date' => $this->parseDate($row['SOLD DATE'] ?? null),
            'sold_to' => $this->blank($row['SOLD TO'] ?? null),
            'inv_no' => $this->blank($row['INV NO'] ?? null),
            'salesman' => $this->blank($row['SALESMAN'] ?? null),
            'bal_paid' => $balPaid,
            'sold_price' => round($balPaid + $tradeInPrice, 2),
            'profit' => $this->numeric($row['PROFIT'] ?? null),
            'comm_paid_date' => $this->parseDate($row['COMM PAID'] ?? null),
            'bal_banked' => $this->numeric($row['Bal_Banked'] ?? null),
            'date_banked' => $this->parseDate($row['Date_Banked'] ?? null),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function blank(?string $value): ?string
    {
        return ($value === null || $value === '') ? null : $value;
    }

    private function numeric(?string $value): float
    {
        return ($value === null || $value === '') ? 0.0 : (float) $value;
    }

    /**
     * mdb-export renders Access datetimes as "MM/DD/YY HH:MM:SS" (two-digit
     * year). Reject anything that doesn't parse rather than guess.
     */
    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $date = \DateTime::createFromFormat('m/d/y H:i:s', $value) ?: \DateTime::createFromFormat('m/d/y', $value);

        return $date ? $date->format('Y-m-d') : null;
    }
}
