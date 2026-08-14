<?php

namespace App\Filament\Resources\StockEntries\Pages;

use App\Filament\Resources\StockEntries\StockEntryResource;
use App\Services\MdbRegisterImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ListStockEntries extends ListRecords
{
    protected static string $resource = StockEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->importMdbAction(),
            CreateAction::make(),
        ];
    }

    private function importMdbAction(): Action
    {
        return Action::make('importMdb')
            ->label('Import from Access (.mdb)')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->color('gray')
            ->modalHeading('Import from Access (.mdb)')
            ->modalDescription('Upload a Register_be.mdb export. Records already in the Stock Register (matched by their original Access ID) are skipped automatically — only new records are inserted.')
            ->modalSubmitActionLabel('Import')
            ->schema([
                FileUpload::make('file')
                    ->label('.mdb file')
                    ->disk('local')
                    ->directory('mdb-imports')
                    ->visibility('private')
                    ->preserveFilenames()
                    ->maxSize(51200)
                    ->required(),
                Select::make('table')
                    ->label('Access table')
                    ->options([
                        'registerTab' => 'registerTab (live data)',
                        'reg' => 'reg (older backup snapshot)',
                    ])
                    ->default('registerTab')
                    ->required(),
            ])
            ->action(function (array $data, MdbRegisterImporter $importer): void {
                $relativePath = $data['file'];

                if (! str_ends_with(strtolower($relativePath), '.mdb')) {
                    Storage::disk('local')->delete($relativePath);

                    Notification::make()
                        ->title('Import failed')
                        ->body('The uploaded file is not a .mdb file.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $result = $importer->import(Storage::disk('local')->path($relativePath), $data['table']);

                    Notification::make()
                        ->title('Import complete')
                        ->body("Source rows: {$result['total']}. Newly imported: {$result['inserted']}. Already present: {$result['skipped']}.")
                        ->success()
                        ->send();
                } catch (RuntimeException $e) {
                    Notification::make()
                        ->title('Import failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                } finally {
                    Storage::disk('local')->delete($relativePath);
                }
            });
    }
}
