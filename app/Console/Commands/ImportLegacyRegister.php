<?php

namespace App\Console\Commands;

use App\Models\StockEntry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

#[Signature('stock-entries:import {--staging-db=northstar_register_staging : Database holding the imported legacy SQL dump}')]
#[Description('Import the legacy Northstar Yamaha register table into stock_entries')]
class ImportLegacyRegister extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stagingTable = $this->option('staging-db').'.register';

        try {
            $count = DB::table($stagingTable)->count();
        } catch (QueryException) {
            $this->error("Couldn't read {$stagingTable}. Import the legacy .sql dump into that database first.");

            return self::FAILURE;
        }

        if ($count === 0) {
            $this->error("{$stagingTable} has no rows to import.");

            return self::FAILURE;
        }

        if (StockEntry::count() > 0 && ! $this->confirm('stock_entries already has data. Truncate and re-import?')) {
            return self::SUCCESS;
        }

        StockEntry::truncate();

        $this->info("Importing {$count} legacy records...");
        $bar = $this->output->createProgressBar($count);

        DB::table($stagingTable)->orderBy('ID')->chunk(500, function ($rows) use ($bar) {
            $now = now();

            $insert = $rows->map(fn ($row) => $this->mapRow($row, $now))->all();

            StockEntry::insert($insert);
            $bar->advance(count($insert));
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Import complete: '.StockEntry::count().' stock entries created.');

        return self::SUCCESS;
    }

    /**
     * Maps a legacy `register` row (denormalized, shouty column names, varchar
     * dates) onto the stock_entries schema. Inserted verbatim via a bulk
     * insert (bypassing Eloquent events) so historical totals are preserved
     * exactly for the row-count/sum verification pass after import.
     */
    private function mapRow(object $row, $now): array
    {
        return [
            'description' => $row->DESCRIPTION,
            'stock_no' => $row->{'STOCK NO'},
            'vin_no' => $row->{'VIN NO'},
            'rego_no' => $row->REGO_No,
            'ymaf_unit_no' => $row->{'YMAF UNIT NO'},
            'purchase_date' => $this->parseDate($row->{'PURCHASE DATE'}),
            'purchase_price' => $row->{'PURCHASE PRICE'} ?? 0,
            'price_before_writeback' => $row->{'PUR PRICE BEFORE WRITE BACK'} ?? 0,
            'writeback' => $row->{'WRITE BACK'} ?? 0,
            'revs_transfer_fee' => $row->{'REVS TRANS FEE'} ?? 0,
            'rego_fee' => $row->REGO ?? 0,
            'stamp_duty' => $row->{'STAMP DUTY'} ?? 0,
            'workshop_parts' => $row->{'W/SHOP PARTS ACC'} ?? 0,
            'workshop_labour' => $row->{'W/SHOP LABOUR'} ?? 0,
            'transport' => $row->TRANSPORT ?? 0,
            'misc' => $row->MISC ?? 0,
            'accessories_purchased' => $row->Acc_Purchased ?? 0,
            'insurance' => $row->INSURANCE ?? 0,
            'ymaf_rebate' => $row->{'YMAF REBATE'} ?? 0,
            'gst_paid' => $row->GST_Piad ?? 0,
            'gst_collected' => $row->GST_Collected ?? 0,
            'gst_payable' => $row->{'GST PAYABLE'} ?? 0,
            'trade_details' => $row->{'TRADE DETAILS'},
            'trade_in_price' => $row->{'TRADE IN PRICE'} ?? 0,
            'sold_date' => $this->parseDate($row->{'SOLD DATE'}),
            'sold_to' => $row->{'SOLD TO'},
            'inv_no' => $row->{'INV NO'},
            'salesman' => $row->SALESMAN,
            'bal_paid' => $row->{'BAL PAID'} ?? 0,
            'sold_price' => $row->{'SOLD PRICE'} ?? 0,
            'profit' => $row->PROFIT ?? 0,
            'comm_paid_date' => $this->parseDate($row->{'COMM PAID'}),
            'bal_banked' => $row->Bal_Banked ?? 0,
            'date_banked' => $this->parseDate($row->Date_Banked),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * Legacy dates are stored as varchar Y-m-d strings, but a handful of
     * Date_Banked rows contain stray time-only values from a bad export —
     * those get dropped rather than guessed at.
     */
    private function parseDate(?string $value): ?string
    {
        if (! $value || $value === '0000-00-00' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }
}
