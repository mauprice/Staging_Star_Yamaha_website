<?php

namespace Honda\Parts;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HondaPartsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('honda-parts')
            ->hasMigrations([
                '2026_10_30_000001_create_honda_parts_tables',
                '2026_10_30_000002_create_honda_parts_prices_table',
            ]);
    }
}
