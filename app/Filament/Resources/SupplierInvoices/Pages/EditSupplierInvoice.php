<?php

namespace App\Filament\Resources\SupplierInvoices\Pages;

use App\Filament\Resources\SupplierInvoices\SupplierInvoiceResource;
use App\Models\SupplierInvoice;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSupplierInvoice extends EditRecord
{
    protected static string $resource = SupplierInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription('This reverses the stock change this document made and deletes it. This cannot be undone.')
                ->before(fn (SupplierInvoice $record) => $record->reverseStock()),
        ];
    }
}
