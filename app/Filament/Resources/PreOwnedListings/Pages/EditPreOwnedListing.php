<?php

namespace App\Filament\Resources\PreOwnedListings\Pages;

use App\Filament\Resources\PreOwnedListings\PreOwnedListingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPreOwnedListing extends EditRecord
{
    protected static string $resource = PreOwnedListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function callSchemaComponentMethod(string $componentKey, string $method, array $arguments = []): mixed
    {
        $result = parent::callSchemaComponentMethod($componentKey, $method, $arguments);

        if ($method === 'reorderUploadedFiles' && str($componentKey)->contains('images') && $this->record) {
            $rawState = data_get($this, 'data.images');

            if (is_array($rawState) && count($rawState) > 0) {
                $paths = array_values(array_filter($rawState, fn ($v) => is_string($v)));

                if (count($paths) > 0) {
                    $this->record->updateQuietly(['images' => $paths]);

                    // Re-key with non-integer-like string keys so JS JSON.parse doesn't
                    // auto-sort them numerically and corrupt the Livewire snapshot order.
                    $reKeyed = [];
                    foreach ($paths as $i => $path) {
                        $reKeyed['f' . $i] = $path;
                    }
                    data_set($this, 'data.images', $reKeyed);
                }
            }
        }

        return $result;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['images']) && $this->record) {
            $formImages = array_values(array_filter((array) $data['images'], fn ($v) => is_string($v)));
            $dbImages = $this->record->fresh()->images;

            $kept = array_values(array_filter($dbImages, fn ($p) => in_array($p, $formImages)));
            $new = array_values(array_filter($formImages, fn ($p) => ! in_array($p, $dbImages)));

            $data['images'] = array_merge($kept, $new);
        }

        return $data;
    }
}
