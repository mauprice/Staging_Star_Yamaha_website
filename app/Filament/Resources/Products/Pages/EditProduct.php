<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected array $pendingImages = [];

    protected ?string $pendingHero = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['images'] = $this->record->images()->orderBy('sort_order')->pluck('path')->all();
        $data['hero_image'] = $this->record->heroImage?->path;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingImages = $data['images'] ?? [];
        $this->pendingHero = $data['hero_image'] ?? null;
        unset($data['images'], $data['hero_image']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncImages($this->pendingImages, $this->pendingHero);
    }
}
