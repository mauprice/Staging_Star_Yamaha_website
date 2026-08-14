<?php

namespace App\Console\Commands;

use App\Services\MdbRegisterImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

#[Signature('stock-entries:import-mdb
    {path=Register_be.mdb : Path to the .mdb file (relative paths resolve from the project root)}
    {--table=registerTab : Which Access table to read}')]
#[Description('Import (or re-import) an MS Access .mdb Register export, inserting only records not already present')]
class ImportMdbRegister extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MdbRegisterImporter $importer): int
    {
        $path = $this->argument('path');

        if (! str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        try {
            $result = $importer->import($path, $this->option('table'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Source rows: {$result['total']}");
        $this->info("Already present (skipped): {$result['skipped']}");
        $this->info("Newly imported: {$result['inserted']}");

        return self::SUCCESS;
    }
}
