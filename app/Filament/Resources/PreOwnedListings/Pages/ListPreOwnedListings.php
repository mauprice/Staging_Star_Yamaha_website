<?php

namespace App\Filament\Resources\PreOwnedListings\Pages;

use App\Filament\Resources\PreOwnedListings\PreOwnedListingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPreOwnedListings extends ListRecords
{
    protected static string $resource = PreOwnedListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
