<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected array $pendingImages = [];

    protected ?string $pendingHero = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pendingImages = $data['images'] ?? [];
        $this->pendingHero = $data['hero_image'] ?? null;
        unset($data['images'], $data['hero_image']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncImages($this->pendingImages, $this->pendingHero);
    }
}
