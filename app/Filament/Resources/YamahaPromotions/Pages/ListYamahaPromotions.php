<?php

namespace App\Filament\Resources\YamahaPromotions\Pages;

use App\Filament\Resources\YamahaPromotions\YamahaPromotionResource;
use Filament\Resources\Pages\ListRecords;

class ListYamahaPromotions extends ListRecords
{
    protected static string $resource = YamahaPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
