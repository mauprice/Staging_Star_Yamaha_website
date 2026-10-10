<?php

namespace App\Filament\Resources\YamahaPromotions\Pages;

use App\Filament\Resources\YamahaPromotions\YamahaPromotionResource;
use Filament\Resources\Pages\EditRecord;

class EditYamahaPromotion extends EditRecord
{
    protected static string $resource = YamahaPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
