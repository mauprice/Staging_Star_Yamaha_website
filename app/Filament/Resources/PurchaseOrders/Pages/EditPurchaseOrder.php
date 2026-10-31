<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadCsv')
                ->label('Upload CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('csv')
                        ->label('CSV file')
                        ->helperText('Columns: part_number, quantity, unit_cost (header row required).')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->disk('local')
                        ->directory('po-imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->importCsv($data['csv']);
                }),
            DeleteAction::make(),
        ];
    }

    private function importCsv(string $path): void
    {
        $fullPath = Storage::disk('local')->path($path);
        $handle = fopen($fullPath, 'r');

        if (! $handle) {
            Notification::make()->title('Could not read the uploaded file')->danger()->send();

            return;
        }

        $header = array_map(fn ($h) => strtolower(trim($h)), fgetcsv($handle) ?: []);
        $partNumberIdx = array_search('part_number', $header, true);
        $quantityIdx = array_search('quantity', $header, true);
        $unitCostIdx = array_search('unit_cost', $header, true);

        if ($partNumberIdx === false || $quantityIdx === false || $unitCostIdx === false) {
            fclose($handle);
            Notification::make()->title('CSV must have part_number, quantity, unit_cost columns')->danger()->send();

            return;
        }

        $created = 0;
        $notFound = [];

        while (($row = fgetcsv($handle)) !== false) {
            $partNumber = trim($row[$partNumberIdx] ?? '');

            if ($partNumber === '') {
                continue;
            }

            $product = Product::where('part_number', $partNumber)->first();

            if (! $product) {
                $notFound[] = $partNumber;

                continue;
            }

            $this->record->lines()->create([
                'product_id' => $product->id,
                'quantity_ordered' => (int) ($row[$quantityIdx] ?? 0),
                'unit_cost' => (float) ($row[$unitCostIdx] ?? 0),
            ]);

            $created++;
        }

        fclose($handle);
        Storage::disk('local')->delete($path);

        $message = "Added {$created} line(s).";

        if ($notFound) {
            $message .= ' Not matched: '.implode(', ', array_slice($notFound, 0, 10)).(count($notFound) > 10 ? '…' : '');
        }

        Notification::make()
            ->title($notFound ? 'Imported with some unmatched part numbers' : 'CSV imported')
            ->body($message)
            ->color($notFound ? 'warning' : 'success')
            ->send();

        $this->fillForm();
    }
}
