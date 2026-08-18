<?php

namespace Yamaha\Parts;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class YamahaPartsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('yamaha-parts')
            ->hasMigrations([
                '2026_01_01_000001_create_yamaha_parts_tables',
                '2026_01_01_000002_add_thumbnail_to_yamaha_parts_products',
                '2026_01_01_000003_add_catalogue_to_yamaha_parts_products',
                '2026_01_01_000004_create_yamaha_parts_prices_table',
            ]);
    }
}
