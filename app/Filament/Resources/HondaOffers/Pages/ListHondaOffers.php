<?php

namespace App\Filament\Resources\HondaOffers\Pages;

use App\Filament\Resources\HondaOffers\HondaOfferResource;
use Filament\Resources\Pages\ListRecords;

class ListHondaOffers extends ListRecords
{
    protected static string $resource = HondaOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
